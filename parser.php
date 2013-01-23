<?php

/*
* Whole file sourced from http://pastebin.com/0bNNPy0p
* Created by User:Chris_G
* 22nd January 2013

----Usage----
$p = new parser("PageTitle","WikiText");
$p->parse();
print_r($p);

*/

class wikObject {
	public $type, $name, $attributes, $rawCode;
	
	public function __construct ($type,$name,$start,$rawCode) {
		$this->type = $type;
		$this->name = $name;
		$this->attributes['start'] = $start;
		$this->attributes['length'] = strlen($rawCode);
		$this->rawCode = $rawCode;
	}
	
	public function inObject ($pos) {
		$end = $this->attributes['start'] + $this->attributes['length'];
		if ($pos >= $this->attributes['start'] && $pos <= $end) {
			return true;
		}
		return false;
	}
}

class htmlComment extends wikObject {
	public function __construct ($start,$rawCode) {
		parent::__construct('htmlComment',null,$start,$rawCode);
	}
}

class section extends wikObject {
	public $level, $line;
	
	public function __construct ($title,$level,$line,$start,$rawCode) {
		$this->level = $level;
		$this->line = $line;
		parent::__construct('section',$title,$start,$rawCode);
	}
}

class htmlTag extends wikObject {
	public $tag;
	
	public function __construct ($tag,$start,$rawCode) {
		$this->tag = $tag;
		parent::__construct('htmlTag',null,$start,$rawCode);
	}
}

class template extends wikObject {
	public $arguments;
	
	public function __construct ($name,$start,$rawCode,$arguments) {
		$this->arguments = $arguments;
		parent::__construct('template',$name,$start,$rawCode);
	}
}

class externalLink extends wikObject {
	public $url;
	
	public function __construct ($title,$start,$rawCode,$url) {
		$this->url = $url;
		parent::__construct('externalLink',$title,$start,$rawCode);
	}
}



class link extends wikObject {
	public $lnamespace, $title;
	
	public function __construct ($name,$start,$rawCode,$lnamespace,$title) {
		$this->lnamespace = $lnamespace;
		$this->title = $title;
		parent::__construct('link',$name,$start,$rawCode);
	}
}

class interwiki extends wikObject {
	public $iwprefix, $title;
	
	public function __construct ($name,$start,$rawCode,$iwprefix,$title) {
		$this->iwprefix = $iwprefix;
		$this->title = $title;
		parent::__construct('interwiki',$name,$start,$rawCode);
	}
}

class category extends wikObject {
	public $title;
	
	public function __construct ($name,$start,$rawCode,$title) {
		$this->title = $title;
		parent::__construct('category',$name,$start,$rawCode);
	}
}

class file extends wikObject {
	public $args;
	
	public function __construct ($name,$start,$rawCode,$args) {
		$this->args = $args;
		parent::__construct('file',$name,$start,$rawCode);
	}
}

class formattedlist extends wikObject {
	public $type, $text, $level;
	
	public function __construct ($text,$start,$rawCode,$type,$level) {
		$this->type = $type;
		$this->text = $text;
		$this->level = $level;
		parent::__construct('formattedlist',null,$start,$rawCode);
	}
}

class parser {
	private $attributes, $objects, $dom, $interwiki;

	private $namespaces = array('Talk','User','User talk','Project','Wikipedia','Project talk','Wikipedia talk','File','Image','File talk','Image talk','Mediawiki','Mediawiki talk','Template','Template talk','Help','Help talk','Category','Category talk');

	private $externalLinkProtocols = array('http://','https://','gopher://','mailto:','news://','ftp://','irc://');

	public function __construct ($title,$text) {
		$this->attributes['title'] = $title;
		$this->attributes['text'] = $text;
		$this->dom = array();
		
		// TODO: this should be updated regularly (is there an API query for this stuff?)
		// Answer: Yes -> https://en.wikipedia.org/w/api.php?action=query&meta=siteinfo&siprop=interwikimap
		$this->interwiki = explode('|','aa|ab|abbenormal|ace|acronym|advogato|aew|af|airwarfare|aiwiki|ak|allwiki|als|am|an|ang|appropedia|aquariumwiki|ar|arborwiki|arc|arxiv|arz|as|ast|atmwiki|av|ay|az|b|ba|bar|bat-smg|battlestarwiki|bcl|bcnbio|be|be-x-old|bemi|benefitswiki|bg|bh|bi|biblewiki|bjn|bluwiki|blw|bm|bn|bo|botwiki|boxrec|bpy|br|brickwiki|bs|bug|bugzilla|bulba|buzztard|bxr|bytesmiths|c2|c2find|ca|canwiki|canyonwiki|cbk-zam|cdo|ce|ceb|cellwiki|centralwikia|ch|chapter|chej|cho|choralwiki|chr|chy|citizendium|ckb|ckwiss|cndbname|cndbtitle|co|comixpedia|communityscheme|communitywiki|comune|corpknowpedia|cr|crazyhacks|creativecommonswiki|creatureswiki|crh|cs|csb|cu|cv|cxej|cy|cz|da|dbdump|dcc|dcdatabase|dcma|de|dejanews|demokraatia|devmo|dict|dictionary|diq|disinfopedia|distributedproofreaders|distributedproofreadersca|dk|dmoz|dmozs|docbook|doi|doom_wiki|download|drae|dreamhost|drumcorpswiki|dsb|dv|dwjwiki|dz|echei|ecoreality|ecxei|ee|el|elibre|emacswiki|eml|en|encyc|energiewiki|eo|eokulturcentro|epo|es|et|etherpad|eu|evowiki|exotica|ext|ecei|fa|fanimutationwiki|ff|fi|finalempire|finalfantasy|finnix|fiu-vro|fj|flickrphoto|flickruser|floralwiki|flyerwiki-de|fo|foldoc|forthfreak|foxwiki|fr|freebio|freebsdman|freeculturewiki|freedomdefined|freefeel|freekiwiki|frp|frr|fur|fy|ga|gag|gan|ganfyd|gardenology|gausswiki|gd|gentoo-wiki|genwiki|gl|glk|globalvoices|glossarwiki|glossarywiki|gn|got|gotamac|greatlakeswiki|gu|guildwarswiki|guildwiki|gutenberg|gutenbergwiki|gv|h2wiki|ha|hak|hammondwiki|haw|he|heroeswiki|herzkinderwiki|hi|hif|ho|hr|hrfwiki|hrwiki|hsb|ht|hu|hupwiki|hy|hz|ia|id|ie|ig|ii|ik|ilo|imdbcharacter|imdbcompany|imdbname|imdbtitle|infoanarchy|infosecpedia|infosphere|io|is|iso639-3|it|iu|iuridictum|ja|jameshoward|javanet|javapedia|jbo|jefo|jiniwiki|jira|jp|jspwiki|jstor|jv|ka|kaa|kab|kamelo|karlsruhe|kbd|kerimwiki|kg|ki|kinowiki|kj|kk|kl|km|kn|ko|koi|kontuwiki|koslarwiki|kpopwiki|kr|krc|ks|ksh|ku|kv|kw|ky|la|lad|lb|lbe|lg|li|lij|linuxwiki|linuxwikide|liswiki|literateprograms|livepedia|lmo|ln|lo|lojban|lostpedia|lqwiki|lt|ltg|lugkr|lv|m|mail|mailarchive|map-bms|mariowiki|marveldatabase|mdf|meatball|mediazilla|memoryalpha|meta|metawiki|mg|mh|mhr|mi|mineralienatlas|minnan|mk|ml|mn|mo|moinmoin|monstropedia|mosapedia|mozcom|mozillawiki|mozillazinekb|mr|mrj|ms|mt|mus|musicbrainz|mwl|mwod|mwot|my|myv|mzn|n|na|nah|nan|nap|nb|nds|nds-nl|ne|new|ng|nkcells|nl|nn|no|nosmoke|nov|nrm|nso|nv|ny|oc|olpc|om|onelook|openfacts|openlibrary|openwetware|openwiki|opera7wiki|or|organicdesign|orthodoxwiki|os|osi reference model|otrs|ourmedia|pa|pag|pam|panawiki|pap|patwiki|pcd|pdc|perlnet|personaltelco|pfl|phpwiki|phwiki|pi|pih|pl|planetmath|pmeg|pms|pnb|pnt|ps|psycle|pt|pythoninfo|pythonwiki|pywiki|q|qu|reuterswiki|revo|rheinneckar|rm|rmy|rn|ro|roa-rup|roa-tara|robowiki|rowiki|ru|rue|rw|s|s23wiki|sa|sah|sc|scholar|schoolswp|scn|sco|scores|scoutwiki|scramble|sd|se|seapig|seattlewiki|seattlewireless|semantic-mw|senseislibrary|sep11|sg|sh|si|silcode|simple|sk|sl|slashdot|slwiki|sm|smikipedia|sn|so|sourceforge|sq|squeak|sr|srn|ss|st|stable|stats|stq|strategywiki|su|sv|svgwiki|sw|swinbrain|swingwiki|swtrain|szl|ta|tabwiki|tavi|tclerswiki|te|technorati|tesoltaiwan|tet|tg|th|thelemapedia|theopedia|thinkwiki|ti|tibiawiki|ticket|tk|tl|tmbw|tmnet|tmwiki|tn|to|tpi|tr|ts|tswiki|tt|tum|turismo|tviv|tvtropes|tw|twiki|ty|tyvawiki|udm|ug|uk|unreal|ur|urbandict|usej|usemod|uz|v|valuewiki|ve|vec|vi|vinismo|vkol|vlos|vls|vo|voipinfo|w|wa|war|webisodes|wiki|wikia|wikiasite|wikichat|wikichristian|wikicities|wikicity|wikif1|wikifur|wikihow|wikiindex|wikilemon|wikilivres|wikimac-de|wikinfo|wikinvest|wikipaltz|wikischool|wikiskripta|wikisophia|wikispot|wikitech|wikiti|wikitravel|wikitree|wikiweet|wikiwikiweb|wikt|wipipedia|wlug|wmar|wmau|wmca|wmch|wmdc|wmfr|wmhk|wmhu|wmid|wmil|wmin|wmit|wmtw|wo|wookieepedia|world66|wowwiki|wqy|wurmpedia|wuu|xal|xh|xmf|yi|yo|za|zea|zh|zh-cfr|zh-classical|zh-cn|zh-min-nan|zh-tw|zh-yue|zrhwiki|zu|zum|zwiki|zzz wiki|Cej');
	}

	public function parse () {
		// Lets parse the page
		
		$this->sections();
		$this->doNoWikiAndComments();
		$this->wikiLinks();
		$this->externalLinks();
		$this->expandTemplates();
		
		return $this->objects;
	}

	public function lists () {
		$text = $this->attributes['text'];
		$lines = explode("\n",$text);
		$pos = $line_number = 0;
		$list_number = 0;
		foreach ($lines as $line) {
			$pos = $pos + strlen($line) + 1;
			$line_number++;
		}
		
	}

	public function sections () {
		$text = $this->attributes['text'];
		$lines = explode("\n",$text);
		$pos = $line_number = 0;
		foreach ($lines as $line) {
			$left = $right = $starpos = $endpos = 0;
			
			for ($i=0;$i<strlen($line);$i++) {
				$char = $line{$i};
				$starpos = $i;
				if ($char=='=') {
					$left++;
				} else {
					break;
				}
			}
			
			if ($left > 0) {
				$x = strlen($line);
				$x--;
				for ($i=$x;$i>=0;$i--) {
					$char = $line{$i};
					if ($char=='=') {
						$right++;
					} elseif ($char != ' ') {
						break;
					}
					$endpos++;
				}
				if ($right > 0) {
					if ($left==$right || $left < $right) {
						$level = $left;
					} else {
						$level = $right;
					}
					$len = strlen($line) - $starpos - $endpos;
					$heading = trim(substr($line,$starpos,$len));
					$this->objects['section'][] = new section($heading,$level,$line_number,$pos,$line);
				}
			}
			
			$pos = $pos + strlen($line) + 1;
			$line_number++;
		}
	}

	public function doNoWikiAndComments () {
		$nowiki = $this->nowiki();
		$comments = $this->htmlComments();
		$merged = array();
		foreach ($nowiki as $element) {
			$merged[$element['start']] = array(
					'type' => 'nowiki',
					'code' => $element['code'],
					'end'  => $element['end']
					);
		}
		foreach ($comments as $element) {
			$merged[$element['start']] = array(
					'type' => 'comment',
					'code' => $element['code'],
					'end'  => $element['end']
					);
		}
		ksort($merged);
		$approved = array();
		foreach ($merged as $start => $array) {
			$ignore = false;
			foreach ($approved as $astart => $aend) {
				if ($astart > $start && $aend < $start) {
					$ignore = true;
					break;
				}
			}
			if (!$ignore) {
				$approved[$start] = $array['end'];
				if ($array['type']=='comment') {
					$this->objects['htmlComment'][] = new htmlComment($start,$array['code']);
				} else {
					$this->objects['htmlTag'][] = new htmlTag('nowiki',$start,$array['code']);
				}
			}
		}
	}

	public function findText ($text,$start,$end) {
		$return = array();
		$i = 0;
		$offset = 0;
		while (true)  {
			$posStart = stripos($text,$start,$offset);
			if ($posStart===false) {
				break;
			}
			$posEnd = stripos($text,$end,$posStart);
			if ($posEnd===false) {
				break;
			}
			$return[$i]['code'] = substr($text,$posStart,($posEnd-$posStart+strlen($end)));
			$return[$i]['start'] = $posStart;
			$return[$i]['end'] = $posEnd;
			$offset = $posEnd;
			$i++;
		}
		return $return;
	}

	public function nowiki () {
		$text = $this->attributes['text'];
		$offset = 0;
		$tags = array();
		$search = $this->findText($text,'<nowiki>','</nowiki>');
		foreach ($search as $found) {
			$tags[] = array(
					'start' => $found['start'],
					'code' => $found['code'],
					'length' => strlen($found['code']),
					'end' => $found['end']
				);
		}
		return $tags;
	}

	public function htmlComments () {
		$text = $this->attributes['text'];
		$offset = 0;
		$comments = array();
		$search = $this->findText($text,'<!--','-->');
		foreach ($search as $found) {
			$comments[] = array(
				'start' => $found['start'],
				'code' => $found['code'],
				'length' => strlen($found['code']),
				'end' => $found['end']
			);
		}
		return $comments;
	}

	public function externalLinks () {
		// Note: this->wikiLinks(); must run before this function
		$text = $this->attributes['text'];
		$offset = 0;
		
		$links = array();
		$ignore = array();
		while (true)  {
			$link = array();
			$pos = strpos($text,'[',$offset);
			if ($pos===false) {
				break;
			}
			
			if ($text{$pos}     == '[' && 
			    $text{($pos+1)} == '[') {
			    	$offset = $pos+2;
			    	continue;
			}
			
			$link['start'] = $pos;
			$link['url'] = '';
			$link['code'] = '';
			for ($i=$pos;$i<strlen($text);$i++) {
				$char = $text{$i};
				
				// Linebreaks kill a link
				if ($char=="\n") {
					$offset = $i;
					continue 2;
				}
				
				$link['code'] .= $char;
				
				if ($char==']') {
					$link['length'] = strlen($link['code']);
					break;
				} elseif (!isset($link['title'])) {
					if ($char==' ') {
						$link['title'] = '';
					} elseif ($char!='[') {
						$link['url'] .= $char;
					}
				} else {
					$link['title'] .= $char;
				}
			}
			$offset = $link['start']+$link['length'];
			$ignore[$link['start']] = $offset;
			$this->objects['externalLinks'][] = new externalLink(@$link['title'],$link['start'],$link['code'],$link['url']);
		}
		foreach ($this->externalLinkProtocols as $scheme) {
			$offset = 0;
			while (true)  {
				$link = array();
				$pos = strpos($text,$scheme,$offset);
				if ($pos===false) {
					break;
				}
				foreach ($ignore as $start => $end) {
					if ($pos >= $start &&
					    $pos <= $end) {
						$offset = $pos+1;
					    	continue 2;
					}
				}
				$link['start'] = $pos;
				$link['code']  = '';
				for ($i=$pos;$i<strlen($text);$i++) {
					$char = $text{$i};
					if ($char==' ' || $char == "\n" || $char == '<') {
						break;
					}
					$link['code'] .= $char;
					$offset = $i;
				}
				$this->objects['externalLinks'][] = new externalLink(null,$link['start'],$link['code'],$link['code']);
			}
		}
	}

	public function wikiLinks () {
		/*
		Things to keep in mind:
			* Interwiki Links
			* Categories
			* Images
			* [[Foo]]bar
			* [[User:Foo|]]
			* [[:Category:Test]]
			* [[/Subpage|hi]]
		 */
		 
		$text = $this->attributes['text'];
		$offset = 0;
		
		$links = array();
		$alpha = str_split('abcdefghijklmnopqrstuvwxyz');
		while (true)  {
			$link = array();
			$pos = strpos($text,'[[',$offset);
			if ($pos===false) {
				break;
			}
			$link['start'] = $pos;
			$link['argCount'] = 0;
			$link['args'] = array();
			$link['level'] = 0;
			$link['code'] = '';
			$link['extra'] = '';
			for ($i=$pos;$i<strlen($text);$i++) {
				$break = false;
				$char = $text{$i};
				$next = $text{($i+1)};
				
				// Linebreaks kill a link
				if ($char=="\n") {
					$offset = $i;
					$link = array();
					break;
				}
				
				$link['code'] .= $char;
				
				if ($char=='[' && $next == '[') {
					$link['level']++;
				} elseif ($char==']' && $next == ']') {
					$link['level']--;
					if ($link['level']==0) {
						$link['code'] .= ']';
						$break = true;
						for ($a=($i+2);$a<strlen($text);$a++) {
							$x = $text{$a};
							if (in_array(strtolower($x),$alpha)) {
								$link['code'] .= $x;
								$link['extra'] .= $x;
							} else {
								break;
							}
						}
						$link['length'] = strlen($link['code']);
					}
				}
				if ($link['level']>0) {
					if ($link['level']==1 && $char=='|') {
						$link['argCount']++;
					} elseif ($link['argCount']==0) {
						if ($char==':') {
							if (!isset($link['page'])) {
								$link['page'] = '';
								$link['escapeChar'] = true;
							} elseif (!isset($link['namespace']) 
							&& in_array(ucfirst(strtolower($link['page'])),$this->namespaces)) {
								$link['namespace'] = ucfirst(strtolower($link['page']));
								$link['page'] = '';
							} elseif (!isset($link['interwiki']) 
							&& in_array(strtolower($link['page']),$this->interwiki)) {
								$link['interwiki'] = strtolower($link['page']);
								$link['page'] = '';
							} else {
								$link['page'] .= $char;
							}
						} elseif ($char != '[' && $char != ']') {
							@$link['page'] .= $char;
						}
					} else {
						@$link['arg'][$link['argCount']] .= $char;
					}
				}
				if ($break) {
					break;
				}
			}
			
			if (empty($link)) {
			} elseif (!isset($link['escapeChar']) && isset($link['namespace'])) {
				if ($link['namespace'] == 'Image' || $link['namespace'] == 'File') {
					$this->objects['files'][] = new file($link['page'],$link['start'],$link['code'],$link['arg']);
				} elseif ($link['namespace'] == 'Category') {
					if (!empty($link['arg'][0])) {
						$link['title'] = $link['arg'][0];
					} else {
						$link['title'] = $link['page'];
					}
					$this->objects['categories'][] = new category($link['page'],$link['start'],$link['code'],$link['title']);
				}
				$offset = $link['start']+$link['length'];
			} else {
				if (!empty($link['arg'][0])) {
					$link['title'] = $link['arg'][0];
				} else {
					if (isset($link['arg'][0]) || empty($link['namespace'])) {
						$link['title'] = $link['page'];
					} else {
						$link['title'] = $link['namespace'].':'.$link['page'];
					}
				}
				if (!empty($link['extra'])) {
					$link['title'] .= $link['extra'];
				}
				if (substr($link['page'],0,1) == '/') {
					$link['page'] = $this->attributes['title'] . $link['page'];
				}
				if (isset($link['interwiki']) && !isset($link['escapeChar'])) {
					$this->objects['interwiki'][] = new interwiki($link['page'],$link['start'],$link['code'],$link['interwiki'],$link['title']);
				} else {
					$this->objects['links'][] = new link($link['page'],$link['start'],$link['code'],@$link['namespace'],$link['title']);
				}
				$offset = $link['start']+$link['length'];
			}
			
		}
	}
	
	public function expandTemplates () {
		$text = str_split($this->attributes['text']);
		$template_level = 0;
		$args = 0;
		$template = array();
		$ignore_next_char = false;
		$in_link = false;
		for ($i=0;$i<count($text);$i++) {
			$prev = $text[($i - 1)];
			$next = $text[($i + 1)];
			$char = $text[$i];
			if ($char=='[' && $prev == '[') {
				$in_link = true;
			} elseif ($char==']' && $next == ']') {
				$in_link = false;
			}
			if ($char=='{' && $prev == '{') {
				$template_level++;
				if ($template_level==1) {
					$start = $i;
					$code = '{{';
					continue;
				}
			} elseif ($char=='}' && $next == '}' && !$ignore_next_char) {
				$template_level--;
				$ignore_next_char = true;
				if ($template_level==0) {
					$args = 0;
					$code .= '}}';
					$template['name'] = trim($template['name']);
					$this->objects['templates'][] = new template($template['name'],$start,$code,$template['args']);
					$template = array();
					continue;
				}
			} elseif ($ignore_next_char) {
				$ignore_next_char = false;
			}
			if ($template_level==1) {
				$code .= $char;
				if ($char=='|' && !$in_link) {
					$args++;
					continue;
				}
				if ($args==0) {
					$template['name'] .= $cont.$char;
				} else {
					$template['args'][$args] .= $cont.$char;
				}
				$cont = '';
			} elseif ($template_level > 1) {
				$cont .= $char;
				$code .= $char;
			}
		}
	}
	
	public function length () {
		return strlen($this->attributes['text']);
	}
}

?>