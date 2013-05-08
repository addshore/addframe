<?PHP

require '../classes/botclasses.php';
require '../config/wiki.php';

$wiki = new wikipedia;
$wiki->url = 'http://en.wikipedia.org/w/api.php';
global $wiki;

$parentpid = posix_getpid();

$nickname = "Addbot-wdr";

set_time_limit(0); 
require '/home/addshore/.password.addbot';
$wiki->login($config['user'],$config['password']);
echo "USER: Logged In!\n";
unset($config['password']);

$rc_host = "irc.wikimedia.org";
$rc_port = 6667;
$rc_channel = "#en.wikipedia";

							$regex = "\n\[\[(nostalgia|ten|test|aa|ab|ace|af|ak|als|am|an|ang|ar|arc|arz|as|ast|av|ay|az|ba|bar|bat-smg|bcl|be|be-x-old|bg|bh|bi|bjn|bm|bn|bo|bpy|br|bs|bug|bxr|ca|cbk-zam|cdo|ce|ceb|ch|cho|chr|chy|ckb|co|cr|crh|cs|csb|cu|cv|cy|da|de|diq|dsb|dv|dz|ee|el|eml|eo|es|et|eu|ext|fa|ff|fi|fiu-vro|fj|fo|fr|frp|frr|fur|fy|ga|gag|gan|gd|gl|glk|gn|got|gu|gv|ha|hak|haw|he|hi|hif|ho|hr|hsb|ht|hu|hy|hz|ia|id|ie|ig|ii|ik|ilo|io|is|it|iu|ja|jbo|jv|ka|kaa|kab|kbd|kg|ki|kj|kk|kl|km|kn|ko|koi|kr|krc|ks|ksh|ku|kv|kw|ky|la|lad|lb|lbe|lez|lg|li|lij|lmo|ln|lo|lt|ltg|lv|map-bms|mdf|mg|mh|mhr|mi|min|mk|ml|mn|mo|mr|mrj|ms|mt|mus|mwl|my|myv|mzn|na|nah|nap|nds|nds-nl|ne|new|ng|nl|nn|no|nov|nrm|nso|nv|ny|oc|om|or|os|pa|pag|pam|pap|pcd|pdc|pfl|pi|pih|pl|pms|pnb|pnt|ps|pt|qu|rm|rmy|rn|ro|roa-rup|roa-tara|ru|rue|rw|sa|sah|sc|scn|sco|sd|se|sg|sh|si|simple|sk|sl|sm|sn|so|sq|sr|srn|ss|st|stq|su|sv|sw|szl|ta|te|tet|tg|th|ti|tk|tl|tn|to|tpi|tr|ts|tt|tum|tw|ty|udm|ug|uk|ur|ve|vec|vep|vi|vls|vo|wa|war|wo|wuu|xal|xh|xmf|yi|yo|za|zea|zh|zh-classical|zh-min-nan|zh-yue|zu):([^\]]+)\]\]";

/* -------------------------------- Wikimedia RC IRC feed -------------------------------- */

$wikimedia = array();
$wikimedia['SOCKET'] = @fsockopen($rc_host, $rc_port, $errno, $errstr, 30);
        if($wikimedia['SOCKET']) 
        { 
                wikimediaCommand("NICK $nickname");
                wikimediaCommand("USER $nickname Addbot Wikipedia Bot");
                wikimediaCommand("JOIN $rc_channel");
                while(!feof($wikimedia['SOCKET']))//while connected to the server
                { 
                        $rawline = fgets($wikimedia['SOCKET'], 1024);

                        $line = str_replace(array("\n","\r","\002"),'',$rawline);
                        $line = preg_replace('/\003(\d\d?(,\d\d?)?)?/','',$line);
//                      echo 'FEED: '.$line."\n";
                        if (!$line) { fclose($feed); break; }
                        $linea= explode(' ',$line,4);
                        if (strtolower($linea[0]) == 'ping') {
                                wikimediaCommand("PONG :".substr($wikimedia['READ_BUFFER'], 6)); //Reply with pong
                        } elseif ((strtolower($linea[1]) == 'privmsg') and (strtolower($linea[2]) == strtolower($rc_channel))) {
                                $message = substr($linea[3],1);
                                
								//[[Special:Log/abusefilter]] hit \* (.+) \* \1 triggered [[Special:AbuseFilter/3|filter 3]], performing the action "edit" on [[([^\]]+)]].
								echo ".";
								
								//if we think it matches our tag
								//
								if (preg_match('/(Filter 531|Removal of all interwiki links)/i',$message))
								{
									echo "*";
								
									//If the line looks right split it up lots :P (from cluebot)
									if (preg_match('/^\[\[((Talk|User|Wikipedia|File|MediaWiki|Template|Help|Category|Portal|Book|Special)(( |_)talk)?:)?([^\x5d]*)\]\] (\S*) (http:\/\/en\.wikipedia\.org\/w\/index\.php\?(diff|oldid)=(\d*)&(oldid|rcid)=(\d*).*|http:\/\/en\.wikipedia\.org\/wiki\/\S+)? \* ([^*]*) \* (\(([^)]*)\))? (.*)$/S',$message,$m)) {
											$change['received'] = microtime(1);
											$change['namespace'] = $m[1];
											$change['title'] = $m[5];
											$change['flags'] = $m[6];
											$change['url'] = $m[7];
											$change['id1'] = $m[9]; //Normal=diff Newpage=oldid
											$change['id2'] = $m[11]; //Normal=oldid Newpage=rcid
											$change['user'] = $m[12];
											$change['length'] = $m[14];
											$change['comment'] = $m[15];
											$change['name'] = $m[1].$m[5];
											
											if (preg_match('/(Joe Decker|Addshore|Delusion23|Lugnuts|Naddy)/i',$change['user'])){continue;}
											
											//skipnonmain
											//CHECKS
											preg_match('/action "edit" on \[\[([^\]]+)\]\]\./',$change['comment'],$tits);
											$title = $tits[1];
											//$title = $wiki->lastedit($change['user']);
											//print_r($title);
											echo $change['user']." on ".$title."\n";
											
											$revid = $wiki->last2edits($title);
											$old = $wiki->getpage($title,$revid[1]);
											
											
											
											
			//get the other links
			$r = $wiki->wikidatasitelinks($title);
			echo "w";
			//if there is only 1 entity (i.e. the wikidata stuff isnt broken somewhere)
			if(count($r) == 1)
			{
				//foreach entitiy found
				foreach($r as $ent)
				{
					$id = $ent['id'];
					//Check if we have site links
					if(isset($ent['sitelinks']))
					{
					
						//get links that were removed $iws
						preg_match_all('/'.$regex.'/',$old,$iws);
						
						$edited = false;
						$link = "";
						
						//foreahc sitelink on page
						foreach($iws[1] as $key => $iw)
						{
							echo ",";
							//if it is not found on wikidata
							if($iw != "" && !isset($ent['sitelinks'][str_replace("-","_",$iw).'wiki']))
							{
								echo "l";
								$edited = true;
								
								$link = $link."[http://www.wikidata.org/w/index.php?title=Special%3AItemByTitle&site=".urlencode($iw)."wiki&page=".urlencode($iws[2][$key])." $iw],";
								
								
								//post

							}
						}
						
						if($edited == true)
						{
							$log = "=== [[User talk:".$change['user']."]] - $title ===\nRemoved iw-($link) on [https://en.wikipedia.org/w/index.php?title=".urlencode($title)."&action=history ".$title."] that is not on [[d:$id]]";
							$wiki->edit("User:Addbot/log/wikidata",$wiki->getPage("User:Addbot/log/wikidata")."\n".$log,"Found possible incorrect removal of IW links by ".$change['user'],true);
							continue;
						}
					
					}
				}
			}								
											
											
											
											
										}
                                }
                        }
                        flush();
                }
        } 

function wikimediaCommand ($cmd) { 
        global $wikimedia; //Extends our $server array to this function 
        @fwrite($wikimedia['SOCKET'], $cmd."\r"); //sends the command to the server 
        echo "IRC<: $cmd\n"; //displays it on the screen 
} 

?>