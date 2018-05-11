<?php

namespace BlueSpice\EchoConnector\Notifier;

use \BlueSpice\EchoConnector\Notification\EchoNotification;
use \BlueSpice\EchoConnector\EchoEventPresentationModel;
use \BlueSpice\EchoConnector\NotificationFormatter;

class EchoNotifier implements \BlueSpice\INotifier {
	
	public function getNotificationObject( $key, $params ) {
		return new EchoNotification( $key, $params );
	}

	public function init() {
		return;
	}

	public function notify( $notification ) {
		if( $notification instanceof EchoNotification == false ) {
			return;
		}

		$echoNotif = [
			'type' => $notification->getKey(),
			'agent' => $notification->getUser(),
			'title' => $notification->getTitle(),
			'extra' => $notification->getParams()
		];

		if( !empty( $notification->getAudience() ) ) {
			$echoNotif['extra']['affected-users'] = $notification->getAudience();
		}

		\EchoEvent::create ( $echoNotif );

		return \Status::newGood();
	}

	public function registerNotification($key, $params) {
		global $wgEchoNotifications;

		$extraParams = [];
		if ( !empty( $params[ 'extra-params' ] ) ) {
			$extraParams = $params[ 'extra-params' ];
		}

		if ( !isset ( $extraParams[ 'formatter-class' ] ) ) {
			$extraParams[ 'formatter-class' ] = NotificationFormatter::class;
		}
		if ( !isset ( $extraParams[ 'presentation-model' ] ) ) {
			$extraParams[ 'presentation-model' ] = EchoEventPresentationModel::class;
		}

		if ( isset ( $params[ 'icon' ] ) ) {
			$extraParams[ 'icon' ] = $params[ 'icon' ];
		}

		$wgEchoNotifications[$key] = $extraParams + [
			'category' => $params[ 'category' ],
			'title-message' => $params[ 'summary-message' ],
			'title-params' => $params[ 'summary-params' ],
			'web-body-message' => $params[ 'web-body-message' ],
			'web-body-params' => $params[ 'web-body-params' ],
			'email-subject-message' => $params[ 'email-subject' ],
			'email-subject-params' => $params[ 'email-subject-params' ],
			'email-body-batch-message' => $params[ 'email-body' ],
			'email-body-batch-params' => $params[ 'email-body-params' ],
			'user-locators' => [self::class . '::setUsersToNotify']
		];
	}

	public function registerNotificationCategory( $key, $params ) {
		global $wgEchoNotificationCategories;

		$wgEchoNotificationCategories[$key] = $params;
	}

	public function unRegisterNotification($key) {
		
	}

	public static function setUsersToNotify( $event ) {
		$users = $event->getExtraParam( 'affected-users', [] );

		$res = [];
		foreach( $users as $user ) {
			$res[$user] = \User::newFromId( $user );
		}

		return $res;
	}

	public static function filterUsersToNotify( $event ) {
	}

}