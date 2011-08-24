<?php
/*
 *  Giki - a flat-file PHP wiki
 *  Copyright (C) 2006  Gregor Richards
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

/* returns true if the title is bad */
function blacklistTitle($title) {
    // blacklist the title
    if (strpos($title, "/") !== false ||
        strpos($title, "\\") !== false ||
        strpos($title, "..") !== false) return true;
    return false;
}

function nodeExists($title) {
    global $nodedir;
    
    // blacklist the title
    if (blacklistTitle($title)) return false;
    
    return file_exists($nodedir.$title.".wiki");
}

/* returns an array:
 * "text"       => text of the node
 * "revhistory" => revision history array:
 *                 0 => history string
 *                 1 => ...
 */
function getNodeContents($title) {
    global $nodedir;
    if (!nodeExists($title)) return false;
    
    // open the file
    $file = fopen($nodedir.$title.".wiki", "r");
    if ($file === false) return false;
    
    // and lock it
    flock($file, LOCK_SH);
    
    // first line is the content
    $text = fgets($file, 65536);
    $text = rtrim($text);
    
    // it uses ^NEWLINE^ to signify newlines
    $text = str_replace("^NEWLINE^", "\n", $text);
    
    // the revision history is stored line-by-line
    $revhistory = array();
    while (!feof($file)) {
        $ln = fgets($file, 65536);
        if ($ln !== false) {
            $ln = rtrim($ln);
            $revhistory[] = $ln;
        }
    }
    
    // finish
    flock($file, LOCK_UN);
    fclose($file);
    
    return array(
        "text"       => $text,
        "revhistory" => $revhistory
        );
}

/* takes the same format as getNodeContents returns */
function putNodeContents($title, $contents, $user) {
    global $nodedir;
    
    // blacklist the title
    if (blacklistTitle($title)) return false;
    
    // open the files
    $file = fopen($nodedir.$title.".wiki", "w");
    $histfile = fopen($nodedir . $title . "." . time() . ".hist", "w");
    
    if ($file === false || $histfile === false) return false;
    
    // and lock them
    flock($file, LOCK_EX);
    flock($histfile, LOCK_EX);
    
    // convert the content
    $text = str_replace("\n", "^NEWLINE^", $contents["text"]);
    
    // write it out
    fputs($file, $text . "\n");
    fputs($histfile, $text . "\n" . $user . "\n");
    
    // also write revision history
    foreach ($contents["revhistory"] as $hist) {
        fputs($file, $hist . "\n");
    }
    
    // finish up
    flock($file, LOCK_UN);
    flock($histfile, LOCK_UN);
    fclose($file);
    fclose($histfile);
}

/* get a list of historical versions of a node
 * returns an array:
 * datestamp => array:
 *              "datestamp" => unix timestamp date
 *              "user"      => the user who made the modification or false if
 *                             unavailable
 * datestamp => ...
 */
function getNodeHistoryList($title) {
    global $nodedir;
    
    // blacklist the title
    if (blacklistTitle($title)) return false;
    
    $hl = array();
    
    $handle = opendir($nodedir);
    while ($entry = readdir($handle)) {
        if (substr($entry, -5) == ".hist" &&
            substr($entry, 0, strlen($title) + 1) == "$title.") {
            // Check for whodunit
            $who = false;
            $fhandle = fopen($nodedir.$entry, "r");
            if ($fhandle !== false) {
                $junk = fgets($fhandle, 65536); // first line is content
                if (!feof($fhandle)) $who = rtrim(fgets($fhandle, 65536));
                fclose($fhandle);
            }
            
            $elems = explode(".", $entry);
            
            $datestamp = $elems[count($elems)-2];
            $hl[intval($datestamp)] = array(
                "datestamp" => $datestamp,
                "user"      => $who
                );
        }
    }
    closedir($handle);
    
    return $hl;
}

/* get a history element for a node, returns the same format as
 * getNodeContents */
function getNodeHistory($title, $stamp) {
    global $nodedir;
    
    // blacklist the title
    if (blacklistTitle($title)) return false;
    
    $fname = $nodedir . $title . "." . $stamp . ".hist";
    
    if (!file_exists($fname)) return false;
    
    $file = fopen($fname, "r");
    if ($file === false) return false;
    
    $text = fgets($file, 65536);
    $text = str_replace("^NEWLINE^", "\n", $text);
    
    fclose($file);
    
    return array("text" => $text, "revhistory" => array());
}
?>
