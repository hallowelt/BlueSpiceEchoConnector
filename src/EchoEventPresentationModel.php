<?php

namespace BlueSpice\EchoConnector;

use BlueSpice\EchoConnector\Formatter\NotificationFormatter;

class EchoEventPresentationModel extends \EchoEventPresentationModel {
	protected $paramParser;
	protected $echoNotifications;

	protected $distributionType;
	protected $emailFormat;

	public function __construct( \EchoEvent $event, $language, \User $user, $distributionType ) {
		global $wgEchoNotifications;

		parent::__construct( $event, $language, $user, $distributionType );

		$this->distributionType = $distributionType;
		$this->mailFormat = \EchoEmailFormat::PLAIN_TEXT;

		$this->paramParser = new \BlueSpice\EchoConnector\Formatter\ParamParser( $event );
		$this->echoNotifications = &$wgEchoNotifications;
	}

	//There is not way to change distribution type after object
	//has been contstructed, and its always constructed with 'web' type
	public function setDistributionType( $type ) {
		$this->distributionType = $type;
		$this->paramParser->setDistributionType( $type );
	}

	public function setEmailFormat( $format ) {
		$this->emailFormat = $format;
		$this->paramParser->setEmailFormat( $format );
	}

	public function canRender() {
		//Force rendering if explicitly specified
		if( isset( $this->echoNotifications[$this->type]['forceRender'] ) ) {
			return true;
		}
		return (bool) $this->event->getTitle();
	}

	public function getIconType() {
		return $this->getIcon();
	}

	public function getIcon() {
		if ( isset( $this->echoNotifications[$this->type]['icon'] ) ) {
			return $this->echoNotifications[$this->type]['icon'];
		}

		return 'placeholder';
	}

	public function getHeaderMessage() {
		$content = $this->getHeaderMessageContent();
		$msg = $this->msg($content['key']);

		if ($this->isBundled()) {
			if ($content['bundle-key']) {
				$msg = $this->msg($content['bundle-key']);
				$msg->params($this->getBundleCount());
			}
		}

		$params = $content['params'];
		if( $this->isBundled() ) {
			$params = $content['bundle-params'];
		}

		if( empty( $params ) ) {
			return $msg;
		}

		foreach( $params as $param ) {
			$this->paramParser->parseParam( $msg, $param );
		}

		return $msg;
	}

	public function getBodyMessage() {
		$content = $this->getBodyMessageContent();
		if( !$content['key'] ) {
			return false;
		}
		$msg = $this->msg( $content['key'] );
		if( empty( $content['params'] ) ) {
			return $msg;
		}

		foreach( $content['params'] as $param ) {
			$this->paramParser->parseParam( $msg, $param );
		}

		return $msg;
	}

	public function getCompactHeaderMessage() {
		// This is the header message for individual notifications
		// *inside* the bundle
		$msg = parent::getCompactHeaderMessage();
		return $msg;
	}

	/**
	 * Gets the URL to the title that notification is about
	 *
	 * @return string|false if no \Title is supplied
	 */
	public function getPrimaryLink() {
		$title = $this->event->getTitle();
		if( $title instanceof \Title == false ) {
			return false;
		}

		return [
			'url' => $title->getFullURL(),
			'label' => $title->getPrefixedText()
		];
	}

	/**
	 * Gets appropriate messages keys and params
	 * for header message
	 *
	 * @return array
	 */
	public function getHeaderMessageContent() {
		$bundleKey = '';
		$bundleParams = [];
		if( isset( $this->echoNotifications[$this->type]['bundle'] ) ) {
			$bundleKey = $this->echoNotifications[$this->type]['bundle']['bundle-message'];
			$bundleParams = $this->echoNotificationss[$this->type]['bundle']['bundle-params'];
		}

		$headerKey = $this->echoNotifications[$this->type]['title-message'];
		$headerParams = $this->echoNotifications[$this->type]['title-params'];
		if( $this->distributionType == 'email' ) {
			$headerKey = $this->echoNotifications[$this->type]['email-subject-message'];
			$headerParams = $this->echoNotifications[$this->type]['email-subject-params'];
		}

		return [
			'key' => $headerKey,
			'params' => $headerParams,
			'bundle-key' => $bundleKey,
			'bundle-params' => $bundleParams
		];
	}

	/**
	 * Gets appropriate message key and params for
	 * web notification message
	 *
	 * @return array
	 */
	public function getBodyMessageContent() {
		if( $this->distributionType == 'email' ) {
			return array(
				'key' => $this->echoNotifications[$this->type]['email-body-batch-message'],
				'params' => $this->echoNotifications[$this->type]['email-body-batch-params']
			);
		}

		return array(
			'key' => $this->echoNotifications[$this->type]['web-body-message'],
			'params' => $this->echoNotifications[$this->type]['web-body-params']
		);
	}

}