<?php

/**
 * This class is designed to represent a Mediawiki Page
 * @author Addshore
 **/
class Page {

	/**
	 * @var string siteHandel for associated site
	 */
	public $siteHandel;
	/**
	 * @var string title of Page
	 */
	public $title;
	/**
	 * @var string text of Page
	 */
	public $text;
	/**
	 * @var string pageid for Page
	 */
	public $pageid;
	/**
	 * @var string namespace id number eg. 2
	 */
	public $ns;
	/**
	 * @var string timestamp for the particular revision text we have got
	 */
	public $timestamp;
	/**
	 * @var array of categories the page is in
	 */
	public $categories;
	/**
	 * @var string current protection status
	 */
	public $protection;

	/**
	 * @param $siteHandel
	 * @param $title
	 */
	function __construct( $siteHandel , $title ) {
		$this->siteHandel = $siteHandel;
		$this->title = $title;
	}

	//@todo load as much stuff as we can here
	//function loadPage(){

	//}

	/**
	 * @return string Load page from the api
	 */
	function load(){
		$param['titles'] = $this->title;

		$result = Globals::$Sites->getSite($this->siteHandel)->doPropRevsions($param);

		foreach($result->value['query']['pages'] as $x){
			$this->ns = $x['ns'];
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
		return $this->text;
	}

	/**
	 * @return array of interwikilinks [1] => array(site=>en,link=>Pagename) etc.
	 */
	function getInterwikisFromtext(){
		$toReturn = array();
		preg_match_all('/\n\[\[([^:]+):([^\]]+)\]\]/',$this->text,$matches);
		foreach($matches[0] as $key => $match){
			$toReturn[] = Array('site' => $matches[1][$key], 'link' => $matches[2][$key]);
		}
		return $toReturn;
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

		$result = Globals::$Sites->getSite($this->siteHandel)->doPropCategories($param);

		foreach($result->value['query']['pages'] as $x){
			$this->pageid = $x['pageid'];
			$this->ns = $x['ns'];
			$this->categories = $x['categories'];
		}
		return $this->categories;
	}

	/**
	 * @param null $summary string to save the Page with
	 * @param bool $minor should be minor?
	 */
	function save($summary = null, $minor = false){
		Globals::$Sites->getSite($this->siteHandel)->doEdit($this->title,$this->text,$summary,$minor);
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
}