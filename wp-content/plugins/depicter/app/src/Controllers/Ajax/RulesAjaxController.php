<?php

namespace Depicter\Controllers\Ajax;


use Averta\WordPress\Utility\JSON;
use Depicter\Rules\Conditions\ListConditions;
use Depicter\Utility\Sanitize;
use WPEmerge\Requests\RequestInterface;

class RulesAjaxController {

	/**
	 * Store rules data
	 *
	 * @param RequestInterface $request
	 * @param                  $view
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
    public function store( RequestInterface $request, $view ) {
        $id = Sanitize::int( $request->body( 'ID', '' ) );
        $content = Sanitize::textfield( $request->body( 'content', '' ) );

        if ( empty( $content ) || empty( $id ) || ! JSON::isJson( $content ) ) {
            return \Depicter::json([
                'errors' => [ __( 'Both id and content are required.', 'depicter' ) ]
            ])->withStatus(400);
        }
		error_log( $content );
		\Depicter::metaRepository()->update( $id, 'rules', $content );

	    return \Depicter::json( [ 'success' => true ] )->withStatus( 200 );
    }

	/**
	 * Show stored rules for provided document ID
	 *
	 * @param RequestInterface $request
	 * @param                  $view
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function show( RequestInterface $request, $view ) {
		$id = Sanitize::int( $request->query( 'ID', '' ) );

		if ( empty( $id ) ) {
			return \Depicter::json([
				'errors' => [ __( 'Document id is required.', 'depicter' ) ]
			])->withStatus(400);
		}

		$rule = \Depicter::metaRepository()->get( $id, 'rules', '' );

		$result = [
			'displayRules' => JSON::isJson( $rule ) ? JSON::decode( $rule, true ) : [],
			'conditions'   => \Depicter::conditionsManager()->listConditions()->list()
		];

		return \Depicter::json( $result )->withStatus( 200 );
	}

	/**
	 * List condition values for given query
	 *
	 * @param RequestInterface $request
	 * @param                  $view
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function conditionValues( RequestInterface $request, $view ) {
		$query = Sanitize::textfield( $request->query( 'query', '' ) );

		if ( empty( $query ) ) {
			return \Depicter::json([
				'errors' => [ __( 'query is required.', 'depicter' ) ]
			])->withStatus(400);
		}

		try{
			return \Depicter::json( \Depicter::conditionsManager()->getConditionOptions( $query ) )->withStatus(200);
		} catch( \Exception $exception ) {
			return \Depicter::json([
				'errors' => [ $exception->getMessage() ]
			])->withStatus(400);
		}
	}
}
