<?php

namespace FluentForm\Framework\Foundation;

trait AsyncRequestTrait
{
	protected $asyncActions = [];

	protected $launchableActions = [];

	public function addAsyncAction($action, $handler)
	{
		$this->asyncActions[$action] = $handler;
	}

	public function doAsyncAction($action, $data = [])
	{
		$this->launchableActions[$action] = $data;
		
	}

	protected function registerAsyncActions()
	{
		if ($this->hasAsyncActions()) {
			$this->registerAdminPostAction(
				$this->config->get('app.slug')
			);
		}
	}

	protected function hasAsyncActions()
	{
		return count($this->asyncActions) > 0;
	}

	protected function registerAdminPostAction($slug)
	{
		add_action("admin_post_wpfluent_async_$slug", [$this, 'callback']);
		add_action("admin_post_nopriv_wpfluent_async_$slug", [$this, 'callback']);

		if (!has_action('shutdown', [$this, 'launchPostRequest'])) {
			add_action('shutdown', [$this, 'launchPostRequest']);
		}
	}

	public function callback()
	{
		add_filter('wp_die_handler', function() { die(); });

		try {
			$this->verifyNonce();

			foreach ($_POST['actions'] as $action => $data) {
				try {
					$handler = $this->parseHookHandler(
						$this->asyncActions[$action]
					);

					$handler($data);
				} catch (\Exception $e) {
					continue;
				}
			}
		} catch (\Exception $e) {
			return;
		}
	}

	public function launchPostRequest()
	{
		if (!$this->hasLaunchableActions()) {
			return;
		}

		$args = [
			'timeout'   => 0.01,
			'blocking'  => false,
			'sslverify' => apply_filters('https_local_ssl_verify', true),
			'body'      => $this->getData(),
			'headers'   => [
				'cookie' => $this->getCookie(),
			],
		];

		wp_remote_post(admin_url('admin-post.php'), $args);
	}

	protected function getData()
	{
		return [
			'_nonce' => $this->createNonce(),
			'action' => $this->getMainAction(),
			'actions' => $this->launchableActions,
		];
	}

	protected function getCookie()
	{
		$cookies = [];

		foreach ($_COOKIE as $name => $value) {
			$cookies[] = "$name=" . urlencode(is_array($value) ? serialize($value) : $value);
		}

		return implode('; ', $cookies);
	}

	protected function hasLaunchableActions()
	{
		return count($this->launchableActions) > 0;
	}

	protected function createNonce()
	{
		$i = wp_nonce_tick();

		$action = $this->getMainAction();

		return wp_hash($i . $action, 'nonce');
	}

	protected function verifyNonce()
	{
		if (!isset($_POST['_nonce'])) {
			throw new \Exception('Invalid Request.');
		}

		$nonce = $_POST['_nonce'];

		$i = wp_nonce_tick();

		$action = $this->getMainAction();

		// Nonce generated 0-12 hours ago
		if (wp_hash($i . $action, 'nonce') == $nonce) {
			return 1;
		}

		// Nonce generated 12-24 hours ago
		if (wp_hash(($i - 1) . $action , 'nonce') == $nonce) {
			return 2;
		}

		// Invalid nonce
		return false;
	}

	protected function getMainAction() {
		$action = $this->config->get('app.slug');

		$action = "wpfluent_async_$action";

		return $action;
	}
}
