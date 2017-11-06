<?php
/* 
lylina news aggregator

Copyright (C) 2005 Andreas Gohr
Copyright (C) 2006 Eric Harmon

Contains code from 'lilina':
Copyright (C) 2004-2005 Panayotis Vryonis

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

require_once('conf.php');
require_once('lang/'.$conf['language'].'.inc.php');
require_once('inc/common.php');
require_once('inc/auth.php');
require_once('inc/safehtml/safehtml.php');
require_once('inc/html.php');
require_once('inc/feedcreator.class.php');

@setlocale(LC_All, $lang['LOCALE']);
setlocale(LC_ALL, 'sp');

if($_REQUEST['logout']){
    $UID = logout();
} elseif($_REQUEST['u']){
    $UID = login($_REQUEST['u'],$_REQUEST['p']);
} else{
    $UID = checkAuthToken();
}

header('Content-Type: text/html; charset=utf-8');
html_head(true);

if($conf['mode'] == 'single')
	$UID = 0;


printItems($UID,$_REQUEST['hours']);

print('<div id="sidebar">');	
printSources($UID);
printMeta();
   print '<p align="center"><a href="http://blogsdebolsa.com/feed.xml"><img src="img/rss.png" alt="RSS Disponible"/></a></p>';

print ('</div>');

if ($conf['generate_RSS'] == 'true')
	generateRSSFeed();

html_foot();

/* --------------------------------------------- */

function printMeta(){

   print '<div class="sources">';
   print '<b>blogsdebolsa.com</b>';
   print '<p align="center">';
   print '<a href="http://blogsdebolsa.blogspot.com/2006/04/acerca-de-blogsdebolsacom.html"><b>Acerca de blogsdebolsa</b></a><br>';

   print '<a href="http://blogsdebolsa.blogspot.com/"><b>blog de blogsdebolsa.com</b></a><br>';

   print '<a href="http://blogsdebolsa.blogspot.com/2006/04/aade-tu-blog-de-bolsa.html"><b>Agrega tu blog!</b></a><br>';

     print '<a href="http://blogsdebolsa.blogspot.com/2006/04/software-detrs-de-blogsdebolsacom.html">
<b>nuestro software</a><br>' ;

print('<script type="text/javascript" src="http://embed.technorati.com/embed/6gf8fir4wc.js"></script>');

   print '</p>';
   print '</div>';

}


function printItems($UID,$hours){
    if(!is_numeric($hours)) $hours = 0;
    if(!$hours) $hours = 24;

    $sql = "SELECT A.id, A.url, A.title, A.body, B.name, B.url as feedurl,
                   DATE_FORMAT(A.dt, '%H:%i') as time,
                   DATE_FORMAT(A.dt, '%W %D %M %Y') as date
              FROM lylina_items A, lylina_feeds B, lylina_userfeeds C
             WHERE B.id = A.feed_id
               AND B.id = C.feed_id
               AND C.user_id = $UID
               AND UNIX_TIMESTAMP(A.dt) > UNIX_TIMESTAMP()-($hours*60*60)
          ORDER BY A.dt DESC, A.title";
    $items = runSQL($sql);
//    foreach($items as $item){
//        formatItem($item);
//    }
	for($n = 0; $n < count($items); $n++)
		formatItem($items[$n],$n);
}

function generateRSSFeed(){

    $rss = new UniversalFeedCreator();
    $rss->useCached();
    $rss->title = $conf['RSS_title'];
    $rss->description = $conf['RSS_description'];
    $rss->link = $conf['lylina_root'] ;
    $rss->syndicationURL = $conf['lylina_root'] .$PHP_SELF;

    $image = new FeedImage();
    $image->title = $conf['RSS_title'];
    $image->url = $conf['page_logo'];
    $image->link = $conf['lylina_root'] ;
    $image->description = $conf['RSS_description'];
    $rss->image = $image;
    $limit = $conf['RSS_elements_to_include'];

    $sql = "SELECT A.id, A.url, A.title, A.body, B.name, B.url as feedurl,
                   DATE_FORMAT(A.dt, '%H:%i') as time,
                   DATE_FORMAT(A.dt, '%W %D %M %Y') as date
              FROM lylina_items A, lylina_feeds B, lylina_userfeeds C
             WHERE B.id = A.feed_id
               AND B.id = C.feed_id
               AND C.user_id = 0
          ORDER BY A.dt DESC, A.title LIMIT 0,30";
    $items = runSQL($sql);
    
    
    print $conf['RSS_elements_to_include'];
    
    
    for($n = 0; $n < count($items); $n++){
        $data = $items[$n];
        $item = new FeedItem();
        $item->title = utf8_decode($data['title']);
        $item->link = $data['url'];
        $item->description = utf8_decode($data['body']);
        $item->date = $data['dt'];
        $item->source = "http://www.blogsdebolsa.com";
        $item->author = $data['name'];
        $rss->addItem($item);
    }

    $rss->saveFeed($conf['RSS_format'], "feed.xml", false); 

}


function getFaviconURL($location){
    if(!$location){
        return false;
    }else{
	$url_parts = parse_url($location);
	$full_url = "http://$url_parts[host]";
	if(isset($url_parts['port'])){
		$full_url .= ":$url_parts[port]";
	}
	$favicon_url = $full_url . "/favicon.ico" ;
    }
   	return $favicon_url;
}

function channelFavicon($location) {
	$empty_ico_data = base64_decode(
	'AAABAAEAEBAAAAEACABoBQAAFgAAACgAAAAQAAAAIAAAAAEACAAAAAAAQAEAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAA////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
	'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD//wAA//8AAP//AAD//wAA//8AAP//' .
	'AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAA=') ;
	
	$ico_url = getFaviconURL($location) ;
	if(!$ico_url) {
		return false ;
	}
	$cached_ico = './cache/' . md5($ico_url) . ".ico" ;
	$cachetime = 7 * 24 * 60 * 60; // 7 days
    	// echo "<br> $ico_url , $cached_ico " ;
	// Serve from the cache if it is younger than $cachetime
	if (file_exists($cached_ico) && (time() - $cachetime < filemtime($cached_ico))) return $cached_ico ;
	if (!$data = @file_get_contents($ico_url)) $data=$empty_ico_data ;
	if (stristr($data,'html')) $data=$empty_ico_data ;
	$fp = fopen($cached_ico,'w') ;
	fputs($fp,$data) ;
	fclose($fp) ;
	return $cached_ico ;
}

function getHost($location) {
	$temp = parse_url($location);
	return $temp['host'];
}

function formatItem($item,$number){
	global $conf;
	global $lang;

    static $date='';
    $time = $item['time'];

    if($item['date'] != $date){
        $date = $item['date'];
        $timestamp = strtotime($date);       
        $pdate = strftime($lang['DATEFORMAT'],$timestamp);
        printf( '<h1>'.utf8_encode($pdate).'</h1>');
    } 

	print '<div class="feed">';
//    print '<div class="item" id="IITEM-'.$item['id'].'">';
	print '<div class="item" id="IITEM-'.md5($item['url']).'">';
	print '<img src="'.channelFavicon($item['feedurl']).'" width="16" height="16" class="icon" alt="" />';

    $timestamp = strtotime($time) + 7*60*60;
    print '<span class="time">'.strftime('%H:%m',$timestamp ).'</span> ';
    print '<span class="title" id="TITLE'.$number.'">';
    print htmlspecialchars($item['title']);
    print '</span> ';
    print '<span class="source">';
	if($conf['new_window'] == 'true') 
		print '<a href="'.$item['url'].'" target="_new">&raquo;';
	else 
		print '<a href="'.$item['url'].'">&raquo;';
    print htmlspecialchars(utf8_encode($item['name']));
    print '</a>';
    print '</span>';
    print '<div class="excerpt" id="ICONT'.$number.'">';
    $safehtml =& new safehtml();
    print $safehtml->parse($item['body']);
	print '<div class="integration">';
	
	if($conf['digg'] == 'true' && !stristr(getHost($item['url']),'digg'))
		print '<a href="http://digg.com/submit?phase=3&amp;url='.$item['url'].'&amp;title='.$item['title'].'" target="_new"><img src="img/digg.gif" alt="" /> '.$lang['digg'].'</a> ';
		
	if($conf['del.icio.us'] == 'true')
		print '<a href="http://del.icio.us/post?url='.$item['url'].'&amp;title='.$item['title'].'" target="_new"><img src="img/del.icio.us.gif" alt="" /> '.$lang['delicious'].'</a> ';
		
	if($conf['reddit'] == 'true' && !stristr(getHost($item['feedurl']),'reddit'))
		print '<a href="http://www.reddit.com/submit?url='.$item['url'].'&amp;title='.$item['title'].'" target="_new"><img src="img/reddit.png" alt="" /> '.$lang['reddit'].'</a> ';
		
	if($conf['furl'] == 'true')
		print '<a href="javascript:furlPost(\''.$item['url'].'\',\''.$item['title'].'\');"><img src="img/furl.gif" alt="" /> '.$lang['furl'].'</a> ';
		
	print '</div>';
    print '</div>';
    print '</div>';
	print '</div>';
	flush();
}

function formatSource($source) {
	print '<li><a href="'.$source['url'].'">';
	print '<img src="img/feed-icon16x16.png" width="14" height="14"/></a> <a href="'.$source['main_url'].'">';
	print utf8_encode($source['name']);
	print '</a></li>';
}

function printSources($uid) {
	global $lang;
	$sql = "SELECT B.name, B.url, B.main_url
              FROM lylina_feeds B, lylina_userfeeds C
             WHERE B.id = C.feed_id
               AND C.user_id = $uid
	      ORDER BY B.name";
    $sources = runSQL($sql);
	print '<div class="sources"><b>'.$lang['sources'].'</b><ul>';
	for($n = 0; $n < count($sources); $n++)
		formatSource($sources[$n]);
	print '</div>';
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
?>
