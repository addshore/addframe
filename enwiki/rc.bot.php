<?PHP

/* -------------------------------- Bot Setup -------------------------------- */

error_reporting(E_ALL);
ini_set('display_errors', 1);

//From http://toolserver.org/~chris/highlight.php?d=chris/classes/&f=botclasses.php
require '/data/project/addbot/classes/botclasses.php';

$wiki = new wikipedia;
$wiki->url = 'http://en.wikipedia.org/w/api.php';
global $wiki;

$parentpid = posix_getpid();

$user = "Addbot";
$owner = "Addshore";

$mysandbox = "User:".$owner."/Sandbox";

set_time_limit(0); 
require '/home/addshore/.password.addbot';
$wiki->login($user,$config['password']);
echo "USER: Logged In!\n";
unset($config['password']);

//IRC Settings
$rc_host = "irc.wikimedia.org";
$rc_port = 6667;
$rc_channel = "#en.wikipedia";
$nickname = "Addbotrc";

/* -------------------------------- Wikimedia RC IRC feed -------------------------------- */

$wikimedia = array();
$wikimedia['SOCKET'] = @fsockopen($rc_host, $rc_port, $errno, $errstr, 30);
set_time_limit(0); 
if($wikimedia['SOCKET']) 
{ 
        wikimediaCommand("NICK $nickname");
        wikimediaCommand("USER $nickname $nickname Wikipedia Bot");
        wikimediaCommand("JOIN $rc_channel");
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
		
function wikimediaCommand ($cmd) { 
        global $wikimedia; //Extends our $server array to this function 
        @fwrite($wikimedia['SOCKET'], $cmd."\r"); //sends the command to the server 
        echo "IRC<: $cmd\n"; //displays it on the screen 
} 

?>
