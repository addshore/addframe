<?php

/*
* Whole wikObject_file sourced from http://pastebin.com/0bNNPy0p
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

class wikObject_htmlComment extends wikObject {
	public function __construct ($start,$rawCode) {
		parent::__construct('wikObject_htmlComment',null,$start,$rawCode);
	}
}

class wikObject_section extends wikObject {
	public $level, $line;
	
	public function __construct ($title,$level,$line,$start,$rawCode) {
		$this->level = $level;
		$this->line = $line;
		parent::__construct('wikObject_section',$title,$start,$rawCode);
	}
}

class wikObject_htmlTag extends wikObject {
	public $tag;
	
	public function __construct ($tag,$start,$rawCode) {
		$this->tag = $tag;
		parent::__construct('wikObject_htmlTag',null,$start,$rawCode);
	}
}

class wikObject_template extends wikObject {
	public $arguments;
	
	public function __construct ($name,$start,$rawCode,$arguments) {
		$this->arguments = $arguments;
		parent::__construct('wikObject_template',$name,$start,$rawCode);
	}
}

class wikObject_externalwikObject_link extends wikObject {
	public $url;
	
	public function __construct ($title,$start,$rawCode,$url) {
		$this->url = $url;
		parent::__construct('wikObject_externalwikObject_link',$title,$start,$rawCode);
	}
}



class wikObject_link extends wikObject {
	public $lnamespace, $title;
	
	public function __construct ($name,$start,$rawCode,$lnamespace,$title) {
		$this->lnamespace = $lnamespace;
		$this->title = $title;
		parent::__construct('wikObject_link',$name,$start,$rawCode);
	}
}

class wikObject_interwiki extends wikObject {
	public $iwprefix, $title;
	
	public function __construct ($name,$start,$rawCode,$iwprefix,$title) {
		$this->iwprefix = $iwprefix;
		$this->title = $title;
		parent::__construct('wikObject_interwiki',$name,$start,$rawCode);
	}
}

class wikObject_category extends wikObject {
	public $title;
	
	public function __construct ($name,$start,$rawCode,$title) {
		$this->title = $title;
		parent::__construct('wikObject_category',$name,$start,$rawCode);
	}
}

class wikObject_file extends wikObject {
	public $args;
	
	public function __construct ($name,$start,$rawCode,$args) {
		$this->args = $args;
		parent::__construct('wikObject_file',$name,$start,$rawCode);
	}
}

class wikObject_formattedlist extends wikObject {
	public $type, $text, $level;
	
	public function __construct ($text,$start,$rawCode,$type,$level) {
		$this->type = $type;
		$this->text = $text;
		$this->level = $level;
		parent::__construct('wikObject_formattedlist',null,$start,$rawCode);
	}
}

class parser {
	private $attributes, $objects, $dom, $wikObject_interwiki;

	private $namespaces = array('Talk','User','User talk','Project','Wikipedia','Project talk','Wikipedia talk','wikObject_file','Image','wikObject_file talk','Image talk','Mediawiki','Mediawiki talk','wikObject_template','wikObject_template talk','Help','Help talk','wikObject_category','wikObject_category talk');

	private $wikObject_externalwikObject_linkProtocols = array('http://','https://','gopher://','mailto:','news://','ftp://','irc://');

	public function __construct ($title,$text) {
		$this->attributes['title'] = $title;
		$this->attributes['text'] = $text;
		$this->dom = array();
		
		// TODO: this should be updated regularly (is there an API query for this stuff?)
		// Answer: Yes -> https://en.wikipedia.org/w/api.php?action=query&meta=siteinfo&siprop=wikObject_interwikimap
		$this->wikObject_interwiki = explode('|','aa|ab|abbenormal|ace|acronym|advogato|aew|af|airwarfare|aiwiki|ak|allwiki|als|am|an|ang|appropedia|aquariumwiki|ar|arborwiki|arc|arxiv|arz|as|ast|atmwiki|av|ay|az|b|ba|bar|bat-smg|battlestarwiki|bcl|bcnbio|be|be-x-old|bemi|benefitswiki|bg|bh|bi|biblewiki|bjn|bluwiki|blw|bm|bn|bo|botwiki|boxrec|bpy|br|brickwiki|bs|bug|bugzilla|bulba|buzztard|bxr|bytesmiths|c2|c2find|ca|canwiki|canyonwiki|cbk-zam|cdo|ce|ceb|cellwiki|centralwikia|ch|chapter|chej|cho|choralwiki|chr|chy|citizendium|ckb|ckwiss|cndbname|cndbtitle|co|comixpedia|communityscheme|communitywiki|comune|corpknowpedia|cr|crazyhacks|creativecommonswiki|creatureswiki|crh|cs|csb|cu|cv|cxej|cy|cz|da|dbdump|dcc|dcdatabase|dcma|de|dejanews|demokraatia|devmo|dict|dictionary|diq|disinfopedia|distributedproofreaders|distributedproofreadersca|dk|dmoz|dmozs|docbook|doi|doom_wiki|download|drae|dreamhost|drumcorpswiki|dsb|dv|dwjwiki|dz|echei|ecoreality|ecxei|ee|el|elibre|emacswiki|eml|en|encyc|energiewiki|eo|eokulturcentro|epo|es|et|etherpad|eu|evowiki|exotica|ext|ecei|fa|fanimutationwiki|ff|fi|finalempire|finalfantasy|finnix|fiu-vro|fj|flickrphoto|flickruser|floralwiki|flyerwiki-de|fo|foldoc|forthfreak|foxwiki|fr|freebio|freebsdman|freeculturewiki|freedomdefined|freefeel|freekiwiki|frp|frr|fur|fy|ga|gag|gan|ganfyd|gardenology|gausswiki|gd|gentoo-wiki|genwiki|gl|glk|globalvoices|glossarwiki|glossarywiki|gn|got|gotamac|greatlakeswiki|gu|guildwarswiki|guildwiki|gutenberg|gutenbergwiki|gv|h2wiki|ha|hak|hammondwiki|haw|he|heroeswiki|herzkinderwiki|hi|hif|ho|hr|hrfwiki|hrwiki|hsb|ht|hu|hupwiki|hy|hz|ia|id|ie|ig|ii|ik|ilo|imdbcharacter|imdbcompany|imdbname|imdbtitle|infoanarchy|infosecpedia|infosphere|io|is|iso639-3|it|iu|iuridictum|ja|jameshoward|javanet|javapedia|jbo|jefo|jiniwiki|jira|jp|jspwiki|jstor|jv|ka|kaa|kab|kamelo|karlsruhe|kbd|kerimwiki|kg|ki|kinowiki|kj|kk|kl|km|kn|ko|koi|kontuwiki|koslarwiki|kpopwiki|kr|krc|ks|ksh|ku|kv|kw|ky|la|lad|lb|lbe|lg|li|lij|linuxwiki|linuxwikide|liswiki|literateprograms|livepedia|lmo|ln|lo|lojban|lostpedia|lqwiki|lt|ltg|lugkr|lv|m|mail|mailarchive|map-bms|mariowiki|marveldatabase|mdf|meatball|mediazilla|memoryalpha|meta|metawiki|mg|mh|mhr|mi|mineralienatlas|minnan|mk|ml|mn|mo|moinmoin|monstropedia|mosapedia|mozcom|mozillawiki|mozillazinekb|mr|mrj|ms|mt|mus|musicbrainz|mwl|mwod|mwot|my|myv|mzn|n|na|nah|nan|nap|nb|nds|nds-nl|ne|new|ng|nkcells|nl|nn|no|nosmoke|nov|nrm|nso|nv|ny|oc|olpc|om|onelook|openfacts|openlibrary|openwetware|openwiki|opera7wiki|or|organicdesign|orthodoxwiki|os|osi reference model|otrs|ourmedia|pa|pag|pam|panawiki|pap|patwiki|pcd|pdc|perlnet|personaltelco|pfl|phpwiki|phwiki|pi|pih|pl|planetmath|pmeg|pms|pnb|pnt|ps|psycle|pt|pythoninfo|pythonwiki|pywiki|q|qu|reuterswiki|revo|rheinneckar|rm|rmy|rn|ro|roa-rup|roa-tara|robowiki|rowiki|ru|rue|rw|s|s23wiki|sa|sah|sc|scholar|schoolswp|scn|sco|scores|scoutwiki|scramble|sd|se|seapig|seattlewiki|seattlewireless|semantic-mw|senseislibrary|sep11|sg|sh|si|silcode|simple|sk|sl|slashdot|slwiki|sm|smikipedia|sn|so|sourceforge|sq|squeak|sr|srn|ss|st|stable|stats|stq|strategywiki|su|sv|svgwiki|sw|swinbrain|swingwiki|swtrain|szl|ta|tabwiki|tavi|tclerswiki|te|technorati|tesoltaiwan|tet|tg|th|thelemapedia|theopedia|thinkwiki|ti|tibiawiki|ticket|tk|tl|tmbw|tmnet|tmwiki|tn|to|tpi|tr|ts|tswiki|tt|tum|turismo|tviv|tvtropes|tw|twiki|ty|tyvawiki|udm|ug|uk|unreal|ur|urbandict|usej|usemod|uz|v|valuewiki|ve|vec|vi|vinismo|vkol|vlos|vls|vo|voipinfo|w|wa|war|webisodes|wiki|wikia|wikiasite|wikichat|wikichristian|wikicities|wikicity|wikif1|wikifur|wikihow|wikiindex|wikilemon|wikilivres|wikimac-de|wikinfo|wikinvest|wikipaltz|wikischool|wikiskripta|wikisophia|wikispot|wikitech|wikiti|wikitravel|wikitree|wikiweet|wikiwikiweb|wikt|wipipedia|wlug|wmar|wmau|wmca|wmch|wmdc|wmfr|wmhk|wmhu|wmid|wmil|wmin|wmit|wmtw|wo|wookieepedia|world66|wowwiki|wqy|wurmpedia|wuu|xal|xh|xmf|yi|yo|za|zea|zh|zh-cfr|zh-classical|zh-cn|zh-min-nan|zh-tw|zh-yue|zrhwiki|zu|zum|zwiki|zzz wiki|Cej');
	}

	public function parse () {
		// Lets parse the page
		
		$this->wikObject_sections();
		$this->doNoWikiAndComments();
		$this->wikiwikObject_links();
		$this->wikObject_externalwikObject_links();
		$this->expandwikObject_templates();
		
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

	public function wikObject_sections () {
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
					$this->objects['wikObject_section'][] = new wikObject_section($heading,$level,$line_number,$pos,$line);
				}
			}
			
			$pos = $pos + strlen($line) + 1;
			$line_number++;
		}
	}

	public function doNoWikiAndComments () {
		$nowiki = $this->nowiki();
		$comments = $this->wikObject_htmlComments();
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
					$this->objects['wikObject_htmlComment'][] = new wikObject_htmlComment($start,$array['code']);
				} else {
					$this->objects['wikObject_htmlTag'][] = new wikObject_htmlTag('nowiki',$start,$array['code']);
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

	public function wikObject_htmlComments () {
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

	public function wikObject_externalwikObject_links () {
		// Note: this->wikiwikObject_links(); must run before this function
		$text = $this->attributes['text'];
		$offset = 0;
		
		$wikObject_links = array();
		$ignore = array();
		while (true)  {
			$wikObject_link = array();
			$pos = strpos($text,'[',$offset);
			if ($pos===false) {
				break;
			}
			
			if ($text{$pos}     == '[' && 
			    $text{($pos+1)} == '[') {
			    	$offset = $pos+2;
			    	continue;
			}
			
			$wikObject_link['start'] = $pos;
			$wikObject_link['url'] = '';
			$wikObject_link['code'] = '';
			for ($i=$pos;$i<strlen($text);$i++) {
				$char = $text{$i};
				
				// Linebreaks kill a wikObject_link
				if ($char=="\n") {
					$offset = $i;
					continue 2;
				}
				
				$wikObject_link['code'] .= $char;
				
				if ($char==']') {
					$wikObject_link['length'] = strlen($wikObject_link['code']);
					break;
				} elseif (!isset($wikObject_link['title'])) {
					if ($char==' ') {
						$wikObject_link['title'] = '';
					} elseif ($char!='[') {
						$wikObject_link['url'] .= $char;
					}
				} else {
					$wikObject_link['title'] .= $char;
				}
			}
			$offset = $wikObject_link['start']+$wikObject_link['length'];
			$ignore[$wikObject_link['start']] = $offset;
			$this->objects['wikObject_externalwikObject_links'][] = new wikObject_externalwikObject_link(@$wikObject_link['title'],$wikObject_link['start'],$wikObject_link['code'],$wikObject_link['url']);
		}
		foreach ($this->wikObject_externalwikObject_linkProtocols as $scheme) {
			$offset = 0;
			while (true)  {
				$wikObject_link = array();
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
				$wikObject_link['start'] = $pos;
				$wikObject_link['code']  = '';
				for ($i=$pos;$i<strlen($text);$i++) {
					$char = $text{$i};
					if ($char==' ' || $char == "\n" || $char == '<') {
						break;
					}
					$wikObject_link['code'] .= $char;
					$offset = $i;
				}
				$this->objects['wikObject_externalwikObject_links'][] = new wikObject_externalwikObject_link(null,$wikObject_link['start'],$wikObject_link['code'],$wikObject_link['code']);
			}
		}
	}

	public function wikiwikObject_links () {
		/*
		Things to keep in mind:
			* wikObject_interwiki wikObject_links
			* Categories
			* Images
			* [[Foo]]bar
			* [[User:Foo|]]
			* [[:wikObject_category:Test]]
			* [[/Subpage|hi]]
		 */
		 
		$text = $this->attributes['text'];
		$offset = 0;
		
		$wikObject_links = array();
		$alpha = str_split('abcdefghijklmnopqrstuvwxyz');
		while (true)  {
			$wikObject_link = array();
			$pos = strpos($text,'[[',$offset);
			if ($pos===false) {
				break;
			}
			$wikObject_link['start'] = $pos;
			$wikObject_link['argCount'] = 0;
			$wikObject_link['args'] = array();
			$wikObject_link['level'] = 0;
			$wikObject_link['code'] = '';
			$wikObject_link['extra'] = '';
			for ($i=$pos;$i<strlen($text);$i++) {
				$break = false;
				$char = $text{$i};
				$next = $text{($i+1)};
				
				// Linebreaks kill a wikObject_link
				if ($char=="\n") {
					$offset = $i;
					$wikObject_link = array();
					break;
				}
				
				$wikObject_link['code'] .= $char;
				
				if ($char=='[' && $next == '[') {
					$wikObject_link['level']++;
				} elseif ($char==']' && $next == ']') {
					$wikObject_link['level']--;
					if ($wikObject_link['level']==0) {
						$wikObject_link['code'] .= ']';
						$break = true;
						for ($a=($i+2);$a<strlen($text);$a++) {
							$x = $text{$a};
							if (in_array(strtolower($x),$alpha)) {
								$wikObject_link['code'] .= $x;
								$wikObject_link['extra'] .= $x;
							} else {
								break;
							}
						}
						$wikObject_link['length'] = strlen($wikObject_link['code']);
					}
				}
				if ($wikObject_link['level']>0) {
					if ($wikObject_link['level']==1 && $char=='|') {
						$wikObject_link['argCount']++;
					} elseif ($wikObject_link['argCount']==0) {
						if ($char==':') {
							if (!isset($wikObject_link['page'])) {
								$wikObject_link['page'] = '';
								$wikObject_link['escapeChar'] = true;
							} elseif (!isset($wikObject_link['namespace']) 
							&& in_array(ucfirst(strtolower($wikObject_link['page'])),$this->namespaces)) {
								$wikObject_link['namespace'] = ucfirst(strtolower($wikObject_link['page']));
								$wikObject_link['page'] = '';
							} elseif (!isset($wikObject_link['wikObject_interwiki']) 
							&& in_array(strtolower($wikObject_link['page']),$this->wikObject_interwiki)) {
								$wikObject_link['wikObject_interwiki'] = strtolower($wikObject_link['page']);
								$wikObject_link['page'] = '';
							} else {
								$wikObject_link['page'] .= $char;
							}
						} elseif ($char != '[' && $char != ']') {
							@$wikObject_link['page'] .= $char;
						}
					} else {
						@$wikObject_link['arg'][$wikObject_link['argCount']] .= $char;
					}
				}
				if ($break) {
					break;
				}
			}
			
			if (empty($wikObject_link)) {
			} elseif (!isset($wikObject_link['escapeChar']) && isset($wikObject_link['namespace'])) {
				if ($wikObject_link['namespace'] == 'Image' || $wikObject_link['namespace'] == 'wikObject_file') {
					$this->objects['wikObject_files'][] = new wikObject_file($wikObject_link['page'],$wikObject_link['start'],$wikObject_link['code'],$wikObject_link['arg']);
				} elseif ($wikObject_link['namespace'] == 'wikObject_category') {
					if (!empty($wikObject_link['arg'][0])) {
						$wikObject_link['title'] = $wikObject_link['arg'][0];
					} else {
						$wikObject_link['title'] = $wikObject_link['page'];
					}
					$this->objects['categories'][] = new wikObject_category($wikObject_link['page'],$wikObject_link['start'],$wikObject_link['code'],$wikObject_link['title']);
				}
				$offset = $wikObject_link['start']+$wikObject_link['length'];
			} else {
				if (!empty($wikObject_link['arg'][0])) {
					$wikObject_link['title'] = $wikObject_link['arg'][0];
				} else {
					if (isset($wikObject_link['arg'][0]) || empty($wikObject_link['namespace'])) {
						$wikObject_link['title'] = $wikObject_link['page'];
					} else {
						$wikObject_link['title'] = $wikObject_link['namespace'].':'.$wikObject_link['page'];
					}
				}
				if (!empty($wikObject_link['extra'])) {
					$wikObject_link['title'] .= $wikObject_link['extra'];
				}
				if (substr($wikObject_link['page'],0,1) == '/') {
					$wikObject_link['page'] = $this->attributes['title'] . $wikObject_link['page'];
				}
				if (isset($wikObject_link['wikObject_interwiki']) && !isset($wikObject_link['escapeChar'])) {
					$this->objects['wikObject_interwiki'][] = new wikObject_interwiki($wikObject_link['page'],$wikObject_link['start'],$wikObject_link['code'],$wikObject_link['wikObject_interwiki'],$wikObject_link['title']);
				} else {
					$this->objects['wikObject_links'][] = new wikObject_link($wikObject_link['page'],$wikObject_link['start'],$wikObject_link['code'],@$wikObject_link['namespace'],$wikObject_link['title']);
				}
				$offset = $wikObject_link['start']+$wikObject_link['length'];
			}
			
		}
	}
	
	public function expandwikObject_templates () {
		$text = str_split($this->attributes['text']);
		$wikObject_template_level = 0;
		$args = 0;
		$wikObject_template = array();
		$ignore_next_char = false;
		$in_wikObject_link = false;
		for ($i=0;$i<count($text);$i++) {
			$prev = $text[($i - 1)];
			$next = $text[($i + 1)];
			$char = $text[$i];
			if ($char=='[' && $prev == '[') {
				$in_wikObject_link = true;
			} elseif ($char==']' && $next == ']') {
				$in_wikObject_link = false;
			}
			if ($char=='{' && $prev == '{') {
				$wikObject_template_level++;
				if ($wikObject_template_level==1) {
					$start = $i;
					$code = '{{';
					continue;
				}
			} elseif ($char=='}' && $next == '}' && !$ignore_next_char) {
				$wikObject_template_level--;
				$ignore_next_char = true;
				if ($wikObject_template_level==0) {
					$args = 0;
					$code .= '}}';
					$wikObject_template['name'] = trim($wikObject_template['name']);
					$this->objects['wikObject_templates'][] = new wikObject_template($wikObject_template['name'],$start,$code,$wikObject_template['args']);
					$wikObject_template = array();
					continue;
				}
			} elseif ($ignore_next_char) {
				$ignore_next_char = false;
			}
			if ($wikObject_template_level==1) {
				$code .= $char;
				if ($char=='|' && !$in_wikObject_link) {
					$args++;
					continue;
				}
				if ($args==0) {
					$wikObject_template['name'] .= $cont.$char;
				} else {
					$wikObject_template['args'][$args] .= $cont.$char;
				}
				$cont = '';
			} elseif ($wikObject_template_level > 1) {
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