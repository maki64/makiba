<?php
define("TITLE", "your imageboard title"); // board title
define("LOCKED", 0); // locked board accepts no posts
define("ADMINOPONLY",0); // allow OP posts to be made only by admin trip?
define("SORTBUMP", 1); // sort OPs in terms of last bump?
define("SHOWPOSTLIST", 0);
define("REPLYBOX_TOP", 1); // show the 'reply' box at the top? doesn't affect thread start box
define("POSTTRUNCATE", 2000);
define("FORMATCODE", 1);
define("SCRIPTNAME", "post.php");
define("ADMINPASS", "some password");
define("ADMINTRIP", "the part after the ! in an admin's trip");
define("SECRETKEY", "replace this string with something really random"); // something "random"
define("INFOFILE", "info"); // board rules/whatever
define("SESSFILE", "session"); // where to store the session key
define("URLROOT", ""); // root of the board, i.e the part after http://mychan.org
define("IMGDIR", "src/"); // where to store full res images
define("THUMBDIR", "thumb/"); // thumbnails
define("IMGDELETE", "catto.png"); // what to show if an image is deleted
define("THUMBDELETE", "thumb_catto.png"); // deleted image thumb
define("THREADDIR", "res/"); // where to store threads
define("MOVEUPLOAD", "The file could not be uploaded."); // error if the file couldn't be uploaded
define("ALLOWEDTYPES", array("image/jpeg", "image/png", "image/gif", IMAGETYPE_GIF, IMAGETYPE_PNG, IMAGETYPE_JPEG)); // allowed types
define("BADFILESFILE", "bad"); // where the hashes of banned files are stored
define("BANFILE", "bans"); // where banned IPs are stored
define("WORDFILTERFILE", "wordfilters"); // where wordfilters are stored
define("MAXWIDTH", 3000); // max image width
define("MAXHEIGHT", 3000); // max image height
define("MAXFSIZE", 2000000); // max file size
define("TWIDTH_OP", 230); // thumbnail width
define("TWIDTH_REPLY", 190); // thumbnail width
define("THUMBICONS", array());
define("MAXNAME", 30);
define("MAXCOMMENT", 1500);
define("MAXMAIL", 50);
define("MAXKEY", 12);
define("MAXSUBJECT", 50);
define("ANONNAME", "Anonymous");
define("MUSTCOMMENTOP", 1);
define("MUSTCOMMENTREPLY", 0);
define("POSTSFILE", "posts");
define("PERPAGE", 7);
define("SHOWREPLIES", 6);
define("ROOT", "./");
define("OLDSTYLE", 1);
define("ALLOWNOIMAGEOP", 1);
define("THREADWAIT", 40);
define("POSTWAIT", 20);
define("CSSSTYLE", "blog");
define("MAXREPLIES", 300);
define("MAXTHREADS", 40);
define("ERR_NOTHREADID", "No thread id submitted. Please submit again with a thread id.");
define("ERR_NOTFOUND", "That OP thread doesn't exist. Please start a new thread.");
define("ERR_THREADSOON", "You cannot post a thread so soon. Please try again later.");
define("ERR_POSTSOON", "You cannot post so quickly. Please try again later.");
define("ERR_BANNED", "Your IP or IP range is banned.");
define("ERR_MOVEUPLOAD", "Your file could not be uploaded. Please try another file.");
define("ERR_BADTYPE", "Your file's filetype is not accepted. Please try another filetype.");
define("ERR_BADFILE", "The hash of that file is on the ban list. Please try another file.");
define("ERR_FILESIZE", "Your file's size is too large. Please try reducing the quality.");
define("ERR_DIM", "Your file's dimensions are too large. Please try resizing it.");
define("ERR_NEEDIMAGE", "Sorry, you need an image to post on this board.");
define("ERR_MUSTCOMMENT", "You must add a comment. Try typing something.");
define("ERR_BANNEDWORD", "Your comment contained a word or phrase that is not permitted. Please edit your post and try again.");
define("ERR_TOOLONG", "Your comment is too long. Try and summarise it.");
define("ERR_FULLTHREAD", "The thread you have replied to has reached the maximum number of posts.");
define("ERR_ADMINOPONLY", "Sorry, only the administrator can create new threads at the moment.");
define("ERR_NODELKEY", "You need a deletion key to delete posts.");
define("ERR_NODELS", "You have to check posts to delete.");
?>
