<?php
namespace Depicter\Editor\Migrations;


use Averta\WordPress\Utility\JSON;

class Version202203091150
{

	public function up(){
		$documents = \Depicter::documentRepository()->all();
		if ( !empty( $documents ) && $documents->count() > 0 ) {
			foreach( $documents as $document ) {
				if ( empty( $document->content ) ) {
					continue;
				}

				$content = JSON::decode( $document->content );
				if ( isset( $content->computedValues ) ) {
					unset( $content->computedValues );
					$document->content = JSON::encode($content);
					$document->update();
				}
			}
		}
	}
}
