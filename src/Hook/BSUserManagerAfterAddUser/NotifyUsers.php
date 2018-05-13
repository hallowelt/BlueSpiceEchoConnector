<?php

namespace BlueSpice\EchoConnector\Hook\BSUserManagerAfterAddUser;

use BlueSpice\UserManager\Hook\BSUserManagerAfterAddUser;

class NotifyUsers extends BSUserManagerAfterAddUser {
	
	protected function doProcess() {
		$notificationsManager = \BlueSpice\Services::getInstance()->getService(
			'BSNotifications'
		);

		$notifier = $notificationsManager->getNotifier( 'bsecho' );

		$notification = $notifier->getNotificationObject(
			'bs-adduser',
			[
				'agent' => $this->user,
				'extra' => [
					'username' => $this->user->getName(),
					'userlink' => $this->user->getUserPage()->getFullUrl(),
					'user' => $this->user->getName()
				]
			]
		);

		$notifier->notify( $notification );

		return true;
	}
}