<?php

namespace BlueSpice\EchoConnector\Hook\TitleMoveComplete;
use BlueSpice\Hook\TitleMoveComplete;

class NotifyUsers extends TitleMoveComplete {
	protected function doProcess() {
		if( $this->user->isAllowed( 'bot' ) ) {
			return true;
		}

		$notificationsManager = \BlueSpice\Services::getInstance()->getService(
			'BSNotifications'
		);

		$notifier = $notificationsManager->getNotifier( 'bsecho' );

		$realname = \BlueSpice\Services::getInstance()->getBSUtilityFactory()
			->getUserHelper()->getDisplayName( $this->user );

		$notification = $notifier->getNotificationObject(
			'bs-move',
			[
				'agent' => $this->user,
				'title' => $this->title,
				'extra' => [
					'newtitle' => $this->newTitle,
					'realname' => $realname
				],
				'affected-users' => [1]
			]
		);

		$notifier->notify( $notification );

		return true;
	}
}