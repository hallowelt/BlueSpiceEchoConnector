<?php

/**
 * Echo Email Single class for notifications
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpice_Distrubution
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
class BsEchoEmailSingle extends EchoEmailSingle {

	/**
	 * Build the intro component
	 * @return string
	 */
	public function buildIntro() {
		$bundle = $this->notifFormatter->getValue( 'bundleData' );
		$email = $this->notifFormatter->getValue( 'email' );

		if ( $bundle[ 'use-bundle' ] && $email[ 'batch-bundle-body' ][ 'message' ] ) {
			$detail = $email[ 'batch-bundle-body' ];
		} else {
			$detail = $email[ 'batch-body' ];
		}

		$message = $this->notifFormatter->formatFragment(
			$detail,
			$this->event,
			$this->user
		);

		return $this->decorator->userBasedDecorateIntro( $message, $this->user );
	}

	public function getTextTemplate() {
		return <<< EOF
%%intro%% %%summary%%
%%action%% %%footer%%
EOF;
	}
}