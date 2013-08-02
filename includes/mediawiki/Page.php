<?php

namespace Addframe;

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
	/** @var WikiText Wikitext for page */
	public $wikiText;
	/** @var string pageid for Page */
	protected $pageid;
	/** @var string namespace id number eg. 2 */
	protected $nsid;
	/** @var array of categories the page is in */
	protected $categories;
	/** @var Entity entity that is associated with the page */
	protected $entity;
	/** @var parser entity that is associated with the page */
	protected $parser;

	/**
	 * @param $site
	 * @param $title
	 */
	public function __construct( $site, $title ) {
		$this->site = $site;
		$this->title =  $title;
		$this->wikiText = new WikiText();
	}

	/**
	 * @return string
	 */
	public function getNsid() {
		if( $this->nsid == null ){
			$this->nsid = $this->site->getNamespaceIdFromTitle( $this->title );
		}
		return $this->nsid;
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
		if( $this->wikiText->getText() == null || $force == true ){
			echo "Loading page " . $this->site->url . " " . $this->title . "\n";
			$this->wikiText->setText( $this->getSite()->getPageTextFromPageTitle( $this->title ) );
		}
		return $this->wikiText->getText();
	}

	public function getTextWithExpandedTemplates(){
		return new WikiText( $this->getSite()->getPageTextFromPageTitle( $this->title, true) );
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
		$this->getNsid();

		if ( $this->nsid != null && $this->nsid != '0' ) {
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
		$result = $this->site->doRequest( $q );
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
	 * Parsers the current text. Sets and returns the parser object.
	 *
	 * @return parser
	 */
	public function parse() {
		$parser = new parser( $this->title, $this->getText() );
		$parser->parse();
		$this->parser = $parser;
		return $this->parser;
	}

	/**
	 * @return string Normalise the namespace of the title if possible.
	 */
	public function normaliseTitleNamespace() {
		$this->getNsid();

		if ( $this->nsid != '0' ) {
			$siteNamespaces = $this->site->requestNamespaces();
			$normalisedNamespace = $siteNamespaces[$this->nsid][0];

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
		$result = $this->site->doRequest( $q );
		foreach ( $result['query']['pages'] as $page ) {
			if ( isset( $page['pageprops']['wikibase_item'] ) ) {
				$this->entity = new Entity( $this->site->getWikibase(), $page['pageprops']['wikibase_item'] );
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

		$toReturn = array();
		//@todo this list of langs should definatly come from a better place...
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

		$result = $this->site->requestPropCategories( $param );

		foreach ( $result->value['query']['pages'] as $x ) {
			$this->pageid = $x['pageid'];
			$this->nsid = $x['nsid'];
			$this->categories = $x['categories'];
		}
		return $this->categories;
	}

	public function getCoordinates(){
		$params['titles'] = $this->title;
		$result = $this->site->requestPropCoordinates( $params );
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
		echo "Saved page " . $this->title . "\n";
		return $this->site->requestEdit( $this->title, $this->getText(), $summary, $minor );
	}

	/**
	 * Gets the entity for the article and removes all possible interwiki links
	 * from the page text.
	 */
	public function removeEntityLinksFromText() {
		$this->getText();
		$baseEntity = $this->getEntity();
		$counter = 0;

		if ( ! $baseEntity instanceof Entity ) {
				return false;
		}
		$baseEntity->load();

		foreach ( $baseEntity->getLanguageData('sitelinks') as $sitelink ) {
			$site = $this->site->family->getSiteFromSiteid( $sitelink['site'] );
			if( $site instanceof Site && $this->site->getType() == $site->getType() ){
				$iwPrefix = $site->getIwPrefix();
				$page = $site->newPageFromTitle( $sitelink['title'] );
				$titleEnd = $page->getTitleWithoutNamespace();
				$possibleNamespaces = $site->requestNamespaces();
				$possibleNamespaces = $possibleNamespaces[$page->getNsid()];

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
					} else if ( $iwPrefix == 'no' || $iwPrefix = 'nb'){
						$iwPrefix = '(nb|no)';
					}

					$lengthBefore = $this->wikiText->getLength();
					$removeLink = '/\n ?\[\[' . $iwPrefix . ' ?: ?' . str_replace( ' ', '( |_)', preg_quote( $titleVarient, '/' ) ) . ' ?\]\] ?/';
					$this->wikiText->removeRegexMatched( $removeLink );
					if ( $lengthBefore > $this->wikiText->getLength() ) {
						$counter = $counter + 1;
						echo "Removed link! $iwPrefix:$titleVarient\n";
					}
				}
			}

		}

		if( count( $this->getInterwikisFromtext() ) == 0 ){

			if( $this->getNsid() == 10 ){
				//Remove empty no include tags
				$this->wikiText->removeRegexMatched('/<noinclude>\s+?<\/noinclude>/');
			}

		$this->wikiText->removeRegexMatched('/<!-- ?(interwikis?( links?)?|other (wiki|language)s?) ?-->/i');
		$this->wikiText->trimWhitespace();

		}

		return $counter;
	}
}