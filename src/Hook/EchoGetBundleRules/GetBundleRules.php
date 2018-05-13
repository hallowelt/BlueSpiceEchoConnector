<?php

namespace BlueSpice\EchoConnector\Hook\EchoGetBundleRules;

use \BlueSpice\EchoConnector\Hook\EchoGetBundleRules;

class GetBundleRules extends EchoGetBundleRules {
	
	protected function doProcess() {
		$bundleString = $this->event->getType ();
		$title = $this->event->getTitle();
		if ( $title instanceof \Title ) {
			$bundleString .= '-' . $title->getNamespace () . '-' . $title->getDBkey ();
		}

		return true;
	}

}