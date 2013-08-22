<?php

return call_user_func( function() {

	$classes = array(
		'Addframe\Mediawiki\Site' => 'Site.php',
		'Addframe\Mediawiki\SiteList' => 'SiteList.php',
	);

	return $classes;

} );
