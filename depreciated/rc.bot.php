<?PHP

/* -------------------------------- Bot Setup -------------------------------- */

require 'bot.login.php';

//IRC Settings
$server_host = "irc.freenode.com"; 
$server_port = 6667; 
$server_chan = "##addshore";
$rc_host = "irc.wikimedia.org";
$rc_port = 6667;
$rc_channel = "#en.wikipedia";

/* -------------------------------- Freenode Irc -------------------------------- */

$freenode = array(); 
$freenode['SOCKET'] = @fsockopen($server_host, $server_port, $errno, $errstr, 2);
$pid['freenode'] = pcntl_fork();
if ( $pid['freenode'] == 0 ) {
        set_time_limit(0); 
        if($freenode['SOCKET']) 
        { 
                freenodeCommand("NICK ".$user."-1"); //sends the nickname 
                freenodeCommand("USER ".$user."-1 Addbot Wikipedia Bot");
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
                                if (substr($d[3],0,2) == ':!') {
                                        if (strtolower($d[2]) == strtolower($user)) { 
                                                $tmp = explode('!',substr($d[0],1));
                                                $cmd = 'NOTICE '.$tmp[0]; }
                                        else { $cmd = 'PRIVMSG '.$d[2]; }

                                        switch (substr(strtolower($d[3]),2)) {
                                                case 'count':
                                                        if (preg_match("/\[\[User:(.*)\]\]/",$freenode['READ_BUFFER'],$n)) {
                                                                freenodeCommand($cmd.' :[[User:'.$n[1].']] has '.$wiki->contribcount($n[1])." contributions.");
                                                        } else {
                                                                freenodeCommand($cmd.' :Couldn\'t find link, Please use format !Count [[User:Addbot]]');
                                                        }
                                                        break;
                                                case 'help':
                                                        freenodeCommand($cmd.' :Please ask Addshore');
                                                        break;
                                        }
                                }
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
                floodCommand("NICK ".$user."-2"); //sends the nickname 
                floodCommand("USER ".$user."-2 Addbot Wikipedia Bot");
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
                wikimediaCommand("NICK $user");
                wikimediaCommand("USER $user Addbot Wikipedia Bot");
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
                                        include 'rc.checks.php'; //check the edit data
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
