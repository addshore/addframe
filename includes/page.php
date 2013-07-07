<?php

/**
 * This class is designed to represet a Mediawiki Page
 * @author Addshore
 **/
class Page {

	/**
	 * @var string handel for associated site
	 */
	public $handel;
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

	function __construct( $handel , $title ) {
		$this->handel = $handel;
		$this->title = $title;
	}

	/**
	 * @return string text of Page
	 */
	function getText(){
		$param['titles'] = $this->title;

		$result = Globals::$Sites->getSite($this->handel)->api->doPropRevsions($param);

		foreach($result->value['query']['pages'] as $x){
			$this->pageid = $x['pageid'];
			$this->ns = $x['ns'];
			$this->text = $x['revisions']['0']['*'];
			$this->timestamp = $x['revisions']['0']['timestamp'];
			return $this->text;
		}
	}

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

		$result = Globals::$Sites->getSite($this->handel)->api->doPropCategories($param);

		foreach($result->value['query']['pages'] as $x){
			$this->pageid = $x['pageid'];
			$this->ns = $x['ns'];
			$this->categories = $x['categories'];
			return $this->categories;
		}
	}

	/**
	 * @param null $summary string to save the Page with
	 * @param bool $minor should be minor?
	 */
	function save($summary = null, $minor = false){
		Globals::$Sites->getSite($this->handel)->doEdit($this->title,$this->text,$summary,$minor);
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
	 * @param $string The string that you want to find.
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
	 * @param $string The string that you want to replace.
	 * @param $newstring The string that will replace the present string.
	 * @return the new text of page
	 **/
	function replaceString( $string, $newstring ){
		$this->text = str_replace( $string, $newstring, $this->text );
		return $this->text;
	}

	/**
	 * gets array of interwiki links from text
	 * @from https://github.com/addshore/addwiki/blob/b8db6c00049d6ff2cefe92187e744c4c6693f815/bot/wikidata/g.php ln140
	 * @return array of interwikilinks matches in text
	 * @author addshore
	 */
	function getInterwikiLinksFromText()
	{
		$return = Array();
		//@todo this langcode should be fetched on execution and be configurable from somewhere (not static here)
		preg_match_all("/\[\[(nostalgia|ten|aa|ab|ace|af|ak|als|am|an|ang|ar|arc|arz|as|ast|av|ay|az|ba|bar|bat-smg|bcl|be|be-x-old|bg|bh|bi|bjn|bm|bn|bo|bpy|br|bs|bug|bxr|ca|cbk-zam|cdo|ce|ceb|ch|cho|chr|chy|ckb|co|cr|crh|cs|csb|cu|cv|cy|da|de|diq|dsb|dv|dz|ee|el|eml|en|eo|es|et|eu|ext|fa|ff|fi|fiu-vro|fj|fo|fr|frp|frr|fur|fy|ga|gag|gan|gd|gl|glk|gn|got|gu|gv|ha|hak|haw|he|hi|hif|ho|hr|hsb|ht|hu|hy|hz|ia|id|ie|ig|ii|ik|ilo|io|is|it|iu|ja|jbo|jv|ka|kaa|kab|kbd|kg|ki|kj|kk|kl|km|kn|ko|koi|kr|krc|ks|ksh|ku|kv|kw|ky|la|lad|lb|lbe|lez|lg|li|lij|lmo|ln|lo|lt|ltg|lv|map-bms|mdf|mg|mh|mhr|mi|min|mk|ml|mn|mo|mr|mrj|ms|mt|mus|mwl|my|myv|mzn|na|nah|nap|nds|nds-nl|ne|new|ng|nl|nn|no|nov|nrm|nso|nv|ny|oc|om|or|os|pa|pag|pam|pap|pcd|pdc|pfl|pi|pih|pl|pms|pnb|pnt|ps|pt|qu|rm|rmy|rn|ro|roa-rup|roa-tara|ru|rue|rw|sa|sah|sc|scn|sco|sd|se|sg|sh|si|simple|sk|sl|sm|sn|so|sq|sr|srn|ss|st|stq|su|sv|sw|szl|ta|te|tet|tg|th|ti|tk|tl|tn|to|tpi|tr|ts|tt|tum|tw|ty|udm|ug|uk|ur|ve|vec|vep|vi|vls|vo|wa|war|wo|wuu|xal|xh|xmf|yi|yo|za|zea|zh|zh-classical|zh-min-nan|zh-yue|zu):([^\]]+)\]\]/i",$this->text,$match);
		//$match[0] is whole match [1] is langcode [2] is pagename
		foreach($match[0] as $key => $m)
		{
			//@todo re add a way to remove certain types of links from an array (sections and namespaces)
			$return[$match[1][$key]] = $match[2][$key];
		}
		return $return;
	}


	/**
	 * Gets an entity from this page
	 * @return WikibaseEntity
	 */
	function getEntityFromPage()
	{
		//@todo this can only work if we also know the pages language / sitelink code (could we use handel?)
		//return new WikibaseEntity('')
	}
}