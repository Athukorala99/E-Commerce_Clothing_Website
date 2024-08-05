<?php
namespace Depicter\Editor\Migrations;


class JobsQueue
{

	public function migrate(){
		$jobsList = \Depicter::storage()->filesystem()->scan(__DIR__);

		$latestExecutedJob = \Depicter::options()->get('last_document_migration', 0 );
		foreach( $jobsList as $key => $job ) {
			if ( $job['name'] == 'JobsQueue.php' ) {
				continue;
			}

			$jobName = basename( $job['name'], '.php');
			$jobVersion = str_replace( 'Version', '', $jobName );
			if ( $jobVersion > $latestExecutedJob ) {
				$jobClassName = "Depicter\Editor\Migrations\\" . $jobName;
				( new $jobClassName() )->up();
				\Depicter::options()->set( 'last_document_migration', $jobVersion );
			}
		}
	}
}
