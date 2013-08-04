<?php

/**
 * @since 0.0.4
 * @author addshore
 */
return call_user_func( function() {

	$classes = array(
		'Addframe\Mediawiki\Category' => 'Category.php',
		'Addframe\Mediawiki\Family' => 'Family.php',
		'Addframe\Mediawiki\Image' => 'Image.php',
		'Addframe\Mediawiki\Page' => 'Page.php',
		'Addframe\Mediawiki\PageList' => 'PageList.php',
		'Addframe\Mediawiki\Regex' => 'Regex.php',
		'Addframe\Mediawiki\Site' => 'Site.php',
		'Addframe\Mediawiki\Template' => 'Template.php',
		'Addframe\Mediawiki\User' => 'User.php',
		'Addframe\Mediawiki\UserLogin' => 'UserLogin.php',
		'Addframe\Mediawiki\Wikitext' => 'Wikitext.php',
		'Addframe\Mediawiki\WikitextParser' => 'WikitextParser.php',
	);

	return $classes;

} );
