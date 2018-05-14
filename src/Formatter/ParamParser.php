<?php

namespace BlueSpice\EchoConnector\Formatter;

use MediaWiki\MediaWikiServices;
use BlueSpice\EchoConnector\ParamParserRegistry;

/**
 * This class deals with how params are displayed in the
 * final notification/email displayed to the user
 */
class ParamParser {

	protected $event;
	protected $message;
	protected $distributionType;
	protected $emailFormat;

	protected $linkRenderer;

	protected $paramParserRegistry;

	public function __construct( $event, $distributionType = 'web', $emailFormat = \EchoEmailFormat::PLAIN_TEXT ) {
		$this->event = $event;
		$this->linkRenderer = MediaWikiServices::getInstance()
			->getService( 'LinkRenderer' );

		$this->distributionType = $distributionType;
		$this->emailFormat = $emailFormat;

		$this->getParamParserRegistry();
	}

	public function setDistributionType( $type ) {
		$this->distributionType = $type;
	}

	public function setEmailFormat( $format ) {
		$this->emailFormat = $format;
	}

	public function parseParam( $message, $param ) {
		$this->message = $message;

		//If param is registered with another extension
		//let it do the parsing
		if( $this->paramParserRegistry->hasKey( $param ) ) {
			return call_user_func_array(
				$this->paramParserRegistry->getValue( $param ),
				[ $message, $param, $this->distributionType ]
			);
		}

		switch( $param ) {
			case 'title':
				$this->parseTitle();
				break;
			case 'agent':
				$this->parseAgent();
				break;
			case 'titlelink':
				$this->parseTitleLink();
				break;
			case 'agentlink':
				$this->parseAgentLink();
				break;
			case 'difflink':
				$this->parseDiffLink();
				break;
			case 'newtitle':
				$this->parseNewTitle();
				break;
			case 'newtitlelink':
				$this->parseNewTitleLink();
				break;
			case 'user':
			case 'username':
				$this->parseUserName();
				break;
			case 'userlink':
				$this->parseUserLink();
				break;
			default:
				//Just display the param value as-is
				$extra = $this->event->getExtra();
				if( isset( $extra[$param] ) ) {
					$value = $extra[$param];
				} else {
					$value = '';
				}

				$this->message->params( $value );
		}
	}

	protected function parseTitle() {
		$title = $this->event->getTitle();
		if( $title instanceof \Title ) {
			return $this->message->params( $title->getPrefixedText() );
		}

		//Check if there is title in extra params
		$extra = $this->event->getExtra();
		if( isset( $extra['title'] ) ) {
			$title = $extra['title'];
			if( $title instanceof \Title ) {
				$this->message->params( $title->getPrefixedText() );
			}
		}
	}

	protected function parseAgent() {
		$agent = $this->event->getAgent();
		if( $agent instanceof \User ) {
			$this->message->params( $agent->getName() );
		}
	}

	protected function parseTitleLink() {
		$title = $this->event->getTitle();
		if( $title instanceof \Title ) {
			if( $this->distributionType == 'email' ) {
				$this->message->params( $title->getFullURL() );
			} else {
				$anchor = $this->getAnchor( $title );
				$this->message->params( $anchor );
			}
		}
	}

	protected function parseAgentLink() {
		$agent = $this->event->getAgent();
		if( $agent instanceof \User ) {
			$userpage = $agent->getUserPage();
			if( $this->distributionType == 'email'
					&& $this->emailFormat == \EchoEmailFormat::PLAIN_TEXT ) {
				$this->message->params( $userpage->getFullURL() );
			} else {
				$anchor = $this->getAnchor( $userpage );
				$this->message->params( $anchor );
			}
		}
	}

	protected function parseDiffLink() {
		if( isset( $this->event->getExtra()['difflink'] ) ) {
			$diffparams = $this->event->getExtra()['difflink'];
			$title = $this->event->getTitle();
			if( $title instanceof \Title ) {
				if( $this->distributionType == 'email'
					&& $this->emailFormat == \EchoEmailFormat::PLAIN_TEXT ) {
					$this->message->params(
						$title->getFullURL( [
							'type' => 'revision',
							'diff' => $diffparams['diff'],
							'oldid' => $diffparams['oldid']
						] )
					);
				} else {
					$anchor = $this->getAnchor(
						$title,
						null,
						[],
						[
							'type' => 'revision',
							'diff' => $diffparams['diff'],
							'oldid' => $diffparams['oldid']
						]
					);
					$this->message->params( $anchor );
				}
			}
		}
	}

	protected function parseNewTitle() {
		if( isset( $this->event->getExtra()['newtitle'] ) ) {
			$newTitle = $this->event->getExtra()['newtitle'];
			if( $newTitle instanceof \Title ) {
				$this->message->params( $newTitle->getPrefixedText() );
			}
		}
	}

	protected function parseNewTitleLink() {
		if( isset( $this->event->getExtra()['newtitle'] ) ) {
			$newTitle = $this->event->getTitle();
			if( $newTitle instanceof \Title ) {
				if( $this->distributionType == 'email'
					&& $this->emailFormat == \EchoEmailFormat::PLAIN_TEXT ) {
					$this->message->params( $newTitle->getFullURL() );
				} else {
					$anchor = $this->getAnchor( $newTitle );
					$this->message->params( $anchor );
				}
			}
		}
	}

	protected function parseUserLink() {
		if( isset( $this->event->getExtra()['user'] ) ) {
			$user = $this->event->getExtra()['user'];
			if( !$user instanceof \User ) {
				return;
			}

			$userpage = $user->getUserPage();
			if( $userpage instanceof \Title ) {
				if( $this->distributionType == 'email'
					&& $this->emailFormat == \EchoEmailFormat::PLAIN_TEXT ) {
					$this->message->params( $userpage->getFullURL() );
				} else {
					$anchor = $this->getAnchor( $userpage );
					$this->message->params( $anchor );
				}
			}
		}
	}

	protected function parseUserName() {
		if( isset( $this->event->getExtra()['user'] ) ) {
			$user = $this->event->getExtra()['user'];
			if( $user instanceof \User ) {
				$this->message->params( $user->getName() );
			}
		}
	}

	protected function getAnchor( $title, $text = '' ) {
		if( !$text ) {
			$text = $title->getPrefixedText();
		}
		return $this->linkRenderer->makeLink( $title, $text );
	}

	/**
	 * This attribute exists so that extensions could add
	 * their params, and ways to parse them without implementing
	 * full-blown PresentationModel
	 */
	protected  function getParamParserRegistry() {
		$paramParserRegistry = new ParamParserRegistry(
			'BlueSpiceEchoConnectorParamParsers'
		);

		$this->paramParserRegistry = $paramParserRegistry;
	}
}
