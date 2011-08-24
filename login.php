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
$logging_out = @$_GET['logging_out'];
$user = stripslashes(@$_POST['user']);
$pass = stripslashes(@$_POST['pass']);

$t=$title;	// Preserve the last node.

if ($logging_out) {		

    // Log out
    //
    // Verify the login before messing with keys
    //
    if ($_COOKIE['wikiticket']) {
        if (!verifyuser(stripslashes($_COOKIE['wikiticket']))) {
            // invalid login!
            //
            setcookie("wikiticket", "", time());
            invalidlogin();
        }
    }
        
    $elems = explode(":::", stripslashes($_COOKIE['wikiticket']));
    $user = $elems[0];
    setcookie("wikiticket", "", time());
    logoutuser($user);
    $title=$loggedout;
    $node=$loggedout_d;
    $bar=array("<a href='index.php?title=$t'>$return</a>", "<a href='login.php?title=$t'>$login</a>");

} else if ($pass) {
    
    if ($remotelog === true) {
        // remotelog.txt - record this login attempt
        $datelogged = date( "Y-m-d H:i:s", time() );
        $remoteip = $_SERVER[ "REMOTE_ADDR" ];
        $userstring = $datelogged . " - " .  $user . " - " . $remoteip;
        $fp = fopen( $nodedir."remotelog.txt", "a" );
        fputs( $fp, $userstring."\n" );
        fclose( $fp );
    }
    
    // Log in
    //
    
    if (!checkpass($user, $pass)) {
        $title=$invpass;
        $node=$invpass_d;
        $bar=array("<a href='login.php?title=$t'>$login</a>", "<a href='index.php?title=$t'>$return</a>");
    } else {						
		
        // Log user in for a day
        //
        
        $usercookie = mkusercookie($user, $pass);
        setcookie("wikiticket", $usercookie, time()+86400);

        $title=$loggedin.$user;
        $node=$loggedin_d;
        $bar=array("<a href='index.php?title=$t'>$return</a>", "<a href='login.php?logging_out=1&amp;title=$t'>$logout</a>");
    }

} else {
    $title=$login;
    
    $node = '<form method="post" action="login.php">' .
        '<table border=0>' .
        
        '<tr><td align=right>' . $tusername . ':</td>' .
        '<td><input name="user" type="text"></td></tr>' .
        
        '<tr><td align=right>' . $tpassword . ':</td>' .
        '<td><input name="pass" type="password"></td></tr>' .
        
        '</table>' .
        '<input type="hidden" name="title" value="' . $t . '">' .
        '<input type="submit" value="' . $login . '">' .
        '</form>';
    $node.=$cookies;
    $bar=array("<a href='index.php?title=$t'>$return</a>");
}

display($title, $node, $bar, $template);
?>
