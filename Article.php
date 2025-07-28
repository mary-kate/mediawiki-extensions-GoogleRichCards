<?php
/**
 * GoogleRichCards
 * Google Rich Cards metadata generator for Articles
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

use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\OutputPage;
use MediaWiki\Title\Title;

class Article {
	/**
	 * @var static Article instance to use for Singleton pattern
	 */
	private static $instance;

	/**
	 * @var Title current instance of Title received from the RequestContext
	 */
	private $title;

	/**
	 * @var string Site name received from global $wgSitename
	 */
	private $sitename;

	/**
	 * @var string Server URL received from global $wgServer
	 */
	private $server;

	/**
	 * @var string Wiki logo path received from global $wgLogo
	 */
	private $logo;

	/**
	 * Singleon pattern getter
	 *
	 * @return Article
	 */
	public static function getInstance() {
		if ( !isset( self::$instance ) ) {
			self::$instance = new Article();
		}

		return self::$instance;
	}

	/**
	 * Class constructor
	 */
	public function __construct() {
		global $wgLogo, $wgServer, $wgSitename;

		$this->title = RequestContext::getMain()->getTitle();
		$this->sitename = $wgSitename;
		$this->server = $wgServer;
		$this->logo = $wgLogo;
	}

	/**
	 * Return creation time or 0 from current Article
	 *
	 * @return int
	 */
	public function getCTime() {
		$rev = MediaWikiServices::getInstance()->getRevisionLookup()->getFirstRevision( $this->title );
		$earliestRevTime = $rev ? $rev->getTimestamp() : null;

		if ( $earliestRevTime ) {
			$ctime = \DateTime::createFromFormat( 'YmdHis', $earliestRevTime );
			if ( $ctime ) {
				return $ctime->format( 'c' );
			}
		}

		return 0;
	}

	/**
	 * Return modification time or 0 from current Article
	 *
	 * @return int
	 */
	public function getMTime() {
		$mtime = \DateTime::createFromFormat( 'YmdHis', $this->title->getTouched() );
		if ( $mtime ) {
			return $mtime->format( 'c' );
		}
		return 0;
	}

	/**
	 * Return first image and its resolution from the current Article
	 *
	 * @param OutputPage &$out OutputPage instance reference
	 * @return array
	 */
	public function getIllustration( OutputPage &$out ) {
		$image = key( $out->getFileSearchOptions() );

		$image_url = $this->server . $this->logo; // MediaWiki logo to be used by default
		$image_width = 135; // Default max logo width
		$image_height = 135; // Default max logo height

		if ( $image ) {
			$image_object = MediaWikiServices::getInstance()->getRepoGroup()->findFile( $image );
			if ( $image_object ) {
				$image_url = $image_object->getFullURL();
				$image_width = $image_object->getWidth();
				$image_height = $image_object->getHeight();
			}
		}

		return [ $image_url, $image_width, $image_height ];
	}

	/**
	 * Render head item with metadata for Google Rich Snippet
	 *
	 * @param OutputPage OutputPage instance referencce
	 */
	function render( OutputPage &$out ) {
		if ( $this->title instanceof Title && $this->title->isContentPage() ) {
			$mtime = $this->getMtime();

			$created_timestamp = $this->getCTime();
			$modified_timestamp = $this->getMTime();

			$first_revision = MediaWikiServices::getInstance()->getRevisionLookup()->getFirstRevision( $this->title );
			$author = 'None';

			if ( $first_revision ) {
				$user = $first_revision->getUser();
				if ( $user ) {
					$author = $user->getName();
				}
			}

			$image = $this->getIllustration( $out );

			$article = [
				'@context'				 => 'http://schema.org',
				'@type'						=> 'Article',
				'mainEntityOfPage' => [
					'@type' => 'WebPage',
					'@id'	 => $this->title->getFullURL(),
				],
				'author'					 => [
					'@type' => 'Person',
					'name'	=> $author,
				],
				'headline'				 => $this->title->getText(),
				'dateCreated'			=> $created_timestamp,
				'datePublished'		=> $created_timestamp,
				'dateModified'		 => $modified_timestamp,
				'discussionUrl'		=> $this->title->getTalkPage()->getFullURL(),
				'image'						=> [
					'@type'	=> 'ImageObject',
					'url'		=> $image[0],
					'height' => $image[2],
					'width'	=> $image[1],
				],
				'publisher'				=> [
					'@type' => 'Organization',
					'name'	=> $this->sitename,
					'logo'	=> [
						'@type' => 'ImageObject',
						'url'	 => $this->server . $this->logo,
					],
				],
				'description'			=> $this->title->getText(),
			];

			$out->addHeadItem(
				'GoogleRichCardsArticle',
				'<script type="application/ld+json">' . json_encode( $article ) . '</script>'
			);
		}
	}
}
