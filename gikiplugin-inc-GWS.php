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

// find the nearest bracket
function findBracket($string, $from) {
    $braca = strpos($string, "<", $from);
    $bracb = strpos($string, "[", $from);
    if ($braca !== false && $bracb !== false) {
        if ($braca < $bracb) return $braca;
        return $bracb;
    }
    if ($braca !== false) return $braca;
    return $bracb;
}

// Replace out-of-HTML tags (leave HTML tags alone, preg_replace elsewhere)
function replaceOOHTML($a, $b, $c)
{
    $output = "";
    $loc = 0;
    $brac = 0;
    $brac2 = 0;
    while (($brac = findBracket($c, $loc)) !== false) {
        $output .= preg_replace($a, $b, substr($c, $loc, $brac - $loc));
        
        /* if this is <pre, ignore more */
        if (strtolower(substr($c, $brac, 4)) == "<pre") {
            $brac2 = strpos($c, ">", $brac);
            $brac2 = strpos($c, "/pre>", $brac2);
            if ($brac2 === false) {
                // this pre doesn't close
                $brac2 = strlen($c) - 1;
            } else { $brac2 += 4; }
            // strip <br>s from the pre
            $output .= str_replace("<br>", "", substr($c, $brac, $brac2 - $brac + 1));
        } else {
            if (substr($c, $brac, 1) == "<") {
                $brac2 = strpos($c, ">", $brac);
            } else {
                $brac2 = strpos($c, "]", $brac);
            }
            // if we just did a header and this is <br>, ignore it
            $next = substr($c, $brac, $brac2 - $brac + 1);
            if (!preg_match("|</h.>$|", $output) || $next != "<br>")
                $output .= $next;
        }
        $loc = $brac2 + 1;
    }
    $output .= preg_replace($a, $b, substr($c, $loc));
    return $output;
}

function parselists($onode, $parsefor, $ltype) {
    $lines = explode("\n", $onode);
    $lines[] = "";
    $olines = array();
    $ll = 0;
    $inpre = false;
    foreach ($lines as $line) {
        if (strpos($line, "<pre") !== false) {
            $inpre = true;
        }
        if (strpos($line, "</pre>") !== false) {
            $inpre = false;
        }
        
        if (!$inpre) {
            if (preg_match('|^\s*' . $parsefor . '+|', $line)) {
                $newull = strlen(preg_replace('|^\s*(' . $parsefor . '+).*|', '\1', $line));
                
                if ($newull == 2 && $parsefor == "#") {
                    // special case, don't interfere with bolding
                    $boldc = preg_match_all("|[^#]##[^#]|", $line, $matches);
                    if ($boldc % 2 == 1) {
                        // even number of ##'s, it's bolding
                        while ($ll > 0) {
                            $olines[] = "</$ltype>";
                            $ll--;
                        }
                        $olines[] = $line;
                        continue;
                    }
                }
                
                while ($ll > $newull) {
                    $olines[] = "</$ltype>";
                    $ll--;
                }
                while ($ll < $newull) {
                    $olines[] = "<$ltype>";
                    $ll++;
                }
                $olines[] = preg_replace('|\s*' . $parsefor . '+\s*(.*?)(<br>)?$|', '<li>\1</li>', $line);
            } else {
                while ($ll > 0) {
                    $olines[] = "</$ltype>";
                    $ll--;
                }
                $olines[] = $line;
            }
        } else {
            $olines[] = $line;
        }
    }
    return implode("\n", $olines);
}
?>
