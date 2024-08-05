<?php
namespace Averta\WordPress\Cache;

use Averta\WordPress\Utility\Sanitize;
use DateInterval;
use Psr\Http\Message\RequestInterface;
use Psr\SimpleCache\CacheInterface;

class WPCache implements CacheInterface{

	/**
	 * The list of transient keys
	 *
	 * @var array
	 */
	private $inUseKeys = [];

	/**
	 *  A prefix for all transient keys
	 *
	 * @var string
	 */
	protected $keyPrefix = '';


	/**
	 * Cache constructor.
	 *
	 * @param null $cachePrefix
	 */
	public function __construct( $cachePrefix = null )
	{
		if( ! is_null( $cachePrefix ) ){
			$this->keyPrefix = $cachePrefix;
		}
	}

    /**
     * Get the value of a transient.
     *
     * If the transient does not exist, does not have a value, or has expired,
     * then the return value will be false.
     *
     * @param string $key Cache key. Expected to not be SQL-escaped.
     *
     * @param bool   $default Default cache value
     *
     * @return mixed Value of transient.
     */
	public function get( $key, $default = false ) {
		$key = $this->validateKey( $key );

		$value = get_transient( $key );
		if ( false === $value ) {
			$value = $default;
		}

		return $value;
	}

	/**
	 * Set/update the value of a transient.
	 *
	 * You do not need to serialize values. If the value needs to be serialized, then
	 * it will be serialized before it is set.
	 *
	 *
	 * @param string $key  		 Cache key. Expected to not be SQL-escaped. Must be
	 *                           172 characters or fewer in length.
	 * @param mixed  $value      Transient value. Must be serializable if non-scalar.
	 *                           Expected to not be SQL-escaped.
	 * @param int    $ttl        Optional. Time until expiration in seconds. Default 0 (no expiration).
	 *
	 * @return bool False if value was not set and true if value was set.
	 */
	public function set( $key, $value, $ttl = null ): bool {
		$key = $this->validateKey( $key );
		$this->addToKeysList( $key );

		if ( $ttl instanceof DateInterval ) {
			$ttl = $this->convertDateIntervalToInteger( $ttl );
		}

		return set_transient( $key, $value, intval($ttl) );
	}

	/**
	 * Delete a transient.
	 *
	 * @param string $key  Cache key. Expected to not be SQL-escaped.
	 *
	 * @return bool true if successful, false otherwise
	 */
	public function delete( $key ): bool {
		$key = $this->validateKey( $key );
		$this->deleteFromKeyList( $key );

		return delete_transient( $key );
	}

	/**
	 * Convert a request object to a unique key
	 *
	 * @param  RequestInterface  $request
	 *
	 * @return string
	 */
	public function hashRequest( RequestInterface $request ) {
		$requestArgs = $request->getQueryParams();

        $requestArgs['url'] = $request->getRequestTarget();
		$requestArgs['url'] = remove_query_arg( ['flush', 'clearCache'], $requestArgs['url'] );

		// exclude flush params from hash request key
		unset( $requestArgs['flush'] );
		unset( $requestArgs['clearCache'] );

        $requestArgs = $this->beforeHashRequest( $requestArgs, $request );

		return Sanitize::textfield( md5( serialize( $requestArgs ) ) );
	}

    /**
	 * Prepares request args for hashing
	 * Override this method to change requestArgs before hash
     *
	 * @param  array             $requestArgs
	 * @param  RequestInterface  $request
	 *
	 * @return array
	 */
    protected function beforeHashRequest( array $requestArgs, RequestInterface $request ){
        return $requestArgs;
    }

	public function has( $key ): bool {
		return $this->get( $key, false ) !== false;
	}


	public function getMultiple( $keys, $default = null ) {
		$result = [];

		foreach ( $keys as $key ) {
			$result[ $key ] = $this->get( $key, $default );
		}

		return $result;
	}


	public function setMultiple( $values, $ttl = null ): bool {
		foreach ( $values as $key => $value ) {
			if ( $this->set( $key, $value, $ttl ) ) {
				continue;
			}
			return false;
		}

		return true;
	}


	public function deleteMultiple( $keys ): bool {

		foreach ( $keys as $key ) {
			if ( $this->delete( $key ) ) {
				continue;
			}
			return false;
		}

		return true;
	}


	public function clear(): bool {

		if( ! empty( $this->keyPrefix ) ) {
			global $wpdb;
			$wpdb->query($wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				"_transient_{$this->keyPrefix}%",
				"_transient_timeout_{$this->keyPrefix}%"
			));
		}

		return $this->deleteMultiple( $this->inUseKeys() );
	}

	/**
	 * @param DateInterval $ttl
	 *
	 * @return int
	 */
	private function convertDateIntervalToInteger( DateInterval $ttl ) : int {

		return ( new DateTime() )
			->setTimestamp(0)
			->add( $ttl )
			->getTimestamp();
	}

	/**
	 * Adds a key to cache key list
	 *
	 * @param string $key
	 */
	private function addToKeysList( $key ): void {
		$this->inUseKeys[ $key ] = $key;
	}

	/**
	 * Removes a key from cache key list
	 *
	 * @param string $key
	 */
	private function deleteFromKeyList( $key ): void {
		unset( $this->inUseKeys[ $key ] );
	}


	private function inUseKeys(): array {
		return $this->inUseKeys;
	}

	protected function validateKey( $key ){
        if ( strpos( $key, $this->keyPrefix ) === 0 ) {
            $key = substr( $key, strlen( $this->keyPrefix ) );
        }
		return $this->keyPrefix . $key;
	}


	public function prevent(){
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}

		if ( ! defined( 'DONOTCACHEDB' ) ) {
			define( 'DONOTCACHEDB', true );
		}

		if ( ! defined( 'DONOTMINIFY' ) ) {
			define( 'DONOTMINIFY', true );
		}

		if ( ! defined( 'DONOTCDN' ) ) {
			define( 'DONOTCDN', true );
		}

		if ( ! defined( 'DONOTCACHCEOBJECT' ) ) {
			define( 'DONOTCACHCEOBJECT', true );
		}

		// prevent caching.
		nocache_headers();
	}

}
