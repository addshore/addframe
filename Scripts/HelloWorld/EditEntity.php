<?php
/**
 * This file makes a simple edit
 *
 * @author Addshore
 *
 **/

use Addframe\Config;
use Addframe\Mediawiki\Family;
use Addframe\Mediawiki\UserLogin;
use Addframe\Mediawiki\Wikibase\Entity;

require_once( dirname( __FILE__ ) . '/../../Init.php' );

$wm = new Family(
	new UserLogin( Config::get( 'wikiuser', 'username'),
		Config::get( 'wikiuser', 'password') ), Config::get( 'wikiuser', 'home') );

$wikidata = $wm->getSite( 'www.wikidata.org' );
$sandbox = Entity::newFromId( $wikidata, 'q4115189' );
$sandbox->addAlias( 'en', "This is a new Alias" );
$sandbox->save();