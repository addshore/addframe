<?php

/**
 * Include the entrance file
 */
require_once( __DIR__ . '/../Addframe.php' );

/**
 * Turn default logging off
 * This makes sure we don't spoil logs we actually use..
 */
\Addframe\Logger::setDefaultSeverityThreshold( \Addframe\Logger::ALL );