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

class FeedParser {
    var $encoding;
    var $datetime;
    var $title;
    var $items;
    
    function FeedParser($data,$enc=''){
		global $conf;
        if($enc){
            $this->encoding = strtoupper($enc);
        }else{
            $this->encoding = 'UTF-8';
        }
        $this->items = array();

        // set encoding
        if(preg_match('!encoding=[\'\"](.*?)[\'\"]!si', $data, $match)){
            $enc = strtoupper(trim($match[1]));
            if($enc) $this->encoding = $enc;
        }

        // get channeldata
        $cdata = $this->_getOneOf($data,'channel','feed');

        // set title
        $this->title = $this->_encode($this->_getOneOf($cdata,'title'));

        // set date
        $date = $this->_getOneOf($cdata,'lastBuildDate','modified','issued','created');
        if($date)       $date = $this->_parseDate($date);
        if(!$date || $date < 0)  $date = time();
        $this->datetime = $date;

        
        // get itemdata
        $idata = $this->_getAllOf($data,'item','entry');
        foreach($idata as $item){
            $this->_parseItem($item);
        }
    }


    function _parseItem($idata){
        $item = array();

        // get basics
        $item['title'] = $this->_encode($this->_getOneOf($idata,'title'));
        $item['body']  = $this->_encode($this->_getOneOf($idata,'summary','description'));

        // fetch link
        $item['link']  = $this->_getOneOf($idata,'link');
        if(!$item['link']){
            $item['link']  = $this->_getAttribute($idata,'link','href');
        }
        $item['link'] = $this->_encode($item['link']);
		
        // fetch date
        $date = $this->_getOneOf($idata,'pubDate','modified','issued','created','date','dc:date');
        if($date) $date = $this->_parseDate($date);
        if(!$date || $date < 0) $date = $this->datetime; 
        if($date) $item['datetime'] = $date;

        $this->items[] = $item;
    }


    function _encode($string){
        if($this->encoding != 'UTF-8'){
            $string = @iconv($this->encoding,'UTF-8',$string);
        }
        if(substr($string,0,9) == '<![CDATA['){
            $string = substr($string,9,-3);
            $string = $this->decode_entities($string,array('>','<'));
        }else{
            $string = $this->decode_entities($string);
        }
        return $string;
    }

    function _parseDate($dtstr){
        if(strlen($dtstr) == 10 && is_numeric($dtstr)){
            return $dtstr; // already unix timestamp;
        }elseif(preg_match('!(\d\d\d\d-\d\d-\d\d)T(\d\d:\d\d:\d\d)([+\-]\d\d):(\d\d)!',
                           $dtstr,$match)){
            return strtotime( $match[1].' '.$match[2].' '.$match[3].$match[4]);
        }elseif(preg_match('!(\d\d\d\d-\d\d-\d\d)T(\d\d:\d\d:\d\d)(.*)!',
                           $dtstr,$match)){
            return strtotime( $match[1].' '.$match[2].' '.$match[3]);
        }else{
            return strtotime($dtstr);
        }
    }

    /**
     *
     * @param  string $data    Data to parse
     * @param  string $tag,... Tags to try
     * @return array all found items
     */
    function _getAllOf(){
        $tags = func_get_args();
        if(count($tags) < 2) die('Bad args for _getOneOf');
        $data = array_shift($tags);

        $found = array();
        foreach($tags as $tag){
            if(preg_match_all('!<'.$tag.'(| .*?)>(.*?)</'.$tag.'>!si',
                              $data,$matches,PREG_SET_ORDER)){
                foreach($matches as $match){
                    $content = trim($match[2]);

                    // nothing in it try next
                    if($content == '') continue;

                    $found[] = $content;
                }
            }
        }
        return $found;
    }

    function _getAttribute($data,$tag,$attr){
        if(preg_match_all('!<'.$tag.' [^>]*?'.$attr.'=[\'\"](.*?)[\'\"][^>]*?(/>|</'.$tag.'>)!si',
                      $data,$matches,PREG_SET_ORDER)){
            foreach($matches as $match){
                //hack to ignore blogger.com edit links
                if(preg_match('!rel="service.edit"!',$match[0])){
                    continue;
                }
            
                $content = trim($match[1]);
                // nothing in it try next
                if($content == '') continue;
                return $content;
            }
        }
        return null;
    }


    /**
     *
     * @param string $data    Data to parse
     * @param string $tag,... Tags to try
     */
    function _getOneOf(){
        $tags = func_get_args();
        if(count($tags) < 2) die('Bad args for _getOneOf');
        $data = array_shift($tags);

        foreach($tags as $tag){
            if(preg_match_all('!<'.$tag.'(| .*?)>(.*?)</'.$tag.'>!si',$data,$matches,PREG_SET_ORDER)){
                foreach($matches as $match){
                    $content = trim($match[2]);

                    // nothing in it try next
                    if($content == '') continue;

                    return $content;
                }
            }
        }
        return null;
    }


    /**
    * Decode all HTML entities (including numerical ones) to regular UTF-8 bytes.
    * Double-escaped entities will only be decoded once ("&amp;lt;" becomes "&lt;", not "<").
    *
    * @param $text
    *   The text to decode entities in.
    * @param $exclude
    *   An array of characters which should not be decoded. For example,
    *   array('<', '&', '"'). This affects both named and numerical entities.
    */
    function decode_entities($text, $exclude = array()) {
      static $table;
      // We store named entities in a table for quick processing.
      if (!isset($table)) {
        // Get all named HTML entities.
        $table = array_flip(get_html_translation_table(HTML_ENTITIES));
        // PHP gives us ISO-8859-1 data, we need UTF-8.
        $table = array_map('utf8_encode', $table);
        // Add apostrophe (XML)
        $table['&apos;'] = "'";
      }
      $newtable = array_diff($table, $exclude);
      // Use a regexp to select all entities in one pass, to avoid decoding double-escaped entities twice.
      return preg_replace('/&(#x?)?([A-Za-z0-9]+);/e', '$this->_decode_entities("$1", "$2", "$0", $newtable, $exclude)', $text);
    }

    /**
    * Helper function for decode_entities
    */
    function _decode_entities($prefix, $codepoint, $original, &$table, &$exclude) {
      // Named entity
      if (!$prefix) {
        if (isset($table[$original])) {
          return $table[$original];
        }
        else {
          return $original;
        }
      }
      // Hexadecimal numerical entity
      if ($prefix == '#x') {
        $codepoint = base_convert($codepoint, 16, 10);
      }
      // Encode codepoint as UTF-8 bytes
      if ($codepoint < 0x80) {
        $str = chr($codepoint);
      }
      else if ($codepoint < 0x800) {
        $str = chr(0xC0 | ($codepoint >> 6))
             . chr(0x80 | ($codepoint & 0x3F));
      }
      else if ($codepoint < 0x10000) {
        $str = chr(0xE0 | ( $codepoint >> 12))
             . chr(0x80 | (($codepoint >> 6) & 0x3F))
             . chr(0x80 | ( $codepoint       & 0x3F));
      }
      else if ($codepoint < 0x200000) {
        $str = chr(0xF0 | ( $codepoint >> 18))
             . chr(0x80 | (($codepoint >> 12) & 0x3F))
             . chr(0x80 | (($codepoint >> 6)  & 0x3F))
             . chr(0x80 | ( $codepoint        & 0x3F));
      }
      // Check for excluded characters
      if (in_array($str, $exclude)) {
        return $original;
      }
      else {
        return $str;
      }
    }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :
