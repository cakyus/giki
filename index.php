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
require_once("stdgikiparse.php");

$title = @$_GET['title'];
if ($title == "") $title=$index;

if (preg_match("|^[a-zA-Z]+://|", $title) >= 1) {
    // This is a link
    header("Location: " . $title);
    exit();
}

if (substr($title, 0, 1) == ":") {
    // This is also a link
    header("Location: " . substr($title, 1));
    exit();
}

$title = str_replace(array("/", "\\", ".."), "", $title); // Don't allow directory traversal

// If it's history, display that
//
$hist = @$_GET['hist'];
if ($hist) {
    $title = str_replace("TITLE", "$hist", $historyof);
    $node = "";
    
    $history = getNodeHistoryList($hist);
    
    $revisions = array();
    foreach ($history as $histe) {
        $cur = "<a href='edit.php?title=" . $hist . "&amp;hist=" . $histe["datestamp"] . "'>";
        $cur .= date("Y-m-d h:i:sA", $histe["datestamp"]);
        if ($histe["user"] !== false) {
            $cur .= " (" . $histe["user"] . ")";
        }
        $cur .= "</a><br>\n";
        
        $revisions[] = $cur;
    }
    
    rsort($revisions);
    foreach ($revisions as $rev) {
        $node .= $rev;
    }
    
    $bar=array("<a href='index.php'>$index</a>", "<a href='index.php?title=$hist'>$current</a>");
} else {
    // Read node data.
    //
    
    $bar=array("<a href='index.php'>$index</a>", "<a href='list.php'>$all</a>",
               "<a href='edit.php?title=$title'>$edit</a>", "<a href='index.php?hist=$title'>$history</a>");
    $node = parse($title);
    
    if (@$_COOKIE['wikiticket']) $bar[]="<a href='login.php?logging_out=1&amp;title=$title'>$logout</a>";
    else $bar[]="<a href='login.php?title=$title'>$login</a>";
}

display($title, $node, $bar, $template); 
?>
