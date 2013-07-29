<?php
/**
 * This file makes a simple edit
 *
 * @author Addshore
 *
 **/

use Addframe\Family;
use Addframe\Globals;
use Addframe\UserLogin;

require_once( dirname( __FILE__ ) . '/../../init.php' );

$wm = new Family(
	new UserLogin( Globals::$config['user.addbot']['user'],
		Globals::$config['user.addbot']['password'] ), 'meta.wikimedia.org' );

$enwiki = $wm->getSite( 'en.wikipedia.org' );
$sandbox = $enwiki->newPageFromTitle( 'Wikipedia:Sandbox' );
$sandbox->appendText( 'This is a simple edit to this page!' );
$sandbox->save( 'This is a simply summary');

