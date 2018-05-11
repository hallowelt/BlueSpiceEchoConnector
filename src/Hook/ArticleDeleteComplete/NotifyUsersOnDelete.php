<?php

namespace BlueSpice\EchoConnector\Hook\ArticleDeleteComplete;
use BlueSpice\Hook\ArticleDeleteComplete;

class NotifyUsersOnDelete extends ArticleDeleteComplete {
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
			'bs-delete',
			[
				'agent' => $this->user,
				'title' => $this->wikipage->getTitle(),
				'extra' => [
					'deletereason' => $this->reason,
					'title' => $this->wikipage->getTitle()->getText(),
					'realname' => $realname
				]
			]
		);

		$notifier->notify( $notification );

		return true;
	}
}