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
	 * @var static WebSite instance to use for Singleton pattern
	 */
	private static $instance;

	/**
	 * Singleton pattern getter
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
	public function __construct() {}

	/**
	 * Render head item with metadata for Google Rich Snippet
	 *
	 * @param OutputPage OutputPage instance referencce
	 */
	function render( OutputPage &$out ) {
		$title = $out->getTitle();
		$server = $out->getConfig()->get( 'Server' );
		$scriptPath = $out->getConfig()->get( 'ScriptPath' );

		if ( $title instanceof Title && $title->isContentPage() ) {
			$website = [
				'@context'				=> 'http://schema.org',
				'@type'					 => 'WebSite',
				'url'						 => $server,
				'potentialAction' => [
					'@type'			 => 'SearchAction',
					'target'			=> $server . $scriptPath . '/index.php?search={search_term_string}',
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
