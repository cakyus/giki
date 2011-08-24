<?php
/*
 *  Giki - a flat-file PHP wiki
 *  Copyright (C) 2003  Sam Thursfield
 *  Copyright (C) 2005, 2006  Gregor Richards
 *
 *  This file is part of Giki.
 *
 *  Giki is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  Giki is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Giki; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

require_once("config.php");
require_once("language.php");
require_once("display.php");
require_once("logininc.php");

$title = @$_GET['title'];
if (!$title) $title = @$_POST['title'];

$rettitle = @$_GET['return'];
if (!$rettitle) $rettitle = @$_POST['return'];
if (!$rettitle) $rettitle = $title;

$hist = @$_GET['hist'];
$node = @$_POST['node'];
$rev = @$_POST['rev'];
$preview = @$_POST['preview'];

$t=$title;
$t = str_replace(array("/", "\\", ".."), "", $t); // Don't allow directory traversal
$h=$hist;
$h = str_replace(array("/", "\\", ".."), "", $h); // Don't allow directory traversal
$rt=$rettitle;
$rt = str_replace(array("/", "\\", ".."), "", $rt); // Don't allow directory traversal

// Check the password
//
if (@$_COOKIE['wikiticket']) {
    if (!verifyuser(stripslashes($_COOKIE['wikiticket']))) {
        // invalid login!
        //
        invalidlogin();
    }
}

if (!$title && !$hist) {
		
    //No node title given
    //
    $title=$invparam;
    $node=$invparam_d;
    $bar=array("<a href='index.php'>index</a>");
} else if ($title && $node) {
    // Fix new data
    //
    $node=stripslashes($node);
    $onode = "";
    
    // Normalize newlines
    //
    $node = str_replace(array("\r\n", "\r"), "\n", $node);
    
    $inpre = false;
    $inscript = false;
    for ($i = 0; $i < strlen($node); $i++) {
        $char = substr($node, $i, 1);
        if ($char == "<") {
            if (strtolower(substr($node, $i, 4)) == "<pre") {
                // if it's "pre," mark it
                $inpre = true;
            } else if (strtolower(substr($node, $i, 5)) == "</pre") {
                $inpre = false;
            } else if (strtolower(substr($node, $i, 7)) == "<script") {
                $inscript = true;
            } else if (strtolower(substr($node, $i, 8)) == "</script") {
                $inscript = false;
            }
            $onode .= $char;
        } else if ($char == "\n") {
            // only add a <br> if we're not in <pre> or <script>
            if (!$inpre && !$inscript) {
                $onode .= "<br>";
            }
            $onode .= "\n";
        } else {
            $onode .= $char;
        }
    }
    $node = $onode;
}

// the processing phase is over, now for the display phase
if (!@$_COOKIE['wikiticket'] && !$allow_guests) {

    // No login cookie present
    //
    $title=$invlogin;
    $node=$invlogin_d;
    $bar=array("<a href='login.php?title=$rt'>$login</a>", "<a href='index.php?title=$rt'>$cancel</a>");
} else if ($title && $node && !$preview) {
    // we have everything we need to write
    $dowrite = true; // Hopefully the plugin that false'd this also set title, node and bar.
    // Load pre plugins
    foreach ($plugins as $plugin) {
        @include("gikiplugin-pre-$plugin.php");
    }
    
    if ($dowrite == true) {
        // Read old data
        //
        $oldcontents = getNodeContents($t);
        if ($oldcontents !== false) {
            $revhistory = $oldcontents["revhistory"];
        } else {
            $revhistory = array();
        }
        
        // Write new data
        //
        $contents = array("text" => $node, "revhistory" => array());
        
        // Update modification data
        //
        if ($_COOKIE['wikiticket']) {
            $up=explode(":::", stripslashes($_COOKIE['wikiticket']));
            $user=$up[0];
        } else {
            $user = "Guest";
        }
        
        if ($rev == "on") {
            $revhistory[-1] = $user . " - " . date("j/n/y - h:i a");
            
            // get rid of element 2 if applicable (only keep three history)
            unset($revhistory[2]);
            ksort($revhistory);
            $revhistory = array_values($revhistory);
            
            $contents["revhistory"] = $revhistory;
        }
        
        putNodeContents($t, $contents, $user);
        
        $title=$success;
        $node='<b>'.$t.'</b>'.$success_d;
        $bar=array("<a href='index.php?title=$rt'>$return</a>");

        // Load post plugins
        foreach ($plugins as $plugin) {
            @include("gikiplugin-post-$plugin.php");
        }
    }
    
} else {

    // Node edit form
    //
    
    if (!$hist) {
        $node = stripslashes($node);
        $onode = "";
        
        if ($preview) {
            // show the current preview
            $onode = "<h1>$tpreview</h1>" .
                parsecontents($t, $node) .
                "<hr>" .
                "<h1>$edit</h1>";
            $contents = array("text" => $node);
        } else {
            $contents = getNodeContents($t);
        }
        
        $onode .= '<form method="post" action="edit.php"><textarea wrap="soft" name="node" cols=80 rows=10>';
    } else {
        $onode='<textarea wrap="soft" cols=80 rows=10>';
        $contents = getNodeHistory($t, $h);
    }
    
    if ($contents !== false) {
        $node = str_replace("&", "&amp;", $contents["text"]);
        $node = rtrim($node);
        $node = str_replace(
            "<br>\n",
            "\n", $node); // <br> to linebreak
        $node = preg_replace("|<br>\$|", "", $node);
        $onode .= $node;
    }
    
    if (!$hist) {
        $onode.="</textarea><br>" .
            "<input name=\"rev\" type=\"checkbox\" checked>$track<br>" .
            "<input type=\"hidden\" name=\"title\" value=\"$t\">" .
            "<input type=\"hidden\" name=\"return\" value=\"$rt\">" .
            "<input type=\"submit\" name=\"submit\" value=\"Submit\">" .
            "<input type=\"submit\" name=\"preview\" value=\"Preview\">" .
            "</form>";
    } else {
        $onode.="</textarea>";
    }

    $onode .= $link_help;
    
    $node = $onode;
    $bar = array("<a href='index.php?title=$rt'>$return</a>");
    
    // Load edit plugins
    foreach ($plugins as $plugin) {
        @include("gikiplugin-edit-$plugin.php");
    }
}

display($title, $node, $bar, $template);
?>
