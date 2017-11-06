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

require_once('conf.php');
require_once('lang/'.$conf['language'].'.inc.php');
require_once('inc/common.php');
require_once('inc/auth.php');
require_once('inc/html.php');

if($_REQUEST['u']){
    $UID = login($_REQUEST['u'],$_REQUEST['p']);
}else{
    $UID = checkAuthToken();
}

if(!$UID) 
{
	header("HTTP/1.1 403 Forbidden");
	die('Forbidden');
}

header('Content-Type: text/html; charset=utf-8');

html_head(false,"preferences");

    switch($_REQUEST['do']){
		case 'user':
			removeUser($_REQUEST['ruid']);
			break;
        case 'savemaster':
            feedAdd(0);
            break;
        case 'save':
            feedAdd($UID);
            break;
        case 'chpwd':
            changePassword($UID,$_REQUEST['p1'],$_REQUEST['p2']);
            break;
        case 'uadd':
            newUser($_REQUEST['nu'],$_REQUEST['p1'],$_REQUEST['p2']);
            break;
        case 'del':
            feedDel($UID,$_REQUEST['fid']);
            break;
		case 'delmaster':
            feedDel(0,$_REQUEST['fid']);
            break;			
        case 'sub':
            feedSub($UID,$_REQUEST['fid']);
            break;
        default:
    }

print '<div class="prefs">';

if($conf['mode'] != 'single') 
	feedAddForm($UID);

if($UID == 1 && $conf['mode'] != 'login') 
	feedAddFormMaster();

changePasswordForm();

if($UID == 1 && $conf['mode'] != 'single')
	addUserForm();
	
if($conf['mode'] != 'single')
	newestFeedList();
	
if($UID == 1 && $conf['mode'] != 'single')
	listUsers();

print '</div>';

if($UID == 1 && $conf['mode'] != 'login' && $conf['mode'] != 'single') {
	print '<img src="img/view.png"> '.$lang['view'].': ';
	if($main == 1)
		print '<a href="edit.php">'.$lang['persfeeds'].'</a> <a href="edit.php?main=1"><strong>'.$lang['mainfeeds'].'</strong></a>';
	else
		print '<a href="edit.php"><strong>'.$lang['persfeeds'].'</strong></a> <a href="edit.php?main=1">'.$lang['mainfeeds'].'</a>';

}

if($UID == 1 && $main == 1 && $conf['mode'] != 'login' || $conf['mode'] == 'single')
	listSubscribedFeeds(0);
else
	listSubscribedFeeds($UID);
	
html_foot();


/* ------------------------------------------- */

function changePasswordForm(){
    global $lang;
    print '<form method="post" action="edit.php">'.NL;
    print '<fieldset class="passedit">'.NL;
    print '<legend><img src="img/password_change.png"> '.$lang['changepw'].'</legend>'.NL;

    print '<input type="hidden" name="do" value="chpwd" />'.NL;

    print '<label for="p1">'.$lang['newpw'].':</label>';
    print '<br />'.NL;
    print '<input type="password" id="p1" name="p1" />'.NL;
    print '<br />'.NL;

    print '<label for="p2">'.$lang['pwrepeat'].':</label>';
    print '<br />'.NL;
    print '<input type="password" id="p2" name="p2" />'.NL;
    print '<br />'.NL;

    print '<input type="submit" value="'.$lang['changepwbutton'].'" />'.NL;

    print '</fieldset>'.NL;
    print '</form>'.NL;
}

function addUserForm(){
    global $lang;
    print '<form method="post" action="edit.php">'.NL;
    print '<fieldset class="useradd">'.NL;
    print '<legend><img src="img/user_add.png"> '.$lang['newuser'].'</legend>'.NL;

    print '<input type="hidden" name="do" value="uadd" />'.NL;

    print '<label for="nu">'.$lang['username'].':</label>';
    print '<br />'.NL;
    print '<input type="text" id="nu" name="nu" />'.NL;
    print '<br />'.NL;

    print '<label for="p1">'.$lang['password'].':</label>';
    print '<br />'.NL;
    print '<input type="password" id="p1" name="p1" />'.NL;
    print '<br />'.NL;

    print '<label for="p2">'.$lang['pwrepeat'].':</label>';
    print '<br />'.NL;
    print '<input type="password" id="p2" name="p2" />'.NL;
    print '<br />'.NL;

    print '<input type="submit" value="'.$lang['newuserbutton'].'" />'.NL;

    print '</fieldset>'.NL;
    print '</form>'.NL;
}

function listUsers() {
    global $lang;
	$sql = "SELECT id, login
              FROM lylina_users
          ORDER BY id";
    $users = runSQL($sql);
	print '<fieldset>';
    print '<legend><img src="img/users.png"> '.$lang['user'].'</legend>';
    foreach($users as $user){
        print '<div class="item">';
        print '<span class="source">';
		print '<a href="edit.php?do=user&amp;ruid='.$user['id'].'" title="'.$lang['rmuser'].'" onclick="return confirm(\''.$lang['rmconfirm'].' '.$user['login'].'?\')"><img src="img/delete.png"> ';
        print $user['login'];
		print '</a>';
        print '</span>';
        print '</div>';
    }
    print '</fieldset>';
}

function listSubscribedFeeds($uid){
    global $lang;
    $sql = "SELECT A.id, A.url, A.name
              FROM lylina_feeds A, lylina_userfeeds B
             WHERE B.user_id = $uid
               AND A.id = B.feed_id
          ORDER BY A.name, A.url";
    $feeds = runSQL($sql);

    foreach($feeds as $feed){
        print '<div class="item" id="IITEM-'.$feed['id'].'">';
        print '<span class="time"></span>';
        print '<span class="source">';
		if($uid == 0)
			print '<a href="?do=delmaster&amp;fid='.$feed['id'].'" onclick="return confirm(\''.$lang['unsubconfirm'].'\')"><img src="img/remove.png"></a>';
		else
			print '<a href="?do=del&amp;fid='.$feed['id'].'" onclick="return confirm(\''.$lang['unsubconfirm'].'\')"><img src="img/remove.png"></a>';
        print '</span>';
        print '<span class="title" id="TITLE'.$feed['id'].'">';
        if(!empty($feed['name'])){
            print htmlspecialchars($feed['name']);
        }else{
            $parts = parse_url($feed['url']);
            print htmlspecialchars($parts['host']);
        }
        print '</span>';
        print '<div class="excerpt" id="ICONT'.$feed['id'].'">';
        print '<a href="'.$feed['url'].'">'.$feed['url'].'</a>';
        print '</div>';
        print '</div>';
    }
}



function feedAddForm($uid){
    global $lang;
    print '<form method="post" action="edit.php">'.NL;
    print '<fieldset class="feededit">';
    print '<legend><img src="img/feed_add.png"> '.$lang['newfeed'].'</legend>'.NL;

    print '<input type="hidden" name="do" value="save" />'.NL;

    print '<label for="fn">'.$lang['optname'].':</label>';
    print '<br />'.NL;
    print '<input type="text" id="fn" name="name" />'.NL;
    print '<br />'.NL;

    print '<label for="fn">'.$lang['feedurl'].':</label>';
    print '<br />'.NL;
    print '<input type="text" id="fu" name="url" />'.NL;
    print '<br />'.NL;

    print '<input type="submit" value="'.$lang['subscribe'].'" />'.NL;

    print '</fieldset>'.NL;
    print '</form>'.NL;
}
function feedAddFormMaster(){
    global $lang;
    print '<form method="post" action="edit.php?main=1">'.NL;
    print '<fieldset class="feededit">';
    print '<legend><img src="img/main_add.png"> '.$lang['feedmain'].'</legend>'.NL;

    print '<input type="hidden" name="do" value="savemaster" />'.NL;

    print '<label for="fn">'.$lang['optname'].':</label>';
    print '<br />'.NL;
    print '<input type="text" id="fn" name="name" />'.NL;
    print '<br />'.NL;

    print '<label for="fn">'.$lang['feedurl'].':</label>';
    print '<br />'.NL;
    print '<input type="text" id="fu" name="url" />'.NL;
    print '<br />'.NL;

    print '<input type="submit" value="'.$lang['subscribe'].'" />'.NL;

    print '</fieldset>'.NL;
    print '</form>'.NL;
}


function newUser($nu,$p1,$p2){
    if(empty($p1)){
        msg('Empty Passwords forbidden',-1);
        return;
    }
    if($p1 != $p2){
        msg('Passwords do not match',-1);
        return;
    }
    if(!$nu){
        msg('No user given',-1);
        return;
    }

    addUser($nu,$p1);
    msg('User added.');
}

function changePassword($uid,$p1,$p2){
    if(empty($p1)){
        msg('Empty Passwords forbidden',-1);
        return;
    }
    if($p1 != $p2){
        msg('Passwords do not match',-1);
        return;
    }

    setPassword($uid,$p1);
    msg('Password updated.');
}

function feedDel($uid,$fid){
    if(!is_numeric($fid)) $fid = 0;
    if(!$fid) return false;

    $sql = "DELETE FROM lylina_userfeeds
             WHERE user_id = $uid
               AND feed_id = $fid";
    runSQL($sql);
    
    $sql = "SELECT COUNT(*) as cnt
              FROM lylina_userfeeds
             WHERE feed_id = $fid";
    $result = runSQL($sql);

    if($result[0]['cnt'] > 0){
        return;
    }

    $sql = "DELETE FROM lylina_items
             WHERE feed_id = $fid";
    runSQL($sql);

    $sql = "DELETE FROM lylina_feeds
             WHERE id = $fid";
    runSQL($sql);
    msg('Feed removed.');
}

function feedAdd($uid){
    $url  = trim($_REQUEST['url']);
    $name = trim($_REQUEST['name']);
    if(empty($url)) return false;

    $url  = addslashes($url);
    $name = addslashes($name);

    $sql = "INSERT IGNORE INTO lylina_feeds
               SET  url  = '$url',
                    name = '$name',
                   added = NOW()";
    runSQL($sql);

    $sql = "SELECT id FROM lylina_feeds WHERE url = '$url'";
    $result = runSQL($sql);
    $fid = $result[0]['id'];

    if(!$fid) return false;

    $sql = "INSERT IGNORE INTO lylina_userfeeds
               SET user_id = $uid,
                   feed_id = $fid";
    runSQL($sql);
	msg('Your feed has been added and should update within 10 minutes.');
}

function feedSub($uid,$fid){
    if(!is_numeric($fid)) $fid = 0;
    if(!$fid) return false;

    $sql = "INSERT IGNORE INTO lylina_userfeeds
               SET user_id = $uid,
                   feed_id = $fid";
    runSQL($sql);
}

function newestFeedList(){
    global $lang;
    $sql = "SELECT name, url, id
              FROM lylina_feeds
          ORDER BY added DESC
             LIMIT 10";
    $feeds = runSQL($sql);

    print '<fieldset>';
    print '<legend><img src="img/main_add.png"> '.$lang['recentfeeds'].'</legend>';
    foreach($feeds as $feed){
        print '<div class="item">';
        print '<span class="source">';
		print '<a href="edit.php?do=sub&amp;fid='.$feed['id'].'" title="'.$lang['subfeed'].'"><img src="img/add.png"> ';
        if(!empty($feed['name'])){
            print htmlspecialchars($feed['name']);
        }else{
            $parts = parse_url($feed['url']);
            print htmlspecialchars($parts['host']);
        }
		print '</a>';
        print '</span>';
        print '</div>';
    }
    print '</fieldset>';
}

function removeUser($ruid) {
	$sql = "SELECT feed_id 
	          FROM lylina_userfeeds
             WHERE user_id = $ruid";
    $feeds = runSQL($sql);
	
	$sql = "DELETE FROM lylina_users
             WHERE id = $ruid";
    runSQL($sql);
  
	foreach($feeds as $feed) {
		$fid = $feed['feed_id'];
    	feedDel($ruid,$fid);
	}
	msg('User removed.');
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
?>
