<?php

require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/maintenance/Maintenance.php' );

class TestNotification extends Maintenance {

	/**
	 *
	 * @var User
	 */
	protected $agentUser = null;

	/**
	 *
	 * @var Title
	 */
	protected $title = null;

	/**
	 *
	 * @var type
	 */
	protected $extraParams = [];


	public function __construct() {
		parent::__construct();

		$this->addOption( "key", "The notification to trigger", true );
		$this->addOption( "agent", "The user that triggers the notification" );
		$this->addOption( "title", "The title to associate the notification with" );
		$this->addOption( "outputMail", "Whether the mails should be put out to the console", false, false );
	}

	public function execute() {
		$this->setupAlternateUserMailer();
		$this->makeAgentUser();
		$this->makeTitle();
		$this->createNotification();
	}

	protected function createNotification() {
		$notificationKey = $this->getOption( 'key' );
		$this->output( "Adding notification '$notificationKey'\n" );
		$this->output( "Agent is '{$this->agentUser->getName()}' (ID:{$this->agentUser->getId()})\n" );
		$this->output( "Subject title is '{$this->title->getPrefixedDbKey()}' (ID:{$this->title->getArticleId()})\n" );

		BSNotifications::notify(
			$notificationKey,
			$this->agentUser,
			$this->title,
			$this->makeExtraParams()
		);

		$this->output( "Done.\n" );
	}

	protected function makeAgentUser() {
		$agent = $this->getOption( 'agent', '' );
		if( !empty( $agent ) ) {
			$this->agentUser = User::newFromName( $agent );
			if( $this->agentUser instanceof User === false || $this->agentUser->getId() === 0 ) {
				throw  new Exception( "Invalid or not existing user: $agent" );
			}
		}
		else {
			$this->agentUser = User::newFromId( 1 );
		}
	}

	protected function makeTitle() {
		$title = $this->getOption( 'title', '' );
		if( !empty( $title ) ) {
			$this->title = Title::newFromText( $title );
		}
		else {
			$this->title = Title::newMainPage();
		}
	}

	protected function makeExtraParams() {
		return array(
					'affected-users' => [ 1 ]
				);
		return [];
	}

	protected function setupAlternateUserMailer() {
		if( !$this->getOption(  'outputMail' , false ) ) {
			return;
		}

		Hooks::register( 'AlternateUserMailer', function( $headers, $to, $from, $subject, $body ) {
			$this->outputMail( $headers, $to, $from, $subject, $body );
			return false;
		});

		//Do a test
		UserMailer::send( 
			[ new MailAddress( 'support@hallowelt.com') ],
			new MailAddress( 'info@hallowelt.com' ),
			'Hello World',
			'Lorem ipsum dolor sit amet'
		);
	}

	/**
	 *
	 * @param array $headers
	 * @param MailAddress[] $to
	 * @param MailAddress $from
	 * @param string $subject
	 * @param string $body
	 */
	protected function outputMail( $headers, $to, $from, $subject, $body ) {
		$out = [];
		$out[] = '###############';
		foreach( $headers as $headerName => $headerValue ) {
			$out[] = "$headerName: $headerValue";
		}

		$out[] = '---------------------------------------------';
		$out[] = 'FROM: '.$from->toString();

		$tos = [];
		foreach( $to as $mailAdress ) {
			$tos[] = $mailAdress->toString();
		}
		$out[] = 'TO: '.implode( '; ', $tos );
		$out[] = 'SUBJECT: '.$subject;
		$out[] = 'BODY:';
		$out[] = $body;
		$out[] = '';

		$this->output( implode( "\n", $out) );
	}
}

$maintClass = "TestNotification";
require_once ( DO_MAINTENANCE );
