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
require_once("parse.php");

function display($title, $node, $bar, $fname) {
    global $bar_prefix, $bar_separator, $bar_suffix, $plugins;
    
    $file=fopen($fname, "r");
    $page="";
    while(!feof($file)) 
        $page.=fgets($file, filesize($fname));
    
    $page=str_replace('$title', $title, $page);
    $page=str_replace('$bar', $bar_prefix . implode($bar_separator, $bar) . $bar_suffix, $page);
    $page=str_replace('$plugins', "Plugins: " . implode(", ", $plugins), $page);
    
    $page=preg_replace_callback('/\$node\[([^\]]+)\]/', "subnodeParse", $page);
    $page=str_replace('$node', $node, $page);
    
    echo $page;
}
?>
