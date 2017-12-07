<?php

/**
 * A maintenance script that processes email digest
 */
class JobQueueProcessBsEchoEmailBatch extends JobQueue {

	/**
	 * Max number of records to process at a time
	 * @var int
	 */
	protected $batchSize = 300;

	protected $ignoreConfiguredSchedule = 0;

	public function __construct( array $params ) {
		parent::__construct( $params );

		$this->mDescription = "Process email digest";

		/*
		$this->addOption(
		  "ignoreConfiguredSchedule", "Send all pending notifications immediately even if configured to be weekly or daily.", false, false, "i" );
		 *
		 */
		$this->ignoreConfiguredSchedule = $params["ignoreConfiguredSchedule"];

		global $wgEchoCluster;

		if ( !class_exists( 'EchoHooks' ) ) {
			$this->error( "Echo isn't enabled on this wiki\n", 1 );
		}

		$ignoreConfiguredSchedule = $this->ignoreConfiguredSchedule;

		echo "Started processing... \n" ;

		$startUserId = 0;
		$count = $this->batchSize;

		while ( $count === $this->batchSize ) {
			$count = 0;

			$res = BsEmailBatch::getUsersToNotify( $startUserId, $this->batchSize );

			$updated = false;
			foreach ( $res as $row ) {
				$userId = intval( $row->eeb_user_id );
				if ( $userId && $userId > $startUserId ) {
					$emailBatch = BsEmailBatch::newFromUserId( $userId, !$ignoreConfiguredSchedule );
					if ( $emailBatch ) {
						echo "processing user_Id " . $userId . " \n";
						$emailBatch->process();
					}
					$startUserId = $userId;
					$updated = true;
				}
				$count++;
			}
			wfWaitForSlaves( false, false, $wgEchoCluster );
			// This is required since we are updating user properties in main wikidb
			wfWaitForSlaves();

			// double check to make sure that the id is updated
			if ( !$updated ) {
				break;
			}
		}

		echo  "Completed \n";
	}

	protected function doAck( \Job $job ) {

	}

	protected function doBatchPush( array $jobs, $flags ) {

	}

	protected function doGetAcquiredCount() {

	}

	protected function doGetSize() {

	}

	protected function doIsEmpty() {

	}

	protected function doPop() {

	}

	protected function optimalOrder() {

	}

	protected function supportedOrders() {
		return [ 'random', 'timestamp', 'fifo' ];
	}

	public function getAllQueuedJobs() {

	}

}
