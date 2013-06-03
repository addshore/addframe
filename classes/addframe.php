<?php

/* - Example Usage - 

$wiki = new wiki("http://en.wikipedia.org/w/api.php");
echo "Logging in to ".$wiki->url."\n";
$wiki->login("user","pass");
$wiki->edittitle ("User:Addshore/Sandbox","BLANK","Blanking text",true,false);

*/

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

	/*
	* Performes a request to the api given the query and post data
	* @param $query Array of query data
	* @param $post Array of post data
	* @return Array of the returning data
	**/
	function apiRequest ($query,$post=null){
		$query['format'] = 'php';
		$query = "?".http_build_query($query);
        if ($post==null)
            $ret = $this->http->get($this->url.$query);
        else
            $ret = $this->http->post($this->url.$query,$post);
        return unserialize($ret);
	}
	
	function apiAction ($type,$post=null){
		$query['action'] = $type;
		return $this->apiRequest($query,$post)
	}
	
	// Actions
	function apiQuery ($query,$post=null){return $this->apiAction('query',$post);}
	function apiList ($query,$post=null){return $this->apiAction('list',$post);}
	function apiProp ($query,$post=null){return $this->apiAction('prop',$post);}
	function apiMeta ($query,$post=null){return $this->apiAction('meta',$post);}
	function apiLogin ($query,$post=null){return $this->apiAction('login',$post);}
	function apiLogout () {return $this->apiAction ('logout',null);}
	function apiCreateaccount ($query,$post=null){return $this->apiAction('createaccount',$post);}
	function apitokens ($query,$post=null){return $this->apiAction('tokens',$post);}
	
	/**
	* Performs an API login of a username and password
	* @param String $user Username to login as.
	* @param String $pass Password that corrisponds to the username.
	* @return true OR error
	**/
	function doLogin ($username,$password) {
		$post['lgname'] = $username;
		$post['lgpassword'] = $password;
		$r = $this->apiLogin($query,$post);
        /* This is now required - see https://bugzilla.wikimedia.org/show_bug.cgi?id=23076 */
        if ($r['login']['result'] == 'NeedToken') {
            $post['lgtoken'] = $r['login']['token'];
            $r = $this->apiLogin($query,$post);
        }
		//Return the login result, error or true
        if ($r['login']['result'] != 'Success') {
			return $r['login']['result'];
        } else {
            return true;
        }
    }
	
	/*
	* Performes a request to the api for an action tokens
	* @param $type Default='edit'
	* Can be: block, delete, deleteglobalaccount, edit, email, import, move, options, patrol, protect, setglobalaccountstatus, unblock, watch
	* @return Array of the returning data
	**/
	function getToken ($type='edit') { 
		$query['type'] = $type;
		$return = apiTokens($query,"type=$type");
		return $return;
	}
	
	//PROP
	function categories (){}
	function categoryinfo ($parameters){return prop('categoryinfo',$parameters);}
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
	function expandtemplates ($parameters) {return action ('expandtemplates',$parameters);}
	function compare () {}	
	function purge ($parameters) {return action ( 'purge', $parameters);}
	function rollback () {}
	function delete () {}
	function undelete () {}
	function protect () {}
	function block () {}
	function unblock () {}
	function move () {}
	function edit ($parameters){return  action ( 'edit', $parameters);}
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

 