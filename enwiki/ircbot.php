<?PHP

/* -------------------------------- Bot Setup -------------------------------- */

//IRC Settings
$server_host = "irc.freenode.com"; 
$server_port = 6667; 
$server_chan = "##addshore";
$user = "Addbot1";
$nickname = "Addbot1";

/* -------------------------------- Freenode Irc -------------------------------- */

$freenode = array(); 
$freenode['SOCKET'] = @fsockopen($server_host, $server_port, $errno, $errstr, 2);
        if($freenode['SOCKET']) 
        { 
                freenodeCommand("NICK ".$user); //sends the nickname 
                freenodeCommand("USER ".$user." Addbot Wikipedia Bot");
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
                                                case 'help':
                                                        freenodeCommand($cmd.' :!check Namespace:Article');
                                                        break;
												case 'check':
														//if it matches something like a page
														if(preg_match("/!check (([a-z _]*?:)?.*?)$/i",$freenode['READ_BUFFER'],$n))
														{
															//escape and run
															$check = escapeshellarg($n[1]);
															freenodeCommand($cmd.' :Checking '.$check);
															$torun = "php /data/project/addbot/enwiki/run.php --page=$check";
															echo $torun;
															//fork onto another process so the bot will keep running on irc
															$pid = pcntl_fork();
															if(!$pid){
																echo exec($torun);
																die();
															}
														}
                                                        break;
                                        }
                                }
                        }
                        flush();
                }
        }

function MessageMe ($message) {
        global $server_chan;
        freenodeCommand("PRIVMSG $server_chan :$message");
}

function freenodeCommand ($cmd) { 
        global $freenode; //Extends our $server array to this function  
        @fwrite($freenode['SOCKET'], $cmd."\r"); //sends the command to the server 
        echo "IRC<: $cmd\n"; //displays it on the screen 
}

?>

