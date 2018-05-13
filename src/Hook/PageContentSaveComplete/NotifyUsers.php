<?php

namespace BlueSpice\EchoConnector\Hook\PageContentSaveComplete;

use BlueSpice\Hook\PageContentSaveComplete;

class NotifyUsers extends PageContentSaveComplete {
	
	protected function doProcess() {
		if ( $this->user->isAllowed ( 'bot' ) )
			return true;
		if ( $this->wikipage->getTitle ()->getNamespace () === NS_USER_TALK )
			return true;

		$notificationsManager = \BlueSpice\Services::getInstance()->getService(
			'BSNotifications'
		);

		$notifier = $notificationsManager->getNotifier( 'bsecho' );

		$realname = \BlueSpice\Services::getInstance()->getBSUtilityFactory()
			->getUserHelper()->getDisplayName( $this->user );

		if ( $this->flags & EDIT_NEW ) {
			$notification = $notifier->getNotificationObject(
				'bs-create',
				[
					'agent' => $this->user,
					'title' => $this->wikipage->getTitle(),
					'extra' => [
						'summary' => $this->summary,
						'titlelink' => true,
						'realname' => $realname,
						'difflink' => ''
					]
				]
			);

			$notifier->notify( $notification );
			return true;
		}

		$diffParams = array ( 'diffparams' => array () );
		if ( is_object ( $this->revision ) ) {
			$diffParams[ 'diffparams' ][ 'diff' ] = $this->revision->getId ();
			if ( is_object ( $this->revision->getPrevious () ) ) {
				$diffParams[ 'diffparams' ][ 'oldid' ] = $this->revision->getPrevious ()->getId ();
			}
		}

		$notification = $notifier->getNotificationObject(
			'bs-edit',
			[
				'agent' => $this->user,
				'title' => $this->wikipage->getTitle(),
				'extra' => [
					'summary' => $this->summary,
					'titlelink' => true,
					'realname' => $realname,
					'difflink' => $diffParams,
					'agentlink' => true
				]
			]
		);

		$notifier->notify( $notification );

		return true;
	}

}