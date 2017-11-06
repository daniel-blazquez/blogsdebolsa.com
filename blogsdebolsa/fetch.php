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

if(!@mkdir('lockdir')){
    print 'ERROR: lockdir exists or isn\'t writable, please remove the folder \'lockdir\' in order to continue';
    exit;
}


if (!extension_loaded('mysql')){
    dl('mysql.so');
}

set_time_limit (0);
ignore_user_abort(true);

require_once('conf.php');

require_once('inc/common.php');
require_once('inc/HTTPClient.php');
require_once('inc/FeedParser.php');

$sql   = 'SELECT * FROM lylina_feeds';
$feeds = runSQL($sql);

foreach($feeds as $feed){
    if($conf['debug'] == 'true') print $feed['url'];
    $enc = '';
    $data = getFeed($feed,$enc);
    if($conf['debug'] == 'true') print "data: ".strlen($data)."\n";
    if($data){
        $rss = new FeedParser($data,$enc);
    if($conf['debug'] == 'true') print "parsed\n";
        updateFeedItems($feed,$rss);
    if($conf['debug'] == 'true') print "updated\n";
    } 
    if($conf['debug'] == 'true') print "\n";
}


rmdir('lockdir');

//---------------------------------

function updateFeedItems($feed,$rss){
    global $conf;
    if(empty($feed['name']) && !empty($rss->title)){
        $sql = "UPDATE lylina_feeds
                   SET name = '".addslashes($rss->title)."'
                 WHERE   id = '".addslashes($feed['id'])."'";
        runSQL($sql);
    }


    foreach($rss->items as $item){
        $sql = "INSERT IGNORE INTO lylina_items
                   SET feed_id = '".$feed['id']."',
                           url = '".addslashes($item['link'])."',
                         title = '".addslashes($item['title'])."',
                          body = '".addslashes($item['body'])."',
                            dt = FROM_UNIXTIME(".addslashes($item['datetime']).")";
        runSQL($sql);
        if($conf['debug'] == 'true') print ".";
        flush();
    }
}

function getFeed($feed,&$enc){
    $http = new DokuHTTPClient();
    $http->timeout = 45;

    // prepare conditional request
    if($feed['lastmod']){
        $http->headers['If-Modified-Since'] = $feed['lastmod'];
    }
    if($feed['etag']){
        $http->headers['If-None-Match'] = $feed['etag'];
    }

    if($conf['debug'] == 'true') print " sending request\n";
    // send request
    $http->sendRequest($feed['url']);
    if($conf['debug'] == 'true') print "sending request done\n";

    if($http->error){
        if($conf['debug'] == 'true') print "ERROR ".$http->error."\n";
    }

    // check status
    if($http->status != 200){
        return '';
    }

    // save headers
    if($http->resp_headers['last-modified']){
        $sql = "UPDATE lylina_feeds SET lastmod = '".
               addslashes($http->resp_headers['last-modified']).
               "' WHERE id = ".$feed['id'];
        runSQL($sql);
    }

    if($http->resp_headers['etag']){
        $sql = "UPDATE lylina_feeds SET etag = '".
               addslashes($http->resp_headers['etag']).
               "' WHERE id = ".$feed['id'];
        runSQL($sql);
    }

    $ctype = $http->resp_headers['content-type'];
    if(preg_match('/charset=([\w\-]+)/i',$ctype,$match)){
        $enc = $match[1];
    }

    // return data
    return $http->resp_body;
}


//Setup VIM: ex: et ts=4 enc=utf-8 :
?>
