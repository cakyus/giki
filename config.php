<?php
// Edit this file to change the Giki script configuration.

// Some forward compatibility stuff
//
if(!isset($_SERVER)) {
    $_GET = @$HTTP_GET_VARS;
    $_POST = @$HTTP_POST_VARS;
}

// The directory data is stored in (must be world-writable and, if possible, above
// the web root). This can be a relative path, but must have the trailing slash.
//
$nodedir = "../wiki/";
	
// Should this wiki allow guests to post?
//
$allow_guests = false;

// Should this wiki allow file uploading?
//
$allow_uploads = false;

// Log all attempts to login or register?
//
$remotelog = false;

// The file used as a template for the entire script
//
$template = "template.html";

// These settings control how the bar is displayed.
// There is a prefix, a separator, and a postfix, examples would be:
//  $bar_prefix = "<table border=1><tr><td>";
//  $bar_separator = "</td><td>";
//  $bar_postfix = "</td></tr></table>";
// That example would turn the bar elements into a table, but there are many other possibilities
//
$bar_prefix = "";
$bar_separator = " - ";
$bar_postfix = "";

// Plugins
// Here you can make an array of plugins to to alter node output
// To use GWS with no HTML for example:
//  $plugins = array("noHTML", "GWS");
//
$plugins = array();
?>
