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
require_once("display.php");
require_once("language.php");
require_once("logininc.php");

$user = @$_POST['user'];
$pass = @$_POST['pass'];
$passb = @$_POST['passb'];

if (!$login_plugin) {
    if ($user && $pass) {
        $node = "";
        
        if ($pass != $passb) {
            $title = $adduser;
            $node = $pass_dont_match;
        } else {
            // load registration plugins
            foreach ($plugins as $plugin) {
                @include("gikiplugin-reg-$plugin.php");
            }
        
            $file=fopen($nodedir."logins.txt", "a+");
            fputs($file, $user."\n");
            fclose($file);

            $file=fopen($nodedir."passwords.txt", "a+");
            fputs($file, md5($pass)."\n");
            fclose($file);
    
            if ($remotelog === true) {
                // remotelog.txt - record this new user
                $datelogged = date( "Y-m-d H:i:s", time() );
                $remoteip = $_SERVER[ "REMOTE_ADDR" ];
                $userstring = $datelogged . " - " .  $user . " - " . $remoteip;
                $fp = fopen( $nodedir."remotelog.txt", "a" );
                fputs( $fp, $userstring."\n" );
                fclose( $fp );
            }
    
            $title = $useradded;
            $node .= $useradded_d;
        }
    } else {
        $title = $adduser;
        $node  = '<form action="adduser.php" method="post">' .
            '<table border=0>' .
            '<tr><td align=right>' . $tusername . ':</td>' .
            '<td><input type="text" name="user" value=""></td></tr>' .
            
            '<tr><td align=right>' . $tpassword . ':</td>' .
            '<td><input type="password" name="pass" value=""></td></tr>' .
            
            '<tr><td align=right>' . $trepeatpw . ':</td>' .
            '<td><input type="password" name="passb" value=""></td></tr>' .
            
            '</table>' .
            '<input type="submit" name="submit" value="Submit"></form>';
        
        // load registration plugins
        foreach ($plugins as $plugin) {
            @include("gikiplugin-reg-$plugin.php");
        }
    }	
} else {
    // Using a login plugin, so this page won't work
    $title=$adduser;
    $node=$no_registration;
}

$bar=array("<a href='index.php?title=$index'>$index</a>");

display($title, $node, $bar, $template); 
?>
