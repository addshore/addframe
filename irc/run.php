<?php

include("irc_class.php");
include("config.php");

$feedcount = 1;
$feeds = Array();

$c = 0;
//For each language
foreach ($languages as $l)
{
	//See if we need a new feed
	if($c == 5)
	{
		$c = 0;
		$feedcount++;
	}
	//if feed is not set create it
	if(!isset($feeds[$feedcount]))
	{
		$feeds[$feedcount] = new ConnectIrc(FEED_HOST, FEED_PORT);
	}
	//increment the number of channels we are now in
	$c++;
}

//for each feed we say we want
foreach ($feeds as $key => $feed)
{
	//if we can open the socket
	if ( $feed->openSocket() ) {
		//set username
		$username = "Addbotg".$key;
		$feed->setNick($username);
		$feed->setUser($username);
		//while connected
		while ( $irc->connected() ) {
			$buffer = $irc->showReadBuffer();
			$irc->returnLastSaid($message);
		}
	}
}

		// While you are connected to the server
		while ( $irc->connected() ) {

			// Print out the read buffer
			$buffer = $irc->showReadBuffer();
			//echo $buffer."\n\r";

			// Here is where you test for certain conditions
			$irc->returnLastSaid($message);
			$params = trim($message[PARAMS]);
			switch ($message[COMMAND])
			{
				// Shutting down
				case "!gtfo":
					echo "Shutting down\n\r";
					$irc->closeConnection(); exit;
					break;

				// Saying hello
				case "!hello":		
					echo "Saying hello to {$message[WHERE]}\n\r";
 					$irc->say("Hey, {$message[SENDER]}!", $message[WHERE]);
					break;

				// Handles joining rooms
				case "!join":
					echo "Joining {$params}\n\r";
					$channel = $params;
					$irc->joinChannel($channel);
					break;

				// handles parting rooms
				case "!part":
					echo "Leaving {$params}\n\r";
					$channel = $params;
					$irc->partChannel($channel);
					break;

				// changing nickname
				case "!nick":
					echo "Changing nick to {$params}\n\r";
					$irc->setNick($params);
					break;

				// grabbing someone's twitter status
				case "!twitter":
					echo "Grabbing status for {$params}\n\r";
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, "http://api.twitter.com/1/statuses/user_timeline/{$params}.json");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					$json = curl_exec($ch);
					curl_close($ch);
					$jsonObject = json_decode($json);
					$status = $jsonObject[0]->text;
					echo "Got latest Tweet from {$params}\n\t-> {$status}";
					$irc->say("{$status}", $message[WHERE]);
					break;
				// for anything else ...
				default:
					if (strtolower($message[COMMAND]) == strtolower($irc->nick) || 
						strstr(strtolower($params), strtolower($irc->nick)))
						$irc->say("What the fuck do you want, {$message[SENDER]}?", $message[WHERE]);
					break;
			}

			// Handle the ping pong
			$irc->handlePingPong();			

			// Flush the buffer
			$irc->flushIrc();
		}

		// Close the connection
		if ( $irc->closeConnection() ) {
			echo "<br />Connection closed... ";
		} else {
			echo "<br />Connection had a problem closing... wait wtf?";
		}
	}

?>