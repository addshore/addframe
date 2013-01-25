<?
global $config;

// main settings
$config['url'] = 'en.wikipedia.org';                        // wiki url we will be working on
$config['user'] = 'Addbot';                                 // bot username for login
$config['owner'] = 'Addshore';                              // bot owner
$config['sandbox'] = 'User:'.$config['user'].'/Sandbox';    // sandbox location
$config['debug'] = true;									// true for debugging
require '/home/addshore/.password.addbot';                  // $config['password'] = 'password';

$config['checkfreq'] = 24;                        // how long in hours before a page can be checked again

// database settings
$config['dbhost'] = 'bots-sql3';
$config['dbport'] = '3306';
$config['dbuser'] = 'addshore';
require '/home/addshore/.password.db';            //$config['dbpass'] = 'password';
$config['dbname'] = 'addbot';

// table settings
$config['tblist'] = 'pending';                 // table containing articles to be checked
$config['tbdone'] = 'checked';                // table containing articles checked along with time

require 'classes/template.php';

// regex to match the date used in maintanace templates
$config['date'] = '((January|February|March|April|May|June|July|August|September|October|November|December) ?20[0-9][0-9])';

$config['tag']['uncat'] = new Template('Uncategorized',array('Cat needed','CatNeeded','Categories needed','Categories requested','Categorise','Categorize','Category needed','Category requested','Categoryneeded','Cats needed','Needs Cat','Needs Cats','No categories','No category','Nocat','Nocategory','Nocats','Uncat','Uncategorised'),array('date'));
$config['tag']['orphan'] = new Template('Orphan',array('Do-attempt','Lonely','Orp'),array('date','att','geo','few','incat'));
$config['tag']['deadend'] = new Template('Deadend',array('Dep','Dead end page','Dead-end','Needs links'),array('date'));
$config['tag']['sections'] = new Template('Sections',array('Needsections','Cleanupsections','Needs sections',),array('date'));
$config['tag']['unref'] = new Template('Unreferenced',array('Citesources','Cleanup-cite','NR','Needs references','No ref','No reference','No references','No refs','No sources','Noref','Noreference','Noreferences','Norefs','Nosources','Nr','Ref needed','References','References-needed','Refs needed','Refsneeded','UNref','Uncited-article','Unref','Unrefarticle','Unreferences article','Unreferenced stub','UnreferencedArticle','Unsourced','Unverified'),array('date'));
$config['tag']['emptysection'] = new Template('Emptysection',array('Empty-section','Emptysect','No content'),array('date'|'small'),false);
$config['tag']['wikify'] = new Template('Wikify',array('Wf','Wfy','Wiki','Wikify section','Wikify-section','Wikifying','Wkfy'),array('date'),false);
$config['tag']['badformat'] = new Template('Bad format',array('BadFormat','BadPDF'),array(),false);

?>