<?PHP

/* -------------------------------- Bot Setup -------------------------------- */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0); 

//IRC Settings
$host = "irc.freenode.org";
$port = 6667;
$nickname = "Addnag";

/* -------------------------------- Wikimedia RC IRC feed -------------------------------- */

$irc = array();
$irc['SOCKET'] = @fsockopen($host, $port, $errno, $errstr, 30);
set_time_limit(0); 
if($irc['SOCKET']) 
{ 
        ircCommand("NICK $nickname");
        ircCommand("USER $nickname $nickname Wikipedia Bot");
        ircCommand("JOIN ##addshore");
		ircCommand("JOIN ###addshore");
		ircCommand("JOIN #wikimedia-labs-nagios");
		ircCommand("JOIN #wm-bot");
        while(!feof($irc['SOCKET']))//while connected to the server
        { 
                $rawline = fgets($irc['SOCKET'], 1024);

                $line = str_replace(array("\n","\r","\002"),'',$rawline);
                $line = preg_replace('/\003(\d\d?(,\d\d?)?)?/','',$line);
				echo 'IRC: '.$line."\n";
                $linea= explode(' ',$line,4);
				 //Reply with pong
                if (strtolower($linea[0]) == 'ping') {
                    //ircCommand("PONG :".substr($irc['READ_BUFFER'], 6));
					ircCommand("PONG :".substr($irc['READ_BUFFER'], 6));
				//Messages
                } elseif ((strtolower($linea[1]) == 'privmsg')){
					$channel = strtolower($linea[2]);
					$message = substr($linea[3],1);
					if($channel == "#wikimedia-labs-nagios") {
						if(preg_match('/(bots-(sql3|bnr1|bsql01)|bastion1).pmtpa.wmflabs/i',$message))
						{MessageMe($message);}
					}elseif($channel == "###addshore") {
						MessageMe($message);
					}elseif($channel == "#wm-bot") {
						if(preg_match('/##addshore/i',$message))
						{MessageMe($message);}
					}
                }
                flush();
        }
} 

function MessageMe ($message) {
        ircCommand("PRIVMSG ##addshore :$message");
} 

function ircCommand ($cmd) { 
        global $irc; //Extends our $server array to this function 
        @fwrite($irc['SOCKET'], $cmd."\r"); //sends the command to the server 
        echo "IRC<: $cmd\n"; //displays it on the screen 
} 

?>
