<?PHP
/*
 *  GWS GikiPlugin - a plugin for Giki to support WikiSyntax
 *  Copyright (C) 2005, 2006  Gregor Richards
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/*
 * This file just adds some text to the edit page to briefly describe how to use wikisyntax
 */


/*
 * see if we can get subwindows
 */
$realnode = $node;
$node = "";
if (!isset($subwins)) {
    if (file_exists("gikiplugin-render-subwins.php")) {
        include("gikiplugin-render-subwins.php");
    } else {
        $subwins = false;
    }
}
$node .= $realnode;

$help = "Content in {{ and }} will be displayed unedited:<br>" .
    "{{<br>" .
    "## //This will appear exactly as you see it//<br>" .
    "}}<br><br>" .
    "URLs will become links automatically.<br>" .
    "<i>//Italic//</i><br>" .
    "<b>##Bold##</b><br>" .
    "__Underline__<br>" .
    "<h1>= Largest heading =</h1><br>" .
    "<h4>==== Smallest heading ====</h1><br>" .
    
    "<table border=1><tr><th>Input</th><th>Output</th></tr>" .
    
    "<tr><td>* bulleted list<br>" .
    "** with subelements</td>" .
    "<td><ul><li>bulleted list</li>" .
    "<ul><li>with subelements</li></ul></ul></td></tr>" .
    
    "<tr><td># numbered list<br>" .
    "## with subelements</td>" .
    "<td><ol><li>numbered list</li>" .
    "<ol><li>with subelements</li></ol></ol></td></tr>" .
    
    "<tr><td>|&nbsp;Tables&nbsp;|&nbsp;Like&nbsp;|&nbsp;So&nbsp;|<br>" .
    "|-2&nbsp;This&nbsp;spans&nbsp;both&nbsp;'Tables'&nbsp;and&nbsp;'Like'&nbsp;|&nbsp;This&nbsp;is&nbsp;under&nbsp;'So'&nbsp;|<br>" .
    "|!2&nbsp;This&nbsp;will&nbsp;span&nbsp;two&nbsp;rows&nbsp;|&nbsp;This&nbsp;is&nbsp;next&nbsp;to&nbsp;the&nbsp;row-spanning&nbsp;element&nbsp;|<br>" .
    "|&nbsp;So&nbsp;is&nbsp;this&nbsp;|</td>" .
    "<td><table border=2><tr><td>Tables</td><td>Like</td><td>So</td></tr>" .
    "<tr><td colspan=2>This spans both 'Tables and 'Like'</td><td>This is under 'So'</td></tr>" .
    "<tr><td rowspan=2>This will span two rows</td><td>This is next to the row-spanning element</td></tr>" .
    "<tr><td>So is this</td></tr></table></tr>" .
    
    "</tr></table>";

if (!$subwins) {
    $node .= "<hr><h1>WikiSyntax:</h1>";
    $node .= $help;
} else {
    $node .= "<hr>";
    $node .= generateSubwin("WikiSyntax", "<h1>WikiSyntax</h1>", $help);
}
?>
