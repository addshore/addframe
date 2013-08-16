<?php
/**
 * This file makes a simple edit to multiple wikis
 *
 * @author Addshore
 * @author John
 *
 **/
 
use Addframe\Family;
use Addframe\Globals;
use Addframe\UserLogin;
 
require_once( dirname( __FILE__ ) . '/../../init.php' );
 
$wm = new Family( new UserLogin( Config::get( 'wikiuser', 'username'),
		Config::get( 'wikiuser', 'password') ), Config::get( 'wikiuser', 'home') );
 
$wikis = array('en.wikipedia.org','cy.wikipedia.org','sco.wikipedia.org','de.wikipedia.org');
 
foreach( $wikis as $wikiurl ){
        $site= $wm->getSite( $wikiurl );
        $page= $site->newPageFromTitle( 'Wikipedia:Sandbox' );
        $page->appendText( 'This is a simple edit to this page!' );
        $page->save( 'This is a simply summary.');
}
