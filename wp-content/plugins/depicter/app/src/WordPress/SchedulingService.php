<?php

namespace Depicter\WordPress;

use Depicter\GuzzleHttp\Exception\GuzzleException;

class SchedulingService
{

	public function hooks() {
		add_action( 'depicter/document/schedule/publish', [ $this, 'publishSlider' ], 10, 1 );
		add_action( 'depicter/document/schedule/draft', [ $this, 'draftSlider' ], 10, 1 );
		add_action( 'depicter/document/schedule/clear/cache', [ $this, 'clearCache' ], 10, 1 );
	}

	/**
	 * Schedule Publish Event for slider
	 *
	 * @param $documentID
	 * @param $date
	 *
	 * @return void
	 */
	public function schedulePublishEvent( $documentID, $date ) {
		$this->clearScheduledPublishEvent( $documentID );
		if ( ! wp_next_scheduled( 'depicter/document/schedule/publish', [ $documentID ] ) ) {
			wp_schedule_single_event( strtotime( $date ), 'depicter/document/schedule/publish', [ $documentID ] );
		}
	}

	/**
	 * Clear all scheduled publish event for specific slider
	 *
	 * @param $documentID
	 *
	 * @return void
	 */
	public function clearScheduledPublishEvent( $documentID ) {
		wp_clear_scheduled_hook( 'depicter/document/schedule/publish', [ $documentID ] );
	}

	/**
	 * Schedule Draft Event for slider
	 *
	 * @param $documentID
	 * @param $date
	 *
	 * @return void
	 */
	public function scheduleDraftEvent( $documentID, $date ) {
		$this->clearScheduledDraftEvent( $documentID );
		if ( ! wp_next_scheduled( 'depicter/document/schedule/draft', [ $documentID ] ) ) {
			wp_schedule_single_event( strtotime( $date ), 'depicter/document/schedule/draft', [ $documentID ] );
		}
	}

	/**
	 * Clear all scheduled draft event for specific slider
	 *
	 * @param $documentID
	 *
	 * @return void
	 */
	public function clearScheduledDraftEvent( $documentID ) {
		wp_clear_scheduled_hook( 'depicter/document/schedule/draft', [ $documentID ] );
	}

	/**
	 * Schedule Publish Event for slider
	 *
	 * @param        $documentID
	 * @param        $date
	 * @param string $timestampType
	 *
	 * @return void
	 */
	public function scheduleClearCacheEvent( $documentID, $date, $timestampType = 'UTC' ) {
		if ( ! wp_next_scheduled( 'depicter/document/schedule/clear/cache', [ $documentID, $date ] ) ) {
			wp_schedule_single_event( strtotime( $date . ( $timestampType ? ' ' . $timestampType : '' ) ), 'depicter/document/schedule/clear/cache', [ $documentID ] );
		}
	}

	/**
	 * Clear all scheduled clear cache event for specific slider
	 *
	 * @param $documentID
	 *
	 * @return void
	 */
	public function clearScheduledClearCacheEvent( $documentID ) {
		wp_clear_scheduled_hook( 'depicter/document/schedule/clear/cache', [ $documentID ] );
	}


	/**
	 * Set publish status for slider
	 *
	 * @param $documentID
	 *
	 * @return void
	 */
	public function publishSlider( $documentID ) {
		try {
			if ( \Depicter::documentRepository()->isPublished( $documentID ) ) {
				return;
			}

			if ( \Depicter::auth()->isPaid() && ! \Depicter::auth()->verifyActivation() ) {
				error_log( esc_html__( 'License is not valid.', 'depicter' ), 0 );
				return;
			}

			if( function_exists( 'get_filesystem_method' ) && get_filesystem_method() != 'direct' ){
				error_log( esc_html__( 'Media files cannot be published due to lack of proper file permissions for uploads directory.', 'depicter' ), 0 );
				return;
			}

			$editorRawData = \Depicter::document()->getEditorRawData( $documentID );

			// Download media if document published
			\Depicter::media()->importDocumentAssets( $editorRawData );

			\Depicter::documentRepository()->saveEditorData( $documentID, ['status' => 'publish'] );
			$this->clearCache( $documentID );
			$documentModel = \Depicter::document()->getModel( $documentID )->prepare();
			$documentModel->render();
			$documentModel->saveCss();
		} catch( \Exception $exception ) {
			error_log( $exception->getMessage(), 0);
		} catch( GuzzleException $exception ) {
			error_log( $exception->getMessage(), 0);
		}
	}

	/**
	 * Set draft status for slider
	 *
	 * @param $documentID
	 *
	 * @return void
	 */
	public function draftSlider( $documentID ) {
		try {
			if ( !\Depicter::documentRepository()->isPublished( $documentID ) ) {
				return;
			}

			$this->clearCache( $documentID );
			\Depicter::documentRepository()->saveEditorData( $documentID, ['status' => 'unpublished'] );
		} catch( \Exception $exception ) {
			error_log( $exception->getMessage(), 0);
		}
	}

	public function clearCache( $documentID ) {
		\Depicter::front()->render()->flushDocumentCache( $documentID );
		\Depicter::document()->cacheCustomStyles( $documentID );
	}

	/**
	 * Compares the current time with a specified time in UTC timezone
	 *
	 * @param string $specifiedTime  Specified timestamp
	 * @param string $type           Timestamp type
	 *
	 * @return bool
	 */
	public function isDatePassed( $specifiedTime, $type = 'UTC' ): bool{
		// Get timestamp based on specified type
		$specifiedTime = strtotime( $specifiedTime . ( $type ? ' ' . $type : '' ) );

		return time() > $specifiedTime;
	}
}
