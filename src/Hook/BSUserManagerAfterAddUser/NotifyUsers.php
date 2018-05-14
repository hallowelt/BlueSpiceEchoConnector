<?php

namespace BlueSpice\EchoConnector\Hook\BSUserManagerAfterAddUser;

use BlueSpice\UserManager\Hook\BSUserManagerAfterAddUser;

class NotifyUsers extends BSUserManagerAfterAddUser {
	
	protected function doProcess() {
		$notificationsManager = \BlueSpice\Services::getInstance()->getBSNotificationManager();

		$notifier = $notificationsManager->getNotifier( 'bsecho' );

		$realname = \BlueSpice\Services::getInstance()->getBSUtilityFactory()
			->getUserHelper()->getDisplayName( $this->user );

		$notification = $notifier->getNotificationObject(
			'bs-adduser',
			[
				'agent' => $this->performer,
				'extra-params' => [
					'realname' => $realname,
					'user' => $this->user
				]
			]
		);

		$notifier->notify( $notification );

		return true;
	}
}