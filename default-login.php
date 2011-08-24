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

// Returns the index of a given user
//
function fseekuser($file, $user) {
    $str="";
    $i=0;
    while ($user."\n"!=$str) { 
        $str=fgets($file, 99); 
        if (!$str) {$user=""; break;}
        $i++; 				
    }
    return $i;
}
    
function userkey($user) {
    global $nodedir;
    
    // create a key if the user doesn't have one, return it if the user does
    $handle = @fopen($nodedir."keys.txt","r");
    if ($handle === false) {
        $handle = fopen($nodedir."keys.txt","w");
        fclose($handle);
        $handle = fopen($nodedir."keys.txt","r");
    }
    flock($handle, LOCK_SH);
    
    while (!feof($handle)) {
        $line = fgets($handle);
        $line = rtrim($line);
        $elems = explode(" ", $line);
        if ($elems[0] == $user) {
            fclose($handle);
            return $elems[1];
        }
    }
    fclose($handle);
    
    // if we didn't find one, make one
    $handle = fopen($nodedir."keys.txt","a");
    flock($handle, LOCK_EX);
    $key = rand(1000000, 9999999);
    fwrite($handle, "$user $key\n");
    fclose($handle);
    return $key;
}

function logoutuser($user) {
    global $nodedir;
    
    $lines = file($nodedir."keys.txt");
    $handle = fopen($nodedir."keys.txt","w");
    flock($handle, LOCK_EX);
    foreach ($lines as $line) {
        $line = rtrim($line);
        $elems = explode(" ", $line);
        if ($elems[0] != $user) {
            fwrite($handle, "$line\n");
        }
    }
    fclose($handle);
}

function verifyuser($key) {
    global $nodedir;
    
    if ($key == "") return true;
    
    // Key is user:::password, split it
    $elems = explode(":::", $key);
    $user = $elems[0];
    $pass = $elems[1];
    
    // Get user number
    //
    $file=@fopen($nodedir."logins.txt", "r");
    if ($file === false) return false;
    $index=fseekuser($file, $user);
    fclose($file);
    
    // Check against password
    //
    
    $file=file($nodedir."passwords.txt");
    $str=$file[$index-1];
    
    $str = rtrim($str);
    if ($pass!=md5($str.userkey($user))) {
        return false;
    } else {						
        return true;
    }
}

function checkpass($user, $pass) {
    global $nodedir;
    
    // Get user number
    //
    $file=@fopen($nodedir."logins.txt", "r");
    if ($file === false) return false;
    $index=fseekuser($file, $user);
    fclose($file);
    
    // Check against password
    //
    $file=file($nodedir."passwords.txt");
    $str=$file[$index-1];
    
    $str = rtrim($str);
    if (md5($pass) != $str) {
        return false;
    } else {
        return true;
    }
}

function invalidlogin() {
    global $template, $invpass, $invpass_d, $t, $login, $return;
    $title=$invpass;
    $node=$invpass_d;
    $bar=array("<a href='login.php?title=$t'>$login</a>", "<a href='index.php?title=$t'>$return</a>");
    display($title, $node, $bar, $template);
    die();
}

function mkusercookie($user, $pass) {
    return $user . ":::" . md5(md5($pass) . userkey($user));
}
?>
