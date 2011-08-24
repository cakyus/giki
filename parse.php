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

require_once("db.php");

$defaultLinkmethod = "<a href=\"index.php?title=TITLE\">TEXT</a>";

function parsecontents($title,
                       $node,
                       $linkmethod = "<a href=\"?title=TITLE\">TEXT</a>",
                       $wrevhist = true
                       ) {
    global $plugins;
    $nodetitle = $title;
    
    // Load render plugins
    for ($i = 0; isset($plugins[$i]); $i++) {
        $plugin = $plugins[$i];
        @include("gikiplugin-render-$plugin.php");
    }
    
    // Now links
    $onode = "";
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
        } else if ($char == "[" && !$inpre && !$inscript) {
            // find the rest of this link
            for ($j = $i + 1; $j < strlen($node) && substr($node, $j, 1) != "]"; $j++);
                
            // $link is set to the contents
            $link = substr($node, $i, $j - $i + 1);
            $i = $j;
                
            // this could be a link proper or an image
            if (substr($link, 0, 7) == "[image:") {
                // put in the image code
                $imgl = preg_replace(
                    '/\[image:([^\]]+(\.png|\.jpg|\.jpeg|\.gif))\]/',
                    '\1', $link);
                $link = '<img src="' . urlencode($imgl) . '">';
            } else {
                // get our title and text
                $match = array (
                    '/\[(([^\]\|]+))\]/',
                    '/\[([^\]\|]+)\|([^\]]+)\]/');
                $title = preg_replace($match, '\2', $link);
                $text = preg_replace($match, '\1', $link);
                
                // now $link gets replaced with a real link
                $link = str_replace (
                    array("TITLE",
                          "TEXT"),
                    array(urlencode($title),
                          $text),
                    stripslashes($linkmethod));
            }
                
            $onode .= $link;
        } else {
            $onode .= $char;
        }
    }
    return $onode;
}
    
function parse($title,
               $linkmethod = "<a href=\"?title=TITLE\">TEXT</a>",
               $wrevhist = true
               ) {
    global $empty, $nodedir, $plugins, $revhistory;
    
    if (nodeExists($title)) {
        $contents = getNodeContents($title);
        if ($contents === false) die("Failed to read node contents!");
        
        $node = $contents["text"];
        
        $node = parsecontents($title, $node, $linkmethod, $wrevhist);
        
        // only show revision history if requested
        if ($wrevhist) {
            if (isset($contents["revhistory"][0])) {
                $node.="<br><br>$revhistory";
                
                foreach ($contents["revhistory"] as $rev) {
                    $node .= "<br>" . $rev;
                }
            }
        }
    } else {
        if (file_exists($nodedir . $title) &&
            strtolower(substr($title, -4)) != ".txt") {
            // output it as a file
            if (substr($title, -5) == ".hist") {
                header("Content-Type: text/plain");
            } else {
                header("Content-Type: application/octet-stream");
            }
            header("Content-Disposition: attachment; filename=\"$title\"");
            readfile($nodedir . $title);
            die();
        } else {
            $node = $empty;
            
            // Load render plugins
            foreach ($plugins as $plugin) {
                @include("gikiplugin-render-$plugin.php");
            }
        }
    }
    
    return $node;
}

function subnodeParse($titles) {
    global $defaultLinkmethod;
    
    $node  = "<div align='right' style='height: 0px; overflow: visible'>";
    $node .= "<a href='edit.php?title=" . $titles[1] . "'>E</a>&nbsp;&nbsp;";
    $node .= "</div><br>";
    $node .= parse($titles[1], $defaultLinkmethod, false);
    return $node;
}
?>
