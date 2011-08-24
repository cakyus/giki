<?PHP
/*
 *  Giki - a flat-file PHP wikis
 *  Copyright (C) 2005, 2006  Gregor Richards
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software
 * and associated documentation files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or
 * substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING
 * BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

require_once("language.php");

/*
 * Use:
 * $node = stdGikiParse('name', 'callback', $node);
 * Where 'name' is the name of your Giki-style new syntax element
 * and 'callback' is the function to call for each block of type 'name', which should return a
 * string.
 */

function stdGikiParse($name, $callback, $node) {
    $nelems = explode("{" . $name . "{", $node, 2);
    if (isset($nelems[1])) {
        if (strpos($nelems[1], "{" . $name . "{") !== false) {
            $nelems[1] = stdGikiParse($name, $callback, $nelems[1]);
        }
                
        $parsable = explode("}" . $name . "}", $nelems[1], 2);
        $parsable[0] = call_user_func($callback, $parsable[0]);
                
        return $nelems[0] . $parsable[0] . $parsable[1];
    } else {
        return $nelems[0];
    }
}

/* Use:
 * $text = array("en" => "Text in English",
 *               "es" => "Text in Español",
 *               (etc)
 *              );
 * $transtxt = stdGikiTranslate($text);
 */
function stdGikiTranslate($text) {
    global $language;
    
    if (isset($text[$language])) return $text[$language];
    return $text["en"];
}
?>
