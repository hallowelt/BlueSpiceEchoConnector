<?php

namespace BlueSpice\EchoConnector\Formatter;

class EchoPlainTextEmailFormatter extends \EchoPlainTextEmailFormatter {
	protected function formatModel( \EchoEventPresentationModel $model ) {
		//PresentationModel is always created with distro type 'web'
		//so let parent handle all notifs that are not presented by our PresentationModel
		//and set distro type manually usign custom function
		if( !$model instanceof \BlueSpice\EchoConnector\EchoEventPresentationModel ) {
			return parent::formatModel( $model );
		}

		$model->setDistributionType( 'email' );
		$model->setEmailFormat( 'plain-text' );

		$subject = \Sanitizer::stripAllTags( $model->getSubjectMessage()->parse() );

		$text = \Sanitizer::stripAllTags( $model->getHeaderMessage()->parse() );

		$text .= "\n\n";

		$bodyMsg = $model->getBodyMessage();
		if ( $bodyMsg ) {
			$text .= \Sanitizer::stripAllTags( $bodyMsg->parse() );
		}

		// Footer
		$text .= "\n\n{$this->getFooter()}";

		return [
			'body' => $text,
			'subject' => $subject,
		];
	}
}