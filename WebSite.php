<?php
/**
 * GoogleRichCards
 * Google Rich Cards metadata generator for WebSites search
 *
 * PHP version 5.4
 *
 * @category Extension
 * @package GoogleRichCards
 * @author Igor Shishkin <me@teran.ru>
 * @license GPL http://www.gnu.org/licenses/gpl.html
 * @link https://github.com/teran/mediawiki-GoogleRichCards
 */

namespace MediaWiki\Extension\GoogleRichCards;

use MediaWiki\Output\OutputPage;
use MediaWiki\Title\Title;

class WebSite {
	/**
	 * @var static Article instance to use for Singleton pattern
	 */
	private static $instance;

	/**
	 * @var Title current instance of Title received from global $wgTitle
	 */
	private $title;

	/**
	 * @var string Server URL received from global $wgServer
	 */
	private $server;

	/**
	 * Singleon pattern getter
	 *
	 * @return WebSite
	 */
	public static function getInstance() {
		if ( !isset( self::$instance ) ) {
			self::$instance = new WebSite();
		}

		return self::$instance;
	}

	/**
	 * Class constructor
	 */
	public function __construct() {
		global $wgServer, $wgTitle;

		$this->title = $wgTitle;
		$this->server = $wgServer;
	}

	/**
	 * Render head item with metadata for Google Rich Snippet
	 *
	 * @param OutputPage OutputPage instance referencce
	 */
	function render( OutputPage &$out ) {
		if ( $this->title instanceof Title && $this->title->isContentPage() ) {
			$website = [
				'@context'				=> 'http://schema.org',
				'@type'					 => 'WebSite',
				'url'						 => $this->server,
				'potentialAction' => [
					'@type'			 => 'SearchAction',
					'target'			=> $this->server . '/index.php?search={search_term_string}',
					'query-input' => 'required name=search_term_string',
				]
			];

			$out->addHeadItem(
				'GoogleRichCardsWebSite',
				'<script type="application/ld+json">' . json_encode( $website ) . '</script>'
			);
		}
	}
}
