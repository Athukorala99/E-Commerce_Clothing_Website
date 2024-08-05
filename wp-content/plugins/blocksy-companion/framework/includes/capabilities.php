<?php

namespace Blocksy;

/*
 */

class Capabilities {
	private $accounts_cache = null;
	private $plans = [];
	private $plan = '__DEFAULT__';

	private $module_slug = 'blocksy-companion';

	public function __construct() {
		$this->accounts_cache = get_option('fs_accounts');

		$for_plans = $this->accounts_cache;

		if (is_multisite()) {
			$for_plans = get_network_option(null, 'fs_accounts');
		}

		if (
			isset($for_plans['plans'])
			&&
			isset($for_plans['plans'][$this->module_slug])
		) {
			$this->plans = $for_plans['plans'][$this->module_slug];
		}
	}

	public function get_features() {
		// possible plans:
		// free
		//
		// personal
		// professional
		// agency
		//
		// personal_v2
		// professional_v2
		// agency_v2
		return [
			'base_pro' => [
				'personal',
				'professional',
				'agency',

				'personal_v2',
				'professional_v2',
				'agency_v2'
			],

			'pro_starter_sites' => [
				'personal',
				'professional',
				'agency',

				'personal_v2',
				'professional_v2',
				'agency_v2'
			],

			'pro_starter_sites_enhanced' => [
				'personal',
				'professional',
				'agency',

				'professional_v2',
				'agency_v2'
			],

			'post_types_extra' => [
				'personal',
				'professional',
				'agency',

				'professional_v2',
				'agency_v2'
			],

			'shop_extra' => [
				'personal',
				'professional',
				'agency',

				'professional_v2',
				'agency_v2'
			],

			'white_label' => [
				'agency',
				'agency_v2'
			]
		];
	}

	public function has_feature($feature = 'base_pro') {
		$plan = $this->get_plan();

		$features = $this->get_features();

		if (! isset($features[$feature])) {
			return false;
		}

		return in_array($plan, $features[$feature]);
	}

	public function get_plan() {
		if ($this->plan !== '__DEFAULT__') {
			return $this->plan;
		}

		if (! blc_can_use_premium_code()) {
			$this->plan = 'free';
			return 'free';
		}

		$site = $this->get_site();

		if (
			! $site
			||
			! isset($site->plan_id)
			||
			empty($this->plans)
		) {
			return 'free';
		}

		$plan_id = null;

		foreach ($this->plans as $incomplete_plan) {
			$plan = $this->casttoclass('stdClass', $incomplete_plan);

			$id = base64_decode($plan->id);

			if (strval($id) === strval($site->plan_id)) {
				$plan_id = strval($id);
			}
		}

		$plans = [
			'8504' => 'free',

			'11880' => 'personal',
			'11881' => 'professional',
			'11882' => 'agency',

			'23839' => 'personal_v2',
			'23840' => 'professional_v2',
			'23841' => 'agency_v2'
		];

		if ($plan_id) {
			if (isset($plans[$plan_id])) {
				$this->plan = $plans[$plan_id];
				return $plans[$plan_id];
			} else {
				$this->plan = 'agency_v2';
				return 'agency_v2';
			}
		}

		$this->plan = 'free';
		return 'free';
	}

	private function get_site() {
		$site = null;

		if (
			! isset($this->accounts_cache)
			||
			! isset($this->accounts_cache['sites'])
			||
			! isset($this->accounts_cache['sites'][$this->module_slug])
		) {
			return null;
		}

		$maybe_site = $this->casttoclass(
			'stdClass',
			$this->accounts_cache['sites'][$this->module_slug]
		);

		return $maybe_site;
	}

	private function casttoclass($class, $object) {
		return unserialize(
			preg_replace(
				'/^O:\d+:"[^"]++"/',
				'O:' . strlen($class) . ':"' . $class . '"',
				serialize($object)
			)
		);
	}
}

