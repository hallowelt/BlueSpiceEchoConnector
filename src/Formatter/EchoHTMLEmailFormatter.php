<?php

namespace BlueSpice\EchoConnector\Formatter;
use MediaWiki\MediaWikiServices;

use \BlueSpice\EchoConnector\EchoEventPresentationModel as BSEchoPresentationModel;

class EchoHTMLEmailFormatter extends \EchoHtmlEmailFormatter {
	protected $config;

	public function __construct(\User $user, \Language $language) {
		parent::__construct($user, $language);

		$this->config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
	}
	protected function formatModel( \EchoEventPresentationModel $model ) {
		if( $model instanceof BSEchoPresentationModel ) {
			$model->setDistributionType( 'email' );
			$model->setEmailFormat( 'html' );
		}

		$subject = $model->getHeaderMessage()->parse();

		$intro = $model->getHeaderMessage()->parse();

		$bodyMsg = $model->getBodyMessage();
		$summary = $bodyMsg ? $bodyMsg->parse() : '';

		$actions = [];

		$primaryLink = $model->getPrimaryLinkWithMarkAsRead();
		if ( $primaryLink ) {
			$actions[] = $this->renderLink( $primaryLink, self::PRIMARY_LINK_STYLE );
		}

		foreach ( array_filter( $model->getSecondaryLinks() ) as $secondaryLink ) {
			$actions[] = $this->renderLink( $secondaryLink, self::SECONDARY_LINK_STYLE );
		}

		$iconUrl = wfExpandUrl(
			\EchoIcon::getUrl( $model->getIconType(), $this->language->getCode() ),
			PROTO_CANONICAL
		);

		$body = $this->renderBody(
			$this->language,
			$iconUrl,
			$summary,
			implode( "&nbsp;&nbsp;", $actions ),
			$intro,
			$this->getFooter()
		);

		return [
			'body' => $body,
			'subject' => $subject,
		];
	}

	protected function renderBody( \Language $lang, $emailIcon, $summary, $action, $intro, $footer ) {
		$path = $this->config->get( 'EchoHtmlMailTemplatePath' );
		$names = $this->config->get( 'EchoHtmlMailTemlateNames' );

		$templateParser = new \TemplateParser( $path );
		$html =  $templateParser->processTemplate(
			$names['single'],
			[
				'icon_url' => $emailIcon,
				'header' => $intro,
				'body' => $summary,
				'actions' => $action,
				'footer' => $footer
			]
		);

		return $html;
	}

	protected function renderLink( $link, $style ) {
		return \Html::element(
			'a',
			[
				'href' => wfExpandUrl( $link['url'], PROTO_CANONICAL ),
				'style' => $style,
			],
			$link['label']
		);
	}
}