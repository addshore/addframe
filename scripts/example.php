<?php
/**
 * This file is an example use of various parts of the frame
 * @author Addshore
 **/

//Initiate the script
require_once( dirname( __FILE__ ) . '/../init.php' );

//Create a site
$testsite = Globals::$Sites->addSite( "localhost", "127.0.0.1/Mediawiki/api.php" );
$testsite->newLogin( 'Bot', 'botp123', true );

//Play with a regular page
$playpage = $testsite->getPage( "PageTitle" );
$playpage->load();
$playpage->emptyText();
$playpage->text = "This is some starting text with a random number >> " . rand( 0, 100 );
$playpage->appendText( "\nThis shoulddd be added to the end of the page" );
$playpage->prependText( "This is the first line! :O\n" );
$playpage->replaceString( 'shoulddd', 'should' );
$playpage->save( "edit summary for edit", true );

//Play with a wikibase entity
$playentity = $testsite->getEntity( 'q70' );
$playentity->loadEntity();
$playentity->addLabel( 'en-gb', 'Item Label' );
$playentity->addDescription( 'en-gb', 'This is a description added by the script' );
$playentity->addAlias( 'en-gb', 'alias1' );
$playentity->addAlias( 'en-gb', 'alias2' );
$playentity->addAlias( 'en-gb', 'alias3' );
$playentity->saveEntity();