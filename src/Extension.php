<?php

namespace BlueSpice\EchoConnector;

class Extension {
	public static function registerNotifications( \BlueSpice\Notifications $notificationsManager ) {
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
					'userlink', 'username', 'username', 'user'
				],
				'extra-params' => array ()
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
				'web-body-message' => 'bs-notifications-email-edit-body',
				'web-body-params' => array (
					'title', 'agent', 'summary', 'titlelink', 'difflink', 'realname'
				),
				'extra-params' => array ()
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
				'web-body-message' => 'bs-notifications-email-create-body',
				'web-body-params' => array (
					'title', 'agent', 'summary', 'titlelink', 'difflink', 'realname'
				),
				'extra-params' => array ()
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
					'title', 'agent', 'summary', 'titlelink', 'difflink', 'realname'
				),
				'web-body-message' => 'bs-notifications-email-delete-body',
				'web-body-params' => array (
					'title', 'agent', 'summary', 'titlelink', 'difflink', 'realname'
				),
				'extra-params' => array ()
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
					'title', 'agent'
				),
				'email-subject' => 'bs-notifications-email-move-subject',
				'email-subject-params' => array (
					'title', 'agent', 'realname'
				),
				'email-body' => 'bs-notifications-email-move-body',
				'email-body-params' => array (
					'title', 'agent', 'newtitle', 'difflink', 'realname'
				),
				'web-body-message' => 'bs-notifications-email-move-body',
				'web-body-params' => array (
					'title', 'agent', 'newtitle', 'difflink', 'realname'
				),
				'extra-params' => array ()
			]
		);
	}

	public static function getUsersToNotify( $event ) {
		
	}
}