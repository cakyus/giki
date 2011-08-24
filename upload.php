<?PHP
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
require_once("logininc.php");

$title = $upload;
$bar = array("<a href='index.php?title=$index'>$index</a>");

if ($allow_uploads == false) {
    $node = $no_uploads;
    display($title, $node, $bar, $template);
    die();
}

// Check the password
//
if ($_COOKIE['wikiticket']) {
    if (!verifyuser(stripslashes($_COOKIE['wikiticket']))) {
        // invalid login!
        //
        invalidlogin();
    } else {
        $up=explode(":::", stripslashes($_COOKIE['wikiticket']));
        $user=$up[0];
    }
} else {
    // guests can't upload
    $node = $upload_noguests;
    display($title, $node, $bar, $template);
    die();
}

if (isset($HTTP_POST_FILES['userfile'])) {
    // Archive info
    $archive_name = $HTTP_POST_FILES['userfile']['name'];
    $archive_lname = strtolower($archive_name);
    $archive_size = $HTTP_POST_FILES['userfile']['size'];
    
    // Check the file type
    if ((substr($archive_lname, -4) != ".tgz" &&
         substr($archive_lname, -7) != ".tar.gz" &&
         substr($archive_lname, -8) != ".tar.bz2" &&
         substr($archive_lname, -4) != ".zip" &&
         substr($archive_lname, -4) != ".jpg" &&
         substr($archive_lname, -5) != ".jpeg" &&
         substr($archive_lname, -4) != ".png" &&
         substr($archive_lname, -4) != ".gif") ||
        $archive_size >= 90 * 1024) {
        $node = $upload_invalid;
    } else{
        if (move_uploaded_file($HTTP_POST_FILES['userfile']['tmp_name'], $nodedir . $user . "-" . $archive_lname)){
            $node = $upload_ok . "$user-$archive_lname";
        }else{
            $node = $upload_error;
        }
    }
} else {
    $node = "<form action=\"upload.php\" method=post enctype=\"multipart/form-data\">";
    $node .= "$upload_file: <input name=\"userfile\" type=\"file\"><br>";
    $node .= "<input type=\"submit\" value=\"Upload\">";
    $node .= "</form>";
}

display($title, $node, $bar, $template);
?> 
