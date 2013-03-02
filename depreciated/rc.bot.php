<?PHP

/* -------------------------------- Bot Setup -------------------------------- */
global $instance, $freenode;	

require 'bot.login.php';

//IRC Settings
$server_host = "irc.freenode.com"; 
$server_port = 6667; 
$server_chan = "##addshore";
$rc_host = "irc.wikimedia.org";
$rc_port = 6667;
$rc_channel = "#en.wikipedia";

$instance[0]['id'] = 0;
$instance[0]['channel'] = "##Addshore";
$instance[0]['regex'] = "/(WP:(HG|TW))/i";
$instance[0]['regex'] = "/(WP:(HG|TW))/i";
/*$instance[1]['id'] = 1;
$instance[1]['channel'] = "##Addshore";
$instance[0]['regex'] = "/Addbot/i";
$instance[2]['id'] = 2;
$instance[2]['channel'] = "##Addshore";
$instance[0]['regex'] = "/John F\. Lewis/i";
$instance[3]['id'] = 3;
$instance[3]['channel'] = "##Addshore";
$instance[0]['regex'] = "/(Riley Huntley)/i";*/


global $instance, $freenode;	

/* -------------------------------- Freenode Irc -------------------------------- */

foreach($instance as $t){
	$pid = pcntl_fork();
	if(!$pid){
		$freenode[$t['id']] = array(); 
		$freenode[$t['id']]['SOCKET'] = @fsockopen($server_host, $server_port, $errno, $errstr, 2);
				if($freenode[$t['id']]['SOCKET']){ 
						@fwrite($freenode[$t['id']]['SOCKET'], "NICK ".$user.$t['id']."\r");
						@fwrite($freenode[$t['id']]['SOCKET'], "USER ".$user.$t['id']." Addbot Wikipedia Bot"."\r");
						@fwrite($freenode[$t['id']]['SOCKET'], "JOIN ".$t['channel']."\r");
						while(!feof($freenode[$t['id']]['SOCKET'])){ 
								$freenode[$t['id']]['READ_BUFFER'][$t['id']] = str_replace(array("\n","\r"),'',fgets($freenode[$t['id']]['SOCKET'], 1024)); //get a line of data from the server
								if ( !preg_match('/(00(1|2|3|4|5)|2(5(0|1|2|4|5)|6(5|6))|3(53|66|7(2|6|5))) '.$nickname.'/', $freenode[$t['id']]['READ_BUFFER'][$t['id']]))
								{ echo "IRC>: ".$freenode[$t['id']]['READ_BUFFER'][$t['id']]."\n";}
								$d = explode(' ',$freenode[$t['id']]['READ_BUFFER'][$t['id']]);
								if (strtolower($d[0]) == 'ping') { @fwrite($freenode[$t['id']]['SOCKET'], "PONG :".substr($freenode[$t['id']]['READ_BUFFER'][$t['id']], 6)."\r");}
								flush(); } }
				unset ($pid['freenode'][$t['id']]);exit();
		break;
	}
}

/* -------------------------------- Wikimedia RC IRC feed -------------------------------- */

$wikimedia = array();
$wikimedia['SOCKET'] = @fsockopen($rc_host, $rc_port, $errno, $errstr, 30);
$wpid['wikimedia'] = pcntl_fork();
if ( $wpid['wikimedia'] == 0 ) {
        set_time_limit(0); 
        if($wikimedia['SOCKET']) 
        { 
                wikimediaCommand("NICK $user");
                wikimediaCommand("USER $user Addbot Wikipedia Bot");
				//wikimediaCommand("LIST");
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
                                        
						
foreach($instance as $check)
{
    if ( preg_match($check['regex'],$rawline))
    {
            //Message($check['id'],"4".$change['user']." - 13".$change['namespace'].$change['title']." - 12".$change['comment']." - 15".$change['url']);
			@fwrite($freenode[$check['id']]['SOCKET'], "PRIVMSG ".$check['channel']." :$message"."\r");
			echo "PRIVMSG ".$check['channel']." :$message"."\r";
    }
	
	
}
														
										
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
foreach ($pid as $pid) {
        //posix_kill($pid);
}
exit();

?>
