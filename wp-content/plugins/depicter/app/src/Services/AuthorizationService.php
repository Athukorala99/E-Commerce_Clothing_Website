<?php

namespace Depicter\Services;

class AuthorizationService {

	/**
	 * Whether the current user has specified capabilities or not.
	 *
	 * @param string|array $capabilities
	 *
	 * @return bool
	 */
	public function currentUserCan( $capabilities ){
		if( empty( $capabilities ) ){
			return false;
		}

		$capabilities = (array) $capabilities;
		foreach( $capabilities as $capability ){
			if( current_user_can( $capability ) ){
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether current user is allowed to publish document or not
	 *
	 * @return bool
	 */
	public function currentUserCanPublishDocument(){
		return $this->currentUserCan( [ 'manage_options', 'publish_depicter' ] );
	}
}
