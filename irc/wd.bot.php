<?PHP

/* -------------------------------- Bot Setup -------------------------------- */

//IRC Settings
$server_host = "irc.freenode.com"; 
$server_port = 6667; 
$server_chan = "##addshore";
$rc_host = "irc.wikimedia.org";
$rc_port = 6667;
$nickname = "Addbotg";
$langs = Array('ab','ace','af','ak','als','am','an','ang','ar','arc','arz','as','ast','av','ay','az','ba','bar','bat-smg','bcl','be','be-x-old','bg','bh','bi','bjn','bm','bn','bo','bpy','br','bs','bug','bxr','ca','cbk-zam','cdo','ce','ceb','ch','chr','chy','ckb','co','cr','crh','cs','csb','cu','cv','cy','da','de','diq','dsb','dv','dz','ee','el','eml','en','eo','es','et','eu','ext','fa','ff','fi','fiu-vro','fj','fo','fr','frp','frr','fur','fy','ga','gag','gan','gd','gl','glk','gn','got','gu','gv','ha','hak','haw','he','hi','hif','hr','hsb','ht','hu','hy','ia','id','ie','ig','ik','ilo','io','is','it','iu','ja','jbo','jv','ka','kaa','kab','kbd','kg','ki','kk','kl','km','kn','ko','koi','krc','ks','ksh','ku','kv','kw','ky','la','lad','lb','lbe','lez','lg','li','lij','lmo','ln','lo','lt','ltg','lv','map-bms','mdf','mg','mhr','mi','min','mk','ml','mn','mr','mrj','ms','mt','mwl','my','myv','mzn','na','nah','nap','nds','nds-nl','ne','new','nl','nn','no','nov','nrm','nso','nv','ny','oc','om','or','os','pa','pag','pam','pap','pcd','pdc','pfl','pi','pih','pl','pms','pnb','pnt','ps','pt','qu','rm','rmy','rn','ro','roa-rup','roa-tara','ru','rue','rw','sa','sah','sc','scn','sco','sd','se','sg','sh','si','simple','sk','sl','sm','sn','so','sq','sr','srn','ss','st','stq','su','sv','sw','szl','ta','te','tet','tg','th','ti','tk','tl','tn','to','tpi','tr','ts','tt','tum','tw','ty','udm','ug','uk','ur','ve','vec','vep','vi','vls','vo','wa','war','wo','wuu','xal','xh','xmf','yi','yo','za','zea','zh','zh-classical','zh-min-nan','zh-yue','zu');

/* -------------------------------- Freenode Irc -------------------------------- */

$freenode = array(); 
$freenode['SOCKET'] = @fsockopen($server_host, $server_port, $errno, $errstr, 2);
$pid['freenode'] = pcntl_fork();
if ( $pid['freenode'] == 0 ) {
        set_time_limit(0); 
        if($freenode['SOCKET']) 
        { 
                freenodeCommand("NICK $nickname"); //sends the nickname 
                freenodeCommand("USER $nickname Addbot Wikipedia Bot");
                freenodeCommand("JOIN $server_chan");
                while(!feof($freenode['SOCKET'])) //while we are connected to the server 
                { 
                        $freenode['READ_BUFFER'] = str_replace(array("\n","\r"),'',fgets($freenode['SOCKET'], 1024)); //get a line of data from the server
                        if ( !eregi('(00(1|2|3|4|5)|2(5(0|1|2|4|5)|6(5|6))|3(53|66|7(2|6|5))) '.$nickname, $freenode['READ_BUFFER']))
                        {
                                echo "IRC>: ".$freenode['READ_BUFFER']."\n";
                        }

                        $d = explode(' ',$freenode['READ_BUFFER']);
                        if (strtolower($d[0]) == 'ping') {
                                freenodeCommand("PONG :".substr($freenode['READ_BUFFER'], 6)); //Reply with pong
                        } elseif (strtolower($d[1]) == 'privmsg') {
						/*
                                if (substr($d[3],0,2) == ':!') {
                                        if (strtolower($d[2]) == strtolower($user)) { 
                                                $tmp = explode('!',substr($d[0],1));
                                                $cmd = 'NOTICE '.$tmp[0]; }
                                        else { $cmd = 'PRIVMSG '.$d[2]; }

                                        switch (substr(strtolower($d[3]),2)) {
                                                case 'help':
                                                        freenodeCommand($cmd.' :Please ask Addshore');
                                                        break;
                                        }
                                }*/
                        }
                        flush();
                }
        }
        unset ($pid['freenode']);
        exit();
}

function MessageMe ($message) {
        global $server_chan;
        if ($go = true ){
			if(rand(1,2)  == 1)
			{freenodeCommand("PRIVMSG $server_chan :$message");}
			else{floodCommand("PRIVMSG $server_chan :$message");}
				
        }
} 
function freenodeCommand ($cmd) { 
        global $freenode; //Extends our $server array to this function 
        @fwrite($freenode['SOCKET'], $cmd."\r"); //sends the command to the server 
        echo "IRC<: $cmd\n"; //displays it on the screen 
} 

function floodCommand ($cmd) { 
        global $flood; //Extends our $server array to this function 
        @fwrite($flood['SOCKET'], $cmd."\r"); //sends the command to the server 
        echo "IRC<: $cmd\n"; //displays it on the screen 
} 

$flood = array(); 
$flood['SOCKET'] = @fsockopen($server_host, $server_port, $errno, $errstr, 2);
$pid['flood'] = pcntl_fork();
if ( $pid['flood'] == 0 ) {
        set_time_limit(0); 
        if($flood['SOCKET']) 
        { 
                floodCommand("NICK $nickname"."2"); //sends the nickname 
                floodCommand("USER $nickname"."2 Addbot Wikipedia Bot");
                floodCommand("JOIN $server_chan");
                while(!feof($flood['SOCKET'])) //while we are connected to the server 
                { 
                        $flood['READ_BUFFER'] = str_replace(array("\n","\r"),'',fgets($flood['SOCKET'], 1024)); //get a line of data from the server
                        if ( !eregi('(00(1|2|3|4|5)|2(5(0|1|2|4|5)|6(5|6))|3(53|66|7(2|6|5))) '.$nickname, $flood['READ_BUFFER']))
                        {
                                echo "IRC>: ".$flood['READ_BUFFER']."\n";
                        }
                        $d = explode(' ',$flood['READ_BUFFER']);
                        if (strtolower($d[0]) == 'ping') {
                                floodCommand("PONG :".substr($flood['READ_BUFFER'], 6)); //Reply with pong
                        }
                        flush();
                }
        }
        unset ($pid['flood']);
        exit();
}

/* -------------------------------- Wikimedia RC IRC feed -------------------------------- */

$wikimedia = array();
$wikimedia['SOCKET'] = @fsockopen($rc_host, $rc_port, $errno, $errstr, 30);
$pid['wikimedia'] = pcntl_fork();
if ( $pid['wikimedia'] == 0 ) {
        set_time_limit(0); 
        if($wikimedia['SOCKET']) 
        { 
                wikimediaCommand("NICK $nickname");
                wikimediaCommand("USER $nickname Addbot Wikipedia Bot");
				foreach ($langs as $l)
				{
					wikimediaCommand("JOIN #".$l.".wikipedia");
					sleep(0.3);
				}
                while(!feof($wikimedia['SOCKET']))//while connected to the server
                { 
                        $rawline = fgets($wikimedia['SOCKET'], 1024);

                        $line = str_replace(array("\n","\r","\002"),'',$rawline);
                        $line = preg_replace('/\003(\d\d?(,\d\d?)?)?/','',$line);
                      echo 'FEED: '.$line."\n";
                        if (!$line) { fclose($feed); break; }
                        $linea= explode(' ',$line,4);
                        if (strtolower($linea[0]) == 'ping') {
                                wikimediaCommand("PONG :".substr($wikimedia['READ_BUFFER'], 6)); //Reply with pong
                        } elseif ((strtolower($linea[1]) == 'privmsg')) {
                                $message = substr($linea[3],1);
                                echo $message."\n";
									if(preg_match('/Add(shore|bot|less)/i',$message))
										{
											Messageme($message);
											sleep(0.5);
											continue;
										}
                        }
                        flush();
                }
        } 
        unset ($pid['wikimedia']);
        exit();
}
function wikimediaCommand ($cmd) { 
        global $wikimedia; //Extends our $server array to this function 
        @fwrite($wikimedia['SOCKET'], $cmd."\r"); //sends the command to the server 
        echo "IRC<: $cmd\n"; //displays it on the screen 
} 

//Wait
while ($pid['freenode'] &&  $pid['wikimedia']){
sleep(1);
}

//Kill all the processes
MessageMe("Ending");
foreach ($pid as $pid) {
        posix_kill($pid);
}
exit();

?>
