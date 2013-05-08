<?php
define( 'DATAVALUES', true );
include_once __DIR__ . '/extensions/DataValues/DataValues.php';

$dir = __DIR__ . '/wikibase/';
include_once $dir . 'Http.php';
include_once $dir . 'Api.php';
include_once $dir . 'WikibaseApi.php';
include_once $dir . 'EntityId.php';
include_once $dir . 'EntityProvider.php';
include_once $dir . 'Entity.php';
include_once $dir . 'Item.php';
include_once $dir . 'Property.php';
include_once $dir . 'Claim.php';
include_once $dir . 'Statement.php';
include_once $dir . 'Snak.php';

$wgDataValues['wikibase-entityid'] = 'EntityId';
$wgDataTypes += array(
	'wikibase-entityid' => array(
		'datavalue' => 'wikibase-entityid',
	)
);
