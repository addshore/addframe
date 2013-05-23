<?php

class wiki {
    private $http;
    private $token;
    private $ecTimestamp;
    public $url;

    /**
     * This is our constructor.
     * @return void
     **/
    function __construct ($url,$hu=null,$hp=null) {
        $this->http = new http;
        $this->token = null;
        $this->url = $url;
        $this->ecTimestamp = null;
        if ($hu!==null)
            $this->http->setHTTPcreds($hu,$hp);
    }

	function api ($query,$post=null){
		$query['format'] = 'php';
		$query = "?".http_build_query($query);
        if ($post==null)
            $ret = $this->http->get($this->url.$query);
        else
            $ret = $this->http->post($this->url.$query,$post);
        return unserialize($ret);
	}
	
	function query ($query,$post=null){
		$query['action'] = 'query';
		return $this->api($query,$post);
	}
	function listt ($query,$post=null){
		$query['action'] = 'list';
		return $this->api($query,$post);
	}
	function prop ($query,$post=null){
		$query['action'] = 'prop';
		return $this->api($query,$post);
	}
	function meta ($query,$post=null){
		$query['action'] = 'meta';
		return $this->api($query,$post);
	}
	
	//Log in to the given api
	function login ($username,$password) {
		$query['action'] = 'login';
		$post['lgname'] = $username;
		$post['lgpassword'] = $password;
		$r = $this->api($query,$post);
        /* This is now required - see https://bugzilla.wikimedia.org/show_bug.cgi?id=23076 */
        if ($r['login']['result'] == 'NeedToken') {
            $post['lgtoken'] = $r['login']['token'];
            $r = $this->api($query,$post);
        }
        if ($r['login']['result'] != 'Success') {
			//TODO error handeling instead fo just die()
            echo "Login error: \n";
            print_r($r);
            die();
        } else {
            return $r;
        }
    }
	
	//Log out of the given api
	function logout () {
		$query['action'] = 'logout';
		return $this->api($query);
	}
	
	function edittitle ($title,$text,$summary = '',$minor = false,$bot = true,$section = null,$detectEC=true,$maxlag='5') {
        if ($this->token==null) {
            $this->token = $this->gettoken();
        }
		$post['title'] = $title;
		$post['text'] = $text;
		$post['token'] = $this->token;
		$post['summary'] = $summary;
		if($minor == true){$post['minor'] = '1';}
		if($bot == true){$post['bot'] = '1';}
        if ($section != null) {$post['section'] = $section;}
        if ($this->ecTimestamp != null && $detectEC == true) {
            $post['basetimestamp'] = $this->ecTimestamp;
            $this->ecTimestamp = null;
        }
        if ($maxlag!='') {$query['maxlag'] = $maxlag;}
		return $this->api($query,$post);
    }
	
	//PROP
	function categories (){}
	//$titles is a | seperated list of categories to get info for
	function categoryinfo ($titles){return prop('categoryinfo',"titles=".implode('|',$titles));}
	function coordinates (){}
	function duplicatefiles (){}
	function extlinks (){}
	function extracts (){}
	function flagged (){}
	function globalusage (){}
	function imageinfo (){}
	function images (){}
	function info (){}
	function iwlinks (){}
	function langlinks (){}
	function links (){}
	function pageimages (){}
	function pageprops (){}
	function revisions (){}
	function stashimageinfo (){}
	function templates (){}
	function transcodestatus (){}
	function videoinfo (){}
	
	
	//LIST
	function abusefilters (){}
	function abuselog (){}
	function allcategories (){}
	function allimages (){}
	function alllinks (){}
	function allpages (){}
	function alltransclusions (){}
	function allusers (){}
	function articlefeedbackv5viewactivity(){}
	function articlefeedbackv5viewfeedback(){}
	function backlinks(){}
	function blocks (){}
	function categorymembers (){}
	function centralnoticelogs (){}
	function checkuser (){}
	function checkuserlog (){}
	function deletedrevs (){}
	function embeddedin (){}
	function exturlusage (){}
	function filearchive (){}
	function gadgetcategories (){}
	function gadgets (){}
	function geosearch (){}
	function globalblocks (){}
	function globalgroups (){}
	function imageusage (){}
	function iwbacklinks (){}
	function langbacklinks (){}
	function logevents (){}
	function oldreviewedpages (){}
	function pagepropnames (){}
	function pageswithprop (){}
	function protectedtitles (){}
	function querypage (){}
	function random (){}
	function recentchanges (){}
	function search (){}
	function tags (){}
	function usercontribs (){}
	function users (){}
	function watchlist (){}
	function watchlistraw (){}
	function wikisets (){}
	//META
	function allmessages (){}
	function siteinfo (){}
	function userinfo (){}
	function globaluserinfo (){}
	function wikibase (){}

	//Modules : continuation
	function expandtemplates ($text) {return action ('expandtemplates',"text=".$text);}
	function compare () {}
	//Gets tokens for data-modifying actions
	//$type is block, delete, deleteglobalaccount, edit, email, import, move, options, patrol, protect, setglobalaccountstatus, unblock, watch
	function tokens ($type='edit') { 
		$query['action'] = 'tokens';
		$query['type'] = $type;
		$return = api( 'tokens',"type=$type");
		print_r($return);
		// $this->token['type'] = $return something
		//TODO return the token
	}
	
	//from wikibot classes
	function gettoken () {
		$query['prop'] = 'info';
		$query['intoken'] = 'edit';
		$query['titles'] = 'Main Page';
        $x = $this->query($query);
        foreach ($x['query']['pages'] as $ret) {
            return $ret['edittoken'];
        }
    }
	
	//Purge the cache for the given titles.
	function purge ($titles) {
		$return = action ( 'purge', "titles=".implode('|',$titles));
		print_r($return);
	}
	function rollback () {}
	function delete () {}
	function undelete () {}
	function protect () {}
	function block () {}
	function unblock () {}
	function move () {}
	function edit () {}
	function upload () {}
	function filerevert () {}
	function emailuser () {}
	function watch () {}
	function patrol () {}
	function import () {}
	function userrights () {}
	function options () {}
	function sitematrix () {}
	function titleblacklist () {}
	function transcodereset () {}
	function emailcapture () {}
	function deleteglobalaccount () {}
	function setglobalaccountstatus () {}
	function abusefilterchecksyntax () {}
	function abusefilterevalexpression () {}
	function abusefiltercheckmatch () {}
	function userdailycontribs () {}
	function clicktracking () {}
	function articlefeedbackv5addflagnote () {}
	function articlefeedbackv5flagfeedback () {}
	function articlefeedbackv5 () {}
	function visualeditor () {}
	function wikilove () {}
	function wikiloveimagelog () {}
	function markashelpful  () {}
	function getmarkashelpfulitem () {}
	function mobileview () {}
	function featuredfeed () {}
	function pagetriagelist () {}
	function pagetriagestats () {}
	function pagetriageaction () {}
	function pagetriagetemplate () {}
	function pagetriagetagging () {}
	function deleteeducation () {}
	function enlist() {}
	function refresheducation () {}
	function scribuntoconsole() {}
	function parsevalue () {}
	function stabilize () {}
	function review () {}
	function reviewactivity () {}
	function flagconfig () {}
	function centralnoticeallocations () {}
	function centralnoticequerycampaign () {}
	
}

class http {
    private $ch;
    private $uid;
    public $cookie_jar;
    public $postfollowredirs;
    public $getfollowredirs;
    public $quiet=true;

    function data_encode ($data, $keyprefix = "", $keypostfix = "") {
        assert( is_array($data) );
        $vars=null;
        foreach($data as $key=>$value) {
            if(is_array($value))
                $vars .= $this->data_encode($value, $keyprefix.$key.$keypostfix.urlencode("["), urlencode("]"));
            else
                $vars .= $keyprefix.$key.$keypostfix."=".urlencode($value)."&";
        }
        return $vars;
    }

    function __construct () {
        $this->ch = curl_init();
        $this->uid = dechex(rand(0,99999999));
        curl_setopt($this->ch,CURLOPT_COOKIEJAR,'/tmp/addwikibot.cookies.'.$this->uid.'.dat');
        curl_setopt($this->ch,CURLOPT_COOKIEFILE,'/tmp/addwikibot.cookies.'.$this->uid.'.dat');
        curl_setopt($this->ch,CURLOPT_MAXCONNECTS,100);
        curl_setopt($this->ch,CURLOPT_CLOSEPOLICY,CURLCLOSEPOLICY_LEAST_RECENTLY_USED);
        $this->postfollowredirs = 0;
        $this->getfollowredirs = 1;
        $this->cookie_jar = array();
    }

    function post ($url,$data) {
        $time = microtime(1);
        curl_setopt($this->ch,CURLOPT_URL,$url);
        curl_setopt($this->ch,CURLOPT_USERAGENT,'Addbot Wikimedia Bot');
        /* Crappy hack to add extra cookies, should be cleaned up */
        $cookies = null;
        foreach ($this->cookie_jar as $name => $value) {
            if (empty($cookies))
                $cookies = "$name=$value";
            else
                $cookies .= "; $name=$value";
        }
        if ($cookies != null)
            curl_setopt($this->ch,CURLOPT_COOKIE,$cookies);
        curl_setopt($this->ch,CURLOPT_FOLLOWLOCATION,$this->postfollowredirs);
        curl_setopt($this->ch,CURLOPT_MAXREDIRS,10);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($this->ch,CURLOPT_TIMEOUT,30);
        curl_setopt($this->ch,CURLOPT_CONNECTTIMEOUT,10);
        curl_setopt($this->ch,CURLOPT_POST,1);
        curl_setopt($this->ch,CURLOPT_POSTFIELDS, $data);
        $data = curl_exec($this->ch);
    if (!$this->quiet)
            echo 'POST: '.$url.' ('.(microtime(1) - $time).' s) ('.strlen($data)." b)\n";
        return $data;
    }

    function get ($url) {
        $time = microtime(1);
        curl_setopt($this->ch,CURLOPT_URL,$url);
        curl_setopt($this->ch,CURLOPT_USERAGENT,'php wikibot classes');
        /* Crappy hack to add extra cookies, should be cleaned up */
        $cookies = null;
        foreach ($this->cookie_jar as $name => $value) {
            if (empty($cookies))
                $cookies = "$name=$value";
            else
                $cookies .= "; $name=$value";
        }
        if ($cookies != null)
            curl_setopt($this->ch,CURLOPT_COOKIE,$cookies);
        curl_setopt($this->ch,CURLOPT_FOLLOWLOCATION,$this->getfollowredirs);
        curl_setopt($this->ch,CURLOPT_MAXREDIRS,10);
        curl_setopt($this->ch,CURLOPT_HEADER,0);
        curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($this->ch,CURLOPT_TIMEOUT,30);
        curl_setopt($this->ch,CURLOPT_CONNECTTIMEOUT,10);
        curl_setopt($this->ch,CURLOPT_HTTPGET,1);
        $data = curl_exec($this->ch);
        if (!$this->quiet)
            echo 'GET: '.$url.' ('.(microtime(1) - $time).' s) ('.strlen($data)." b)\n";
        //}
        return $data;
    }

    function setHTTPcreds($uname,$pwd) {
        curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($this->ch, CURLOPT_USERPWD, $uname.":".$pwd);
    }

    function __destruct () {
        curl_close($this->ch);
        @unlink('/tmp/cluewikibot.cookies.'.$this->uid.'.dat');
    }
}
	
//Select a list of all redirects to $title in $namespace
//select page_namespace,page_title from redirect,page where rd_title='$template' and rd_namespace=$namespace and page_id=rd_from;

//Select a list of pages with $title in $namespace transcluded on them
//select page_namespace,page_title from templatelinks,page where tl_title='$title' and tl_namespace=$namespace and tl_from=page_id and page_namespace=0 limit 1;

//Select a list of categories from of page
//select cl_to,cat_hidden from categorylinks,page,category where page_title='$title' and page_namespace=$namespace and cl_from=page_id and cl_to=cat_title;
	
	

?>

 