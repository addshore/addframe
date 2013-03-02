<?php

ini_set('memory_limit','16M');

require_once( '/home/addshore/addbot/Peachy/Init.php' );

$site = Peachy::newWiki( "addbot" );

$ignorelist = array(
	'Articles lacking sources (Erik9bot)',
	'Articles created via the Article Wizard',
	'Unreviewed new articles',
	'Article Feedback 5',
);

//$p = $template->embeddedin( array(0) );
//change the above to the below list
//http://toolserver.org/~jason/categorized_articles.php
//http://toolserver.org/~jason/data/categorized_articles_list.txt

file_get_contents('http://toolserver.org/~dpl/data/trigger_cat_file.php');
echo "Sleeping for 5";
sleep(5);
$p = file_get_contents('http://toolserver.org/~dpl/data/categorized_articles_list.txt');
$p = str_replace(']]','',$p);
$p = str_replace('[[','',$p);
$p = explode("\n",$p);


$c = 1;

$tofind = array(
	'Classify',
	'CatNeeded',
	'Uncategorised',
	'Uncat',
	'Categorize',
	'Categories needed',
	'Categoryneeded',
	'Category needed',
	'Category requested',
	'Categories requested',
	'Nocats',
	'Categorise',
	'Nocat',
	'Uncat-date',
	'Uncategorized-date',
	'Needs cat',
	'Needs cats',
	'Cat needed',
	'Cats needed',
);

foreach ($p as $pg) {
	sleep(1);
	if( $c > 100 ) break;
	
	$page = initPage($pg);

	$text = $page->get_text();
	preg_match_all('/\[\[Category:(.*?)(\|(.*?))?\]\]/Si', $text, $cats);
	if( $cats ) {
		$cats = $cats[1];
		$remove = 'no';
		foreach( $cats as $cat ) {
			if( in_array( $cat, $ignorelist ) ) {
				continue;
			}
			
			$tmp = initPage( 'Category:'.$cat );
			
			if( $tmp->get_exists() ) {
				$remove = 'yes';
				break;
			}
		}
		if( $remove == 'yes' ) {
			$newtext = preg_replace('/\{\{('.implode('|',$tofind).')(.*?)\}\}/i', '', $text);
			$diff = getTextDiff('unified', $text, $newtext);
			echo $diff;
			
			$page->edit($newtext,"[[User:Addbot|Bot:]] Removing Uncategorized template",true);
			$c++;
			sleep(45);
		}
	}
}

?>