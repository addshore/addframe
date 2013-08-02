<?php
/**
 * This file makes a simple edit
 *
 * @author Addshore
 *
 **/

use Addframe\Mediawiki\Family;
use Addframe\Globals;
use Addframe\Mediawiki\UserLogin;

require_once( dirname( __FILE__ ) . '/../../init.php' );

$wm = new Family(
	new UserLogin( Globals::$config['wikiuser']['username'],
		Globals::$config['wikiuser']['password'] ), Globals::$config['wikiuser']['home'] );

$wikidata = $wm->getSite( 'www.wikidata.org' );
$sandbox = $wikidata->newEntityFromEntityId( 'q4115189' );
$sandbox->addAlias( 'en', "This is a new Alias" );
$sandbox->save();