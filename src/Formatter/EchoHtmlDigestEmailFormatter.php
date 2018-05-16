<?php

namespace BlueSpice\EchoConnector\Formatter;

use BlueSpice\EchoConnector\Formatter\EchoHTMLEmailFormatter;
use MediaWiki\MediaWikiServices;

class EchoHtmlDigestEmailFormatter extends \EchoHtmlDigestEmailFormatter {
	protected $config;

	public function __construct( \User $user, \Language $language, $digestMode ) {
		parent::__construct( $user, $language, $digestMode );

		$this->config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
	}

	protected function formatModels(array $models) {
		$intro = $this->msg('echo-email-batch-body-intro-' . $this->digestMode)
				->params($this->user->getName())
				->parse();
		$intro = nl2br($intro);

		$eventsByCategory = $this->groupByCategory($models);
		ksort($eventsByCategory);
		$digestList = $this->renderDigestList($eventsByCategory);

		$htmlFormatter = new EchoHTMLEmailFormatter($this->user, $this->language);

		$body = $this->renderBody(
				$this->language, $intro, $digestList, $this->renderAction(), $htmlFormatter->getFooter()
		);

		$subject = $this->msg('echo-email-batch-subject-' . $this->digestMode)
				->numParams(count($models), count($models))
				->text();

		return [
			'subject' => $subject,
			'body' => $body,
		];
	}

	protected function renderBody(\Language $language, $intro, $digestList, $action, $footer) {
		$path = $this->config->get( 'EchoHtmlMailTemplatePath' );
		$names = $this->config->get( 'EchoHtmlMailTemlateNames' );

		$templateParser = new \TemplateParser( $path );
		$html = $templateParser->processTemplate(
			$names['digest'], [
				'intro' => $intro,
				'digest_list' => $digestList,
				'actions' => $action,
				'footer' => $footer
			]
		);

		return $html;
	}

	/**
	 * @param EchoEventPresentationModel[] $models
	 * @return array [ 'category name' => EchoEventPresentationModel[] ]
	 */
	protected function groupByCategory($models) {
		$eventsByCategory = [];
		foreach ($models as $model) {
			$eventsByCategory[$model->getCategory()][] = $model;
		}
		return $eventsByCategory;
	}

	protected function renderDigestList( $eventsByCategory ) {
		$result = [];
		// build the html section for each category
		foreach ($eventsByCategory as $category => $models) {
			$output = $this->applyStyleToCategory(
				$this->getCategoryTitle($category, count($models))
			);
			foreach ($models as $model) {
				$output .= "\n" . $this->applyStyleToEvent($model);
			}
			$result[] = '<table border="0" width="100%">' . $output . '</table>';
		}

		return trim(implode("\n", $result));
	}

	protected function renderAction() {
		return \Html::element(
			'a', [
				'href' => \SpecialPage::getTitleFor('Notifications')->getFullURL('', false, PROTO_CANONICAL),
				'style' => EchoHtmlEmailFormatter::PRIMARY_LINK_STYLE,
			], $this->msg('echo-email-batch-link-text-view-all-notifications')->text()
		);
	}

	/**
	 * @param string $type Notification type
	 * @param int $count Number of notifications in this type's section
	 * @return string Formatted category section title
	 */
	protected function getCategoryTitle($type, $count) {
		return $this->msg("echo-category-title-$type")
			->numParams($count)
			->parse();
	}

}
