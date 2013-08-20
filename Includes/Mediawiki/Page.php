<?php

namespace Addframe\Mediawiki;
use Addframe\Addframe;
use Addframe\Mediawiki\Wikibase\Entity;

/**
 * This class is designed to represent a Site Page
 * @author Addshore
 * @since 0.0.1
 **/
class Page {

	/** @var Site siteUrl for associated site */
	public $site;
	/** @var string title of Page including namespace */
	public $title;
	/** @var TextContent TextContent for page */
	public $content;
	/** @var string pageid for Page */
	protected $pageid;
	/** @var array of categories the page is in */
	protected $categories;
	/** @var Entity entity that is associated with the page */
	protected $entity;
	/** @var  Array info from prop=info */
	protected $pageinfo;

	/**
	 * @param Site $site
	 * @param string $title
	 */
	public function __construct( $site, $title ) {
		$this->site = $site;
		$this->title =  $title;
		$this->content = new TextContent();
	}

	/**
	 * Lazily fetch info from prop=info
	 * @param null|string $key of data to get from pageinfo
	 * @return Array
	 */
	public function getInfo( $key = null ) {
		if ( $this->pageinfo === null ) {
			$this->pageinfo = $this->site->getPageInfo( $this->getTitle() );
		}
		if( is_null( $key ) || !array_key_exists( $key, $this->pageinfo ) ){
			return $this->pageinfo;
		}
		return $this->pageinfo[ $key ];
	}

	/**
	 * Get the page id
	 * @return int
	 */
	public function getId() {
		$data = $this->getInfo();
		return $data['pageid'];
	}

	/**
	 * ContentModel for the page
	 * @return string
	 */
	public function getContentModel() {
		$data = $this->getInfo();
		return $data['contentmodel'];
	}

	/**
	 * This is typically the site's contentlanguage, but can
	 * be overridden by extension hooks.
	 * Returns language code
	 * @return string
	 */
	public function getPageLanguage() {
		$data = $this->getInfo();
		return $data['pagelanguage'];
	}

	/**
	 * Returns the value of when the page was last touched
	 * This is not equal to last edit
	 * @return string
	 * @todo This should be a timestamp object or something
	 */
	public function getTouched() {
		$data = $this->getInfo();
		return $data['touched'];
	}

	/**
	 * Current revision's page length
	 * @return int
	 * @todo integrate with TextContent object somehow
	 */
	public function getLength() {
		$data = $this->getInfo();
		return $data['length'];
	}

	/**
	 * Human friendly url, eg "en.wp.o/wiki/Title"
	 * @return string
	 */
	public function getPrettyUrl() {
		$data = $this->getInfo();
		return $data['fullurl'];
	}

	/**
	 * Namespace number that the page is in
	 * @return int
	 */
	public function getNamespace() {
		$data = $this->getInfo();
		return $data['ns'];
	}

	/**
	 * @return string
	 */
	public function getPageid() {
		return $this->pageid;
	}

	/**
	 * @return Site
	 */
	public function getSite() {
		return $this->site;
	}

	/**
	 * @param bool $force force getting new text?
	 * @return string
	 */
	public function getText( $force = false) {
		if( $this->content->getText() == null || $force == true ){
			Addframe::log( "Loading page " . $this->site->url . " " . $this->title . "\n", \KLogger::DEBUG );
			$this->content->setText( $this->getSite()->getPageTextFromPageTitle( $this->title ) );
		}
		return $this->content->getText();
	}

	public function getTextWithExpandedTemplates(){
		return new TextContent( $this->getSite()->getPageTextFromPageTitle( $this->title, true) );
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return string The title with the namespace removed if possible
	 */
	public function getTitleWithoutNamespace() {
		$namespace = $this->getNamespace();

		if ( $namespace != null && $namespace != '0' ) {
			$explode = explode( ':', $this->title, '2' );
			return $explode[1];
		}
		return $this->title;
	}

	public function isFullyEditProtected(){
		$q['action'] = 'query';
		$q['prop'] = 'info';
		$q['titles'] = $this->title;
		$q['inprop'] = 'protected';
		$result = $this->site->api->doRequest( $q );
		foreach( $result['query']['pages'] as $page ){
			if( isset( $page['protection'] ) ){
				foreach( $page['protection'] as $protection ){
					if( $protection['type'] == 'edit' && $protection['level'] == 'sysop'){
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * @return string Normalise the namespace of the title if possible.
	 */
	public function normaliseTitleNamespace() {
		$namespace = $this->getNamespace();

		if ( $namespace != '0' ) {
			$siteNamespaces = $this->site->getNamespaces();
			$normalisedNamespace = $siteNamespaces[ $namespace ][0];

			$explosion = explode( ':', $this->title, 2 );
			$explosion[0] = $normalisedNamespace;
			$this->title = implode( ':', $explosion );
		}
		return $this->title;

	}

	/**
	 * @return null|Entity The entity that this page is included on
	 */
	public function getEntity() {
		$q['action'] = 'query';
		$q['prop'] = 'pageprops';
		$q['titles'] = $this->title;
		$result = $this->site->api->doRequest( $q );
		foreach ( $result['query']['pages'] as $page ) {
			if ( isset( $page['pageprops']['wikibase_item'] ) ) {
				$this->entity = Entity::newFromId( $this->site->getWikibase(), $page['pageprops']['wikibase_item'] );
				return $this->entity;
			}
		}
		return null;
	}

	/**
	 * @return array of interwikilinks [1] => array(site=>en,link=>Pagename) etc.
	 */
	//@todo add data about site type here i.e. wiki or wikivoyage?
	public function getInterwikisFromtext() {
		$text = $this->getText();
		$text = preg_replace("/(<nowiki>.*?<\/nowiki>|<!--.*?-->)/is","",$text);

		$toReturn = array();
		preg_match_all( '/\n\[\[' . Regex::getLanguageRegexPart() . ':([^\]]+)\]\]/', $text, $matches );
		foreach ( $matches[0] as $key => $match ) {
			$toReturn[] = Array( 'site' => $matches[1][$key], 'link' => $matches[2][$key] );
		}
		return $toReturn;
	}

	/**
	 * Finds interwikis on the page and returns an array of pages for them
	 *
	 * @return array
	 */
	public function getPagesFromInterwikiLinks() {
		$pages = array();

		$interwikis = $this->getInterwikisFromtext();
		foreach ( $interwikis as $interwikiData ) {
			$site = $this->site->family->getSiteFromSiteid( $interwikiData['site'] . $this->site->getType() );
			if ( $site instanceof Site ) {
				$pages[] = $site->newPageFromTitle( $interwikiData['link'] );
			}
		}

		return $pages;
	}

	/**
	 * @return array of Pages linked to using inter project links
	 */
	public function getPagesFromInterprojectLinks() {
		$text = $this->getText();
		$pages = array();

		//We do not want to match our own site links so remove these from matches!
		$sitesArray = Regex::getSiteTypeArray();
		$thisTypeArray = array( $this->site->getType() );
		if( $thisTypeArray[0] == 'wiki' ){
			$thisTypeArray[] = 'wikipedia';
		}
		$sitesArray = array_diff( $sitesArray, $thisTypeArray );

		preg_match_all( '/\n\[\[(' . implode('|',$sitesArray) . '):(' . Regex::getLanguageRegexPart() . ':)?([^\]]+?)\]\]/i', $text, $matches );
		foreach ( $matches[0] as $key => $match ) {
			$parts = array();

			//set the site
			if ( stristr( $matches[1][$key], 'wikipedia' ) ) {
				$parts['site'] = 'wiki';
			} else {
				$parts['site'] = strtolower( $matches[1][$key] );
			}
			//set the language
			if ( $matches[3][$key] == '' ) {
				$parts['lang'] = $this->site->getLanguage();
			} else {
				$parts['lang'] = $matches[3][$key];
			}
			$parts['title'] = $matches[4][$key];

			$site = $this->site->family->getSiteFromSiteid( $parts['lang'] . $parts['site'] );
			if ( $site instanceof Site ) {
				$pages[] = $site->newPageFromTitle( $parts['title'] );
			}
		}

		return $pages;
	}

	/**
	 * @return array of Pages linked to using inter project / page templates
	 */
	public function getPagesFromInterprojectTemplates() {
		$text = $this->getText();
		$pages = array();

		preg_match_all( '/\{\{(wikipedia|wikivoyage)(\|([^\]]+?))\}\}/i', $text, $matches );
		foreach ( $matches[0] as $key => $match ) {
			$parts = array();
			//set the site
			if ( stristr( $matches[1][$key], 'wikipedia' ) ) {
				$parts['site'] = 'wiki';
			} else {
				$parts['site'] = strtolower( $matches[1][$key] );
			}
			$parts['lang'] = $this->site->getLanguage();
			$parts['title'] = $matches[3][$key];

			$site = $this->site->family->getSiteFromSiteid( $parts['lang'] . $parts['site'] );
			if ( $site instanceof Site ) {
				$pages[] = $site->newPageFromTitle( $parts['title'] );
			}

		}

		return $pages;
	}

	/**
	 * @param null $hidden
	 * @return mixed
	 * @todo return an array of category objects which would extend Page
	 * @todo refactor into site->getCategoriesFromPageTitle
	 */
	public function getCategories( $hidden = null ) {
		$param['titles'] = $this->title;
		if ( $hidden === true ) {
			$param['clshow'] = 'hidden';
		} elseif ( $hidden === false ) {
			$param['clshow'] = '!hidden';
		}

		$result = $this->site->api->requestPropCategories( $param );

		foreach ( $result->value['query']['pages'] as $x ) {
			$this->pageid = $x['pageid'];
			$this->categories = $x['categories'];
		}
		return $this->categories;
	}

	public function getCoordinates(){
		$params['titles'] = $this->title;
		$result = $this->site->api->requestPropCoordinates( $params );
		foreach( $result['query']['pages'] as $page ){
			if(array_key_exists( 'coordinates', $page )){
				return $page['coordinates'];
			}
		}
		return null;
	}

	/**
	 * @param null $summary string to save the Page with
	 * @param bool $minor should be minor?
	 * @return string
	 */
	public function save( $summary = null, $minor = false ) {
		Addframe::log( "Saved page " . $this->title . "\n" );
		return $this->site->doEdit( $this->title, $this->getText(), $summary, $minor );
	}

	/**
	 * Gets the entity for the article and removes all possible interwiki links
	 * from the page text.
	 */
	public function removeEntityLinksFromText() {
		$this->getText();
		$baseEntity = $this->getEntity();
		$counter = 0;

		if ( !$baseEntity instanceof Entity ) {
				return false;
		}
		$baseEntity->load();

		foreach ( $baseEntity->getLanguageData('sitelinks') as $sitelink ) {
			$linkSite = $this->site->family->getSiteFromSiteid( $sitelink['site'] );
			if( $linkSite instanceof Site && $this->site->getType() == $linkSite->getType() ){
				$iwPrefix = $linkSite->getIwPrefix();
				$page = $linkSite->newPageFromTitle( $sitelink['title'] );
				$titleEnd = $page->getTitleWithoutNamespace();
				$possibleNamespaces = $linkSite->getNamespaces();
				$possibleNamespaces = $possibleNamespaces[ $page->getNamespace() ];

				//@todo this could all be improved with something like getRegexForTitle or  getRegexForInterwikiLink
				foreach ( $possibleNamespaces as $namespace ) {
					if ( $namespace != "" ) {
						$titleVarient = $namespace . ':' . $titleEnd;
					} else {
						$titleVarient = $titleEnd;
					}

					//@todo this code is wikimedia specific
					if( $iwPrefix == 'zh-min-nan' || $iwPrefix == 'nan' ){
						$iwPrefix = '(zh-min-nan|nan)';
					} else if ( $iwPrefix == 'no' || $iwPrefix == 'nb'){
						$iwPrefix = '(nb|no)';
					}

					$lengthBefore = $this->content->getLength();
					$removeLink = '/\n ?\[\[' . $iwPrefix . ' ?: ?' . str_replace( ' ', '( |_)', preg_quote( $titleVarient, '/' ) ) . ' ?\]\] ?/';
					$this->content->removeRegexMatched( $removeLink );
					if ( $lengthBefore > $this->content->getLength() ) {
						$counter = $counter + 1;
						echo "Removed link! $iwPrefix:$titleVarient\n";
					}
				}
			}

		}

		if( count( $this->getInterwikisFromtext() ) == 0 ){

			if( $this->getNamespace() == 10 ){
				//Remove empty no include tags
				$this->content->removeRegexMatched('/<noinclude>\s+?<\/noinclude>/');
			}

		$this->content->removeRegexMatched('/<!-- ?(interwikis?( links?)?|other (wiki|language)s?) ?-->/i');
		$this->content->trimWhitespace();

		}

		return $counter;
	}

	//todo add whatlinkshere() https://github.com/addshore/addwiki/blob/PreRewrite/classes/botclasses.php#L333
	//todo getSubpages https://github.com/addshore/addwiki/blob/PreRewrite/classes/botclasses.php#L405
	//todo nobots https://github.com/addshore/addwiki/blob/PreRewrite/classes/botclasses.php#L474
	//todo purge https://github.com/addshore/addwiki/blob/PreRewrite/classes/botclasses.php#L503
	//todo getTransclusions https://github.com/addshore/addwiki/blob/PreRewrite/classes/botclasses.php#L531
}