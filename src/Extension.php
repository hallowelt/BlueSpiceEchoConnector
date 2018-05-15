<?php

namespace BlueSpice\EchoConnector;

class Extension {
	public static function registerNotifications( \BlueSpice\NotificationManager $notificationsManager ) {
		$echoNotifier = $notificationsManager->getNotifier( 'bsecho' );
		$echoNotifier->registerNotificationCategory(
			'bs-admin-cat',
			[
				'priority' => 3,
				'usergroups' => ['sysop']
			]
		);
		$echoNotifier->registerNotificationCategory( 'bs-page-actions-cat', ['priority' => 3] );

		$notificationsManager->registerNotification(
			'bs-adduser',
			$echoNotifier,
			[
				'category' => 'bs-admin-cat',
				'summary-message' => 'bs-notifications-addacount',
				'summary-params' => [
					'username'
				],
				'email-subject' => 'bs-notifications-email-addaccount-subject',
				'email-subject-params' => [
					'username', 'username'
				],
				'email-body' => 'bs-notifications-email-addaccount-body',
				'email-body-params' => [
					'userlink', 'username', 'username', 'user'
				],
				'web-body-message' => 'bs-notifications-email-addaccount-body',
				'web-body-params' => [
					'realname', 'username'
				],
				'extra-params' => array (),
				'user-locators' => [self::class . '::getUsersToNotify']
			]
		);

		$notificationsManager->registerNotification(
			'bs-edit',
			$echoNotifier,
			[
				'category' => 'bs-page-actions-cat',
				'summary-message' => 'bs-notifications-edit',
				'summary-params' => [
					'title'
				],
				'email-subject' => 'bs-notifications-email-edit-subject',
				'email-subject-params' => array (
					'title', 'agent', 'realname'
				),
				'email-body' => 'bs-notifications-email-edit-body',
				'email-body-params' => array (
					'title', 'agent', 'summary', 'titlelink', 'difflink', 'realname'
				),
				'web-body-message' => 'bs-notifications-web-edit-body',
				'web-body-params' => array (
					'title', 'agent', 'realname'
				),
				'extra-params' => array (),
				'user-locators' => [self::class . '::getUsersToNotify']
			]
		);

		$notificationsManager->registerNotification(
			'bs-create',
			$echoNotifier,
			[
				'category' => 'bs-page-actions-cat',
				'summary-message' => 'bs-notifications-create',
				'summary-params' => array (
					'title'
				),
				'email-subject' => 'bs-notifications-email-create-subject',
				'email-subject-params' => array (
					'title', 'agent', 'realname'
				),
				'email-body' => 'bs-notifications-email-create-body',
				'email-body-params' => array (
					'title', 'agent', 'summary', 'titlelink', 'difflink', 'realname'
				),
				'web-body-message' => 'bs-notifications-web-create-body',
				'web-body-params' => array (
					'title', 'agent', 'realname'
				),
				'extra-params' => array (),
				'user-locators' => [self::class . '::getUsersToNotify']
			]
		);

		$notificationsManager->registerNotification(
			'bs-delete',
			$echoNotifier,
			[
				'category' => 'bs-page-actions-cat',
				'summary-message' => 'bs-notifications-delete',
				'summary-params' => array (
					'title'
				),
				'email-subject' => 'bs-notifications-email-delete-subject',
				'email-subject-params' => array (
					'title', 'agent', 'realname'
				),
				'email-body' => 'bs-notifications-email-delete-body',
				'email-body-params' => array (
					'title', 'agent', 'deletereason', 'titlelink', 'difflink', 'realname'
				),
				'web-body-message' => 'bs-notifications-web-delete-body',
				'web-body-params' => array (
					'title', 'agent', 'realname', 'deletereason'
				),
				'extra-params' => array (
					//usually only existing titles can produce notifications
					//we do not have a title after its deleted
					'forceRender' => true
				),
				'user-locators' => [self::class . '::getUsersToNotify']
			]
		);

		$notificationsManager->registerNotification(
			'bs-move',
			$echoNotifier,
			[
				'category' => 'bs-page-actions-cat',
				'category' => 'bs-page-actions-cat',
				'summary-message' => 'bs-notifications-move',
				'summary-params' => array (
					'title'
				),
				'email-subject' => 'bs-notifications-email-move-subject',
				'email-subject-params' => array (
					'title', 'agent', 'newpage', 'realname'
				),
				'email-body' => 'bs-notifications-email-move-body',
				'email-body-params' => array (
					'title', 'agent', 'newtitle', 'newtitlelink', 'realname'
				),
				'web-body-message' => 'bs-notifications-web-move-body',
				'web-body-params' => array (
					'title', 'agent', 'newtitle', 'realname'
				),
				'extra-params' => array(
					'bundle' => [
						'web' => true,
						'email' => true,
						'expandable' => true,
						'bundle-message' => 'bs-notifications-move-bundle',
						'bundle-params' => []
					]
				),
				'user-locators' => [self::class . '::getUsersToNotify']
			]
		);
	}

	//This seems like a shootgun approach, not everyone should
	//be notified of every change on every page
	public static function getUsersToNotify( $event ) {
		// Everyone deserves to know when something happens
		// on their user talk page
		$dbr = wfGetDB ( DB_SLAVE );
		switch ( $event->getType () ) {
			case 'bs-adduser':
			//Get admin users
			$resSysops = $dbr->select ( "user_groups", "ug_user", 'ug_group = "sysop"' );
			foreach ( $resSysops as $row ) {
				$user = \User::newFromId ( $row->ug_user );
				$users[ $user->getId () ] = $user;
			}
			break;
			case 'bs-create':
			case 'bs-edit':
			case 'bs-move':
			case 'bs-delete':
				//We need to pre-filter for the subscription user setting here.
				//Otherwise a large user base (2000+) will result in bad performance
				$resUser = $dbr->select(
					"user_properties",
					"DISTINCT up_user",
					[
						"up_property" => [
							"echo-subscriptions-web-bs-page-actions-cat",
							"echo-subscriptions-email-bs-page-actions-cat"
						],
						"up_value" => 1
					]
				);

				foreach ( $resUser as $row ) {
					$user = \User::newFromId ( $row->up_user );
					$users[ $user->getId () ] = $user;
				}
			break;
		}

		return $users;
	}
}