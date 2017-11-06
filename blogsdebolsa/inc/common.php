<?php

/* 
lylina news aggregator

Copyright (C) 2005 Andreas Gohr
Copyright (C) 2006 Eric Harmon

lylina is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

lylina is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with lylina; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

$version = "1.11";

/**
 * Executes an SQL query
 *
 * @returns two-dim array on select, insertID on insert 
 */
 
function runSQL($sql_string) {
    global $conf;
    static $link = null;

    $resultarray= array();

    if(!$link){
        $link = mysql_connect ($conf['db_host'], $conf['db_user'], $conf['db_pass']) or
                die("DB Connection Error");
    }

    $result = mysql_db_query($conf['db_database'],$sql_string,$link) or
              die('Database Problem: '.mysql_error($link)."\n<br />\n".$sql_string);

    //mysql_db_query returns 1 on a insert statement -> no need to ask for results
    if ($result != 1) {
        for($i=0; $i< mysql_num_rows($result); $i++) {
            $temparray = mysql_fetch_assoc($result);
            $resultarray[]=$temparray;
        }
        mysql_free_result ($result);
    }elseif($result == 1 && mysql_insert_id($link)) {
        $resultarray = mysql_insert_id($link); //give back ID on insert
    }
    return $resultarray;
}

/**
 * Build an string of URL parameters
 *
 * Handles URL encoding
 */
function buildURLparams($params,$sep='&amp;'){
  $url = '';
  $amp = false;
  foreach($params as $key => $val){
    if($amp) $url .= $sep;

    $url .= $key.'=';
    $url .= urlencode($val);
    $amp = true;
  }
  return $url;
}

/**
 * Build an string of html tag attributes
 *
 * Handles html encoding
 */
function buildAttributes($params){
  $url = '';
  foreach($params as $key => $val){
    $url .= $key.'="';
    $url .= htmlspecialchars ($val);
    $url .= '" ';
  }
  return $url;
}

/**
 * Helper to print a variable's content - just for debugging
 */
function dbg($val){
    print '<pre class="cms debug">';
    print_r($val);
    print '</pre>';
}

// http://www.php.net/manual/en/function.html-entity-decode.php
function unhtmlentities($string){
   // replace numeric entities
   $string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
   $string = preg_replace('~&#([0-9]+);~e', 'chr(\\1)', $string);
   // replace literal entities
   $trans_tbl = get_html_translation_table(HTML_ENTITIES);
   $trans_tbl = array_flip($trans_tbl);
   $trans_tbl['&apos;'] = "'";
   return strtr($string, $trans_tbl);
}

/**
 * print a message
 *
 * If HTTP headers were not sent yet the message is added 
 * to the global message array else it's printed directly
 * using html_msgarea()
 * 
 *
 * Levels can be: 
 *  
 * -1 error
 *  0 info
 *  1 success
 *  
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    html_msgarea
 */ 
function msg($message,$lvl=0){
  global $MSG;
  $errors[-1] = 'error';
  $errors[0]  = 'info';
  $errors[1]  = 'success';

  if(!headers_sent()){
    if(!isset($MSG)) $MSG = array();
    $MSG[]=array('lvl' => $errors[$lvl], 'msg' => $message);
  }else{
    $MSG = array();
    $MSG[]=array('lvl' => $errors[$lvl], 'msg' => $message);
    if(function_exists('html_msgarea')){
      html_msgarea();
    }else{
      print "ERROR($lvl) $message";
    }
  }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
