<?php

$config['myurl'] = "//tools.wmflabs.org/addshore/";

function getSideBar()
{
	global $config;
	return "<div id='sidebar'>
		<h4><a href='".$config['myurl']."'>Addshore's tools</a></h4>
			<h5>Wikimedia</h5>
			<ul>
			<li><a href='".$config['myurl']."addbot/status' title='monitor addbots current stats'>Addbot Status</a></li>
			<li><a href='".$config['myurl']."addbot/iwlinks' title='remaining wikipedia interwiki links'>Remaining interwikis</a></li>
			<li><a href='".$config['myurl']."csdf8' title='english wikipedia CFD F8 compare tool'>CSDF8 comparison</a></li>
			<li><a href='".$config['myurl']."toolslab' title='monitor tools lab'>Tools lab stuff</a></li>
			</ul>
			<h5>generic</h5>
			<ul>
			<li><a href='".$config['myurl']."regextester' title='php regex tester'>Regex tester</a></li>
			</ul>
		</div>";
}

function getHeader($title = "No Title")
{
	return "<head>
	".getHead($title)."
	</head>";
}

function getHead($title = "No Title")
{
	global $config;
	return "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
		<title>$title</title>
		<link rel='shortcut icon' href='".$config['myurl']."favicon.ico' />
		<link rel='stylesheet' type='text/css' href='".$config['myurl']."stylesheet.css' />
		<script src='//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js' type='text/javascript'></script>";
}


?>