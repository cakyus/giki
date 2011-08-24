<?php
/*
 *  Giki - a flat-file PHP wiki
 *  Copyright (C) 2003  Sam Thursfield
 *  Copyright (C) 2005  Gregor Richards
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

$title=$nodelist;
$node="";

// Read and sort node data.
//
if ($dir = opendir($nodedir)) {
    $filelist = array();
    while ($file = readdir($dir)) {
        if (strstr($file, ".wiki")) {
            $file = substr($file, 0, strlen($file)-5);
            $filelist[] = $file;
        }
    }
    natcasesort($filelist);
    foreach($filelist as $file) {
        $node.="<a href=\"index.php?title=$file\">$file</a><br>";
    }
    closedir($dir);
}

$bar = array("<a href='index.php?title=$index'>$index</a>");
display($title, $node, $bar, $template); 
?>
