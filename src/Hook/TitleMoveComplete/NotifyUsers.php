<?php

namespace BlueSpice\EchoConnector\Hook\TitleMoveComplete;
use BlueSpice\Hook\TitleMoveComplete;

class NotifyUsers extends TitleMoveComplete {
	protected function doProcess() {
		if( $this->user->isAllowed( 'bot' ) ) {
			//return true;
		}

		$notificationsManager = \BlueSpice\Services::getInstance()->getBSNotificationManager();

		$notifier = $notificationsManager->getNotifier( 'bsecho' );

		$realname = \BlueSpice\Services::getInstance()->getBSUtilityFactory()
			->getUserHelper()->getDisplayName( $this->user );

		$notification = $notifier->getNotificationObject(
			'bs-move',
			[
				'agent' => $this->user,
				'title' => $this->title,
				'extra-params' => [
					'newtitle' => $this->newTitle,
					'realname' => $realname
				]
			]
		);

		$notifier->notify( $notification );

		return true;
	}
}