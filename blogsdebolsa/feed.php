<?php

require_once('conf.php');
require_once('inc/common.php');
require_once('inc/feedcreator.class.php');


$rss = new UniversalFeedCreator();
$rss->useCached();
$rss->title = "Blogs de bolsa";
$rss->description = "fomentando la cultura financiera";
$rss->link = "http://localhost:85/lylina/upload/";
$rss->syndicationURL = "http://localhost:85/lylina/upload/".$PHP_SELF;

$image = new FeedImage();
$image->title = "blogs de bolsa logo";
$image->url = "http://localhost:85/lylina/upload/img/blogsdebolsa.png";
$image->link = "http://www.dailyphp.net";
$image->description = "Feed proporcionado por blogsdebolsa.com";
$rss->image = $image;

// get your news items from somewhere, e.g. your database:
    $sql = "SELECT A.id, A.url, A.title, A.body, B.name, B.url as feedurl,
                   DATE_FORMAT(A.dt, '%H:%i') as time,
                   DATE_FORMAT(A.dt, '%W %D %M %Y') as date
              FROM lylina_items A, lylina_feeds B, lylina_userfeeds C
             WHERE B.id = A.feed_id
               AND B.id = C.feed_id
               AND C.user_id = 0
               AND UNIX_TIMESTAMP(A.dt) > UNIX_TIMESTAMP()-(500*60*60)
          ORDER BY A.dt DESC, A.title";
    $items = runSQL($sql);
    
    
for($n = 0; $n < count($items); $n++){
    $data = $items[$n];
    $item = new FeedItem();
    $item->title = $data['title'];
    $item->link = $data['url'];
    $item->description = $data['body'];
    $item->date = $data['dt'];
    $item->source = "http://www.dailyphp.net";
    $item->author = "John Doe";
    $rss->addItem($item);
}

$rss->saveFeed("RSS1.0", "feed.xml"); 


?>