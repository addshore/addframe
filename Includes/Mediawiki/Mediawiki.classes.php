<?php

/**
 * @since 0.0.4
 * @author addshore
 */
return call_user_func( function() {

	$classes = array(
		'Mediawiki\Category' => 'Mediawiki/Category.php',
		'Mediawiki\Family' => 'Mediawiki/Family.php',
		'Mediawiki\Image' => 'Mediawiki/Image.php',
		'Mediawiki\Page' => 'Mediawiki/Page.php',
		'Mediawiki\PageList' => 'Mediawiki/PageList.php',
		'Mediawiki\Regex' => 'Mediawiki/Regex.php',
		'Mediawiki\Site' => 'Mediawiki/Site.php',
		'Mediawiki\Template' => 'Mediawiki/Template.php',
		'Mediawiki\User' => 'Mediawiki/User.php',
		'Mediawiki\UserLogin' => 'Mediawiki/UserLogin.php',
		'Mediawiki\Wikitext' => 'Mediawiki/Wikitext.php',
		'Mediawiki\WikitextParser' => 'Mediawiki/WikitextParser.php',
	);

	return $classes;

} );
