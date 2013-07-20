<?php

/**
 * This class is designed to represent a Mediawiki Page
 * @author Addshore
 **/
class Page {

	/** @var Mediawiki siteUrl for associated site	 */
	public $site;
	/** @var string title of Page including namespace	 */
	public $title;
	/** @var string text of Page	 */
	public $text;
	/** @var string pageid for Page	 */
	public $pageid;
	/** @var string namespace id number eg. 2	 */
	public $nsid;
	/** @var string timestamp for the particular revision text we have got	 */
	public $timestamp;
	/** @var array of categories the page is in	 */
	public $categories;
	/** @var string current protection status	 */
	public $protection;
	/** @var WikibaseEntity entity that is associated with the page	 */
	public $entity;
	/** @var parser entity that is associated with the page	 */
	public $parsed;

	/**
	 * @param $site
	 * @param $title
	 */
	function __construct( $site , $title ) {
		$this->site = $site;
		$this->title = $title;
	}

	/**
	 * @return string The title with the namespace removed if possible
	 */
	function getTitleWithoutNamespace(){
		$this->nsid = $this->site->getNamespaceIdFromTitle($this->title);
		if($this->nsid != null && $this->nsid != '0'){
			$explode = explode(':', $this->title, '2');
			return $explode[1];
		}
		return $this->title;
	}

	/**
	 * @return string Load page from the api
	 */
	function load(){
		echo "Loading page ".$this->site->url." ".$this->title."\n";
		$param['titles'] = $this->title;

		$result = $this->site->doPropRevsions($param);

		foreach($result['query']['pages'] as $x){
			$this->nsid = $x['ns'];
			if( !isset( $x['missing'] ) ){
				$this->pageid = $x['pageid'];
				$this->text = $x['revisions']['0']['*'];
				$this->timestamp = $x['revisions']['0']['timestamp'];
			}
			else
			{
				//MSG page doesn't exist
			}
		}
		$this->normaliseTitleNamespace();
		return $this->text;
	}

	/**
	 * Parsers the current text. Sets and returns the parser object.
	 *
	 * @return parser
	 */
	function parse(){
		$parser = new parser($this->title,$this->text);
		$parser->parse();
		$this->parsed = $parser;
		return $this->parsed;
	}

	/**
	 * @return string Normalise the namespace of the title if possible.
	 */
	function normaliseTitleNamespace(){
		$this->nsid = $this->site->getNamespaceIdFromTitle($this->title);

		if($this->nsid != '0'){
			$siteNamespaces = $this->site->getNamespaces();
			$normalisedNamespace = $siteNamespaces[$this->nsid][0];

			$explosion = explode(':',$this->title ,2);
			$explosion[0] = $normalisedNamespace;
			$this->title = implode(':', $explosion);
		}
		return $this->title;

	}

	/**
	 * @return null|WikibaseEntity The entity that this page is included on
	 */
	function getEntity(){
		$q['action'] = 'query';
		$q['prop'] = 'pageprops';
		$q['titles'] = $this->title;
		$result = $this->site->doRequest($q);
		foreach($result['query']['pages'] as $page){
			if( isset( $page['pageprops']['wikibase_item'] ) ){
				$this->entity = new WikibaseEntity($this->site->family->getSiteFromUrl($this->site->wikibase),$page['pageprops']['wikibase_item']);
				return  $this->entity;
			}
		}
		return null;
	}

	/**
	 * @return array of interwikilinks [1] => array(site=>en,link=>Pagename) etc.
	 */
	//@todo add data about site type here i.e. wiki or wikivoyage?
	function getInterwikisFromtext(){
		if(!isset($this->text)){
			$this->load();
		}

		$toReturn = array();
		//@todo this list of langs should definatly come from a better place...
		preg_match_all('/\n\[\['.Globals::$regex['langs'].':([^\]]+)\]\]/'
			,$this->text,$matches);
		foreach($matches[0] as $key => $match){
			$toReturn[] = Array('site' => $matches[1][$key], 'link' => $matches[2][$key]);
		}
		return $toReturn;
	}

	/**
	 * Finds interwikis on the page and returns an array of pages for them
	 *
	 * @return array
	 */
	function getPagesFromInterwikiLinks(){
		$pages = array();

		$interwikis = $this->getInterwikisFromtext();
		foreach( $interwikis as $interwikiData ){
			$site = $this->site->family->getSiteFromSiteid($interwikiData['site'].$this->site->code);
			if($site instanceof Mediawiki){
				$pages[] = $site->getPage($interwikiData['link']);
			}
		}

		return $pages;
	}

	/**
	 * @return array of Pages linked to using inter project links
	 */
	function getPagesFromInterprojectLinks(){
		if(!isset($this->text)){
			$this->load();
		}
		$pages = array();

		preg_match_all('/\[\['.Globals::$regex['sites'].':('.Globals::$regex['langs'].':)?([^\]]+?)\]\]/i',$this->text,$matches);
		foreach($matches[0] as $key => $match){
			$parts = array();

			//set the site
			if( stristr($matches[1][$key], 'wikipedia') ){
				$parts['site'] = 'wiki';
			} else {
				$parts['site'] = strtolower( $matches[1][$key] );
			}
			//set the language
			if( $matches[3][$key] == '') {
				$parts['lang'] = $this->site->lang;
			} else {
				$parts['lang'] = $matches[3][$key];
			}
			$parts['title'] = $matches[4][$key];

			$site = $this->site->family->getSiteFromSiteid( $parts['lang'].$parts['site'] );
			if($site instanceof Mediawiki){
				$pages[] = $site->getPage( $parts['title'] );
			}
		}

		return $pages;
	}

	/**
	 * @return array of Pages linked to using inter project / page templates
	 */
	function getPagesFromInterprojectTemplates(){
		if(!isset($this->text)){
			$this->load();
		}
		$pages = array();

		preg_match_all('/\{\{(wikipedia|wikivoyage)(\|([^\]]+?))\}\}/i',$this->text,$matches);
		foreach($matches[0] as $key => $match){
			$parts = array();
			//set the site
			if( stristr($matches[1][$key], 'wikipedia') ){
				$parts['site'] = 'wiki';
			} else {
				$parts['site'] = strtolower( $matches[1][$key] );
			}
			$parts['lang'] = $this->site->lang;
			$parts['title'] = $matches[3][$key];

			$site = $this->site->family->getSiteFromSiteid( $parts['lang'].$parts['site'] );
			if($site instanceof Mediawiki){
				$pages[] = $site->getPage( $parts['title'] );
			}

		}

		return $pages;
	}

	/**
	 * Gets the current protection status of the page
	 */
	function getProtectionStatus(){
		//@todo write this
		////http://en.wikipedia.org/w/api.php?action=query&titles=Cyprus&prop=info&inprop=protection
		//checkout https://github.com/addshore/addwiki/blob/b8db6c00049d6ff2cefe92187e744c4c6693f815/classes/botclasses.php ln1083
		//return $this->protection;
	}

	/**
	 * @param null $hidden
	 * @return mixed
	 * @todo return an array of category objects which would extend Page
	 */
	function getCategories($hidden = null){
		$param['titles'] = $this->title;
		if($hidden === true){ $param['clshow'] = 'hidden';}
		elseif($hidden === false){ $param['clshow'] = '!hidden';}

		$result = $this->site->doPropCategories($param);

		foreach($result->value['query']['pages'] as $x){
			$this->pageid = $x['pageid'];
			$this->nsid = $x['nsid'];
			$this->categories = $x['categories'];
		}
		return $this->categories;
	}

	/**
	 * @param null $summary string to save the Page with
	 * @param bool $minor should be minor?
	 * @return string
	 */
	function save($summary = null, $minor = false){
		echo "Saved page ".$this->title."\n";
		return $this->site->doEdit($this->title,$this->text,$summary,$minor);
	}

	/**
	 * @param $text string to append to $text
	 */
	function appendText($text){
		if ( ! empty( $this ) ) {
			$this->text = $this->text.$text;
		}
	}

	/**
	 * @param $text string to prepend to $text
	 */
	function prependText($text){
		if ( ! empty( $this ) ) {
			$this->text = $text.$this->text;
		}
	}

	/**
	 * Empties the text of the page
	 */
	function emptyText(){
		$this->text = "";
	}

	/**
	 * Find a string
	 * @param $string string The string that you want to find.
	 * @return bool value (1 found and 0 not-found)
	 **/
	function findString( $string ){
		if( strstr( $this->text, $string ) )
			return 1;
		else
			return 0;
	}

	/**
	 * Replace a string
	 * @param $string string The string that you want to replace.
	 * @param $newstring string The string that will replace the present string.
	 * @return string the new text of page
	 **/
	function replaceString( $string, $newstring ){
		$this->text = str_replace( $string, $newstring, $this->text );
		return $this->text;
	}

	function pregReplace($patern, $replacment){
		$this->text = preg_replace($patern,$replacment,$this->text);
		return $this->text;
	}

	function removeRegexMatched($patern){
		return $this->pregReplace($patern,'');
	}

	/**
	 * Gets the entity for the article and removes all possible interwiki links
	 * from the page text.
	 */
	function removeEntityLinksFromText(){
		$baseEntity = $this->getEntity();
		if($baseEntity instanceof WikibaseEntity){
			$baseEntity->load();
			if( !isset($baseEntity->id) ){
				return false;
			}

			foreach($baseEntity->languageData['sitelinks'] as $sitelink){
				$site = $this->site->family->getSiteFromSiteid($sitelink['site']);
				$site->getSiteinfo();
				$lang = $site->lang;
				$titleEnd = $this->getTitleWithoutNamespace();
				$possibleNamespaces = $this->site->getNamespaces();
				$possibleNamespaces = $possibleNamespaces[$this->nsid];

				//@todo this could all be improved with something like getRegexForTitle or  getRegexForInterwikiLink
				foreach($possibleNamespaces as $namespace){
					if($namespace != ""){
						$titleVarient = $namespace.':'.$titleEnd;
					} else {
						$titleVarient = $titleEnd;
					}
					//@todo remember (zh-min-nan|nan) and (nb|no) (they are the same site)
					$lengthBefore = strlen($this->text);
					$removeLink = '/\n ?\[\['.$lang.' ?: ?'.str_replace(' ','( |_)',preg_quote($titleVarient,'/')).' ?\]\] ?/';
					$this->removeRegexMatched($removeLink);
					if($lengthBefore < strlen($this->text)){
						echo "Removed link! $lang:$titleVarient\n";
					}
				}

			}

			//Remove extra space we might have left at the end
			$this->pregReplace('/(\n\n)\n+$/',"$1");
			$this->pregReplace('/^(\n|\r){0,5}$/',"");

			return true;
		}
		return false;
	}
}