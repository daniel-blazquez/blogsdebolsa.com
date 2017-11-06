<?php

// Welcome to lylina

// =============================================================
//
// The following options MUST be changed in order for lylina to
// work correctly.
//
// =============================================================

// -------------------- MySQL Configuration --------------------
// This tells lylina which database to store information in.

$conf['db_host']     = 'localhost';
$conf['db_user']     = 'buyorsel_wrdp1';
$conf['db_pass']     = 'ybX8cHjdD1on';
$conf['db_database'] = 'buyorsel_wrdp1';

// =============================================================
//
// The following options allow you to futher customize lylina
//
// =============================================================

$conf['lylina_root'] ='http://blogsdebolsa.com/';

// ----------------------- Display Mode  -----------------------
// This will allow you to configure which "display mode" to use
// with lylina. Valid settings are: "normal" - multiuser system,
// "login" - multiuser requiring login to use, "single" - a
// single user system requiring login only to edit sources.

$conf['mode']        = 'single';

// ------------------------ Page Title -------------------------
// Sets the titlebar for the page

$conf['page_title']  = 'blogs de bolsa';

// ------------------------- Page Logo -------------------------
// Allows you to change the logo seen in the page

$conf['page_logo']   = 'img/blogsdebolsa.png';

// -------------------------- Language -------------------------
// Contols the language used by lylina.
// Languages must be in the 'lang' folder, and must use the
// ISO language code, ie: 'en.inc.php'

$conf['language']    = 'es';

// ------------------------- Page Style ------------------------
// Allows you to choose the CSS skin used to theme the page.
// This file MUST be in the 'style' folder.

$conf['page_style']  = 'classic.css';

// -------------------------- Sources --------------------------
// Controls the display of sources at the bottom of the page

$conf['sources']     = 'true';

// -------------------- Digg.com integration -------------------
// Adds a "submit to digg" link to each post, letting you
// quickly post it to digg.com

$conf['digg']        = 'false';

// ------------------- del.icio.us integration -----------------
// Adds an "add to del.icio.us" link to each post, letting you
// post it quickly to your social bookmarks.

$conf['del.icio.us'] = 'true';

// --------------------- reddit integration --------------------
// Adds a "submit to reddit" link to each post, letting you
// post it quickly to reddit

$conf['reddit']      = 'false';

// ---------------------- furl integration ---------------------
// Adds an "add to furl" link to each post, letting you submit
// the item to your furl bookmarks

$conf['furl']        = 'false';

// ---------------- Open News Links in New Window --------------
// Turn this on to force links to news items to open in new
// browser windows.

$conf['new_window']  = 'true';


// ----------------   RSS feeds genration options --------------
// Customize the behaviour of the RSS feeds

// 
$conf['generate_RSS']  = 'true';

// RSS feed format to be generated
// valid format strings are: RSS0.91, RSS1.0, RSS2.0, PIE0.1 (deprecated),
// MBOX, OPML, ATOM, ATOM0.3, HTML, JS
$conf['RSS_format']  = 'RSS1.0';

// The title that the RSS feed will have
$conf['RSS_title']  = 'blogsdebolsa.com : agregador de blogs sobre bolsa';

// Description assciated to the RSS feed
$conf['RSS_description']  = 'fomentando la cultura financiera desde 2006';

// URL that 
$conf['RSS_syndicationURL'] = '';

// Number of elements to include in the RSS Feed
$conf['RSS_elements_to_include'] = 30;





// =============================================================
//
// The following options ARE FOR ADVANCED USERS ONLY.
// These options allow for powerful adjustments to how lylina
// works, and should only be used by advanced users.
//
// =============================================================

// ------------------------- Debug Mode ------------------------
// This setting will turn on debugging mode for item fetching

$conf['debug']       = 'true';

// --------------------- Password Encryption -------------------
// This sets the password encryption scheme in lylina. DO NOT
// CHANGE THIS SETTING AFTER YOU SETUP USERS IN LYLINA. No users
// will be able to login. By default lylina sets up the admin
// user with a smd5 encrypted password, thus, you will have to
// change this manually in MySQL. It is not suggested you change
// this setting.
// 
// Valid encryption schemes: smb5, md5, sha1, ssha, crypt, mysql,
// my411

$conf['passcrypt']   = 'smd5';

// ---------------------- HTMLSax3 location --------------------
// Specifies the location of the HTMLSax3 library. This is
// installed by lylina, thus, should not require modification.

define('XML_HTMLSAX3','inc/safehtml/');
?>
