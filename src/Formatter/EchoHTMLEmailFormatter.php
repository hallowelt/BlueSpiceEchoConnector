<?php

namespace BlueSpice\EchoConnector\Formatter;

use \BlueSpice\EchoConnector\EchoEventPresentationModel as BSEchoPresentationModel;

class EchoHTMLEmailFormatter extends \EchoHtmlEmailFormatter {
	protected function formatModel( \EchoEventPresentationModel $model ) {
		if( !$model instanceof BSEchoPresentationModel ) {
			return parent::formatModel( $model );
		}

		$model->setDistributionType( 'email' );
		$model->setEmailFormat( 'html' );

		return parent::formatModel( $model );
	}
}