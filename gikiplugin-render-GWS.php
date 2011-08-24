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
 * This plugin changes GWS script into HTML.
 *
 * Supported markup:
 *
 * {{pre}}
 *
 * http://whatever/ -> link
 * ftp://whatever/ -> link
 *
 * //italic//
 * ##bold##
 * __underline__
 *
 * =heading1=
 * ==heading2 (etc)==
 *
 * * bulleted list
 * ** bulleted list subelement
 * # numbered list
 * ## numbered list subelement
 *
 * |table|row|
 * |!rowspan=2!With spans|
 */

require_once("gikiplugin-inc-GWS.php");

$onode = $node;

// 0) Parse for {{ ... }} = <pre></pre>
//
$onode = replaceOOHTML("|}}|", "</pre>", $onode);
$onode = replaceOOHTML("|{{|", "<pre>", $onode);

// 1) Parse for http:// and ftp:// links
//
$onode = replaceOOHTML(array("|(http://\\S+)|i", "|(ftp://\\S+)|i"), "<a href=\"\\1\">\\1</a>", $onode);

// 2) Parse for * and # lists
//
$onode = parselists($onode, "\\*", "ul");
$onode = parselists($onode, "#", "ol");

// 3) Parse for //, ##, __
//
$onode = replaceOOHTML(
    array("|//(.*?)//|",
          "|##(.*?)##|",
          "|__(.*?)__|"), 
    array("<i>\\1</i>",
          "<b>\\1</b>",
          "<u>\\1</u>"), $onode);

// 4) Parse for ==heading==
//
$onode = replaceOOHTML(
    array("|====(.*)====|",
          "|===(.*)===|",
          "|==(.*)==|",
          "|=(.*)=|"),
    array("<h4>\\1</h4>",
          "<h3>\\1</h3>",
          "<h2>\\1</h2>",
          "<h1>\\1</h1>"), $onode);

// 5) Parse for tables
//
$lines = explode("\n", $onode);
$olines = array();
$intable = false;
$inpre = false;
foreach ($lines as $line) {
    if (strpos($line, "<pre") !== false) {
        $inpre = true;
    }
    if (strpos($line, "</pre>") !== false) {
        $inpre = false;
    }
    
    if (!$inpre) {
        if (preg_match("/^\\s*\\|/", $line)) {
            if (!$intable) {
                $olines[] = "<table border=1>";
                $intable = true;
            }
            $olines[] = "<tr>";

            $elems = explode("|", $line);
           
            unset($elems[0]);
            unset($elems[count($elems)]);
            
            // since | is also used in links, we need to recombine broken links
            // in retrospect, I should have used a different character - too late :(
            $max = count($elems);
            for ($i = 0; $i < $max; $i++) {
                if (strpos($elems[$i], "[") !== false) {
                    for ($j = $i + 1; $j < $max; $j++) {
                        $elems[$i] .= "|" . $elems[$j];
                        if (strpos($elems[$j], "]") !== false) {
                            // we're done
                            unset($elems[$j]);
                            break;
                        }
                        unset($elems[$j]);
                    }
                }
            }

            foreach ($elems as $elem) {
                if (substr($elem, 0, 1) == "-" ||
                    substr($elem, 0, 1) == "!") {
                    $outline = "<td";
                
                    $tage = explode(" ", $elem, 2);
                    if (substr($tage[0], 0, 1) == "-") {
                        $tage[0] = substr($tage[0], 1);
                        $withs = explode("!", $tage[0]);
                        $outline .= " colspan=" . $withs[0];
                        if ($withs[1]) {
                            $outline .= " rowspan=" . $withs[1];
                        }
                    } else {
                        $tage[0] = substr($tage[0], 1);
                        $withs = explode("-", $tage[0]);
                        $outline .= " rowspan=" . $withs[0];
                        if ($withs[1]) {
                            $outline .= " colspan=" . $withs[1];
                        }
                    }
                
                    $outline .= ">" . $tage[1] . "</td>";
                
                    $olines[] = $outline;
                } else {
                    $olines[] = "<td>" . $elem . "</td>";
                }
            }
        
            $olines[] = "</tr>";
        } else {
            if ($intable) {
                $olines[] = "</table>";
                $intable = false;
            }
            $olines[] = $line;
        }
    } else {
        $olines[] = $line;
    }
}
$onode = implode("\n", $olines);
    
$node = $onode;
?>
