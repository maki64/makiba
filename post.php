<html>

<b><SPAN>your imageboard title</SPAN></b></font>
<style>
    body {
    background-color:#ffffee;
    color:#800000;
}

a {
    color:#0000ee;
}

a:visited {
    color:#0000ee;
}

.rtd {
    background-color: #F0E0D6;
    padding-right:5px;
}

.rts {
    vertical-align:top;
    width:18px;
}

.implying {
    color: #789922;
}

.orangetext {
    color: #FF8C00;
}
.fl {
    background-color: #ea8;
    width: 4.5em;
}
#info {
    font-size:small;
    max-width:75%;
    margin-bottom:50px;
}

#pb {
    margin-left:auto;
    margin-right:auto;
    width:50%;
}

.adminpost {
    font-style:italic;
    color:red;
    font-family:sans-serif;
}

#deleteform {
    float:right;
}

</style>
<hr width="90%" size=1><form action="post.php?mode=post" method="POST" enctype="multipart/form-data"><input type="hidden" name="thread" value="0"><div id="pb"><table><tr><td class="fl"><b>Name</b></td><td><input type=text name=name size="28"></td></tr>
<tr><td class="fl"><b>E-mail</b></td><td><input type=text name=email size="28"></td></tr>
<tr><td class="fl"><b>Subject</b></td><td><input type=text name=subject size="35"><input type=submit value="スレッドを立てる"></td></tr>
<tr><td class="fl"><b>Comment</b></td><td><textarea id="com" name=comment cols="48" rows="4" id="ftxa"></textarea></td></tr>
<tr><td class="fl"><b>File</b></td><td><input type=file name=img size="35">[<label><input type=checkbox name="noimage" value="y">No image</label>]</td></tr><tr><td class="fl"><b>Key</b></td><td><input type=password name=key size=8 maxlength=12 ></td></tr>
</table>
<div id="info">

</div>
</div>
</form>
<?php


ini_set('error_reporting', E_ALL);
ini_set('display_errors', 'On');
include 'settings.php';

function posthash($tp){
    $threadid = $tp['thread']?$tp['thread']:$tp['id'];
    $identity = $tp['ip']."\x1f".$threadid."\x1f".SECRETKEY;
    return substr(hash("sha256", $identity),-8);
}

function mktripcode($pw){
    /* this function is Copyright (C) avimedia, licensed under the GPLv3
       source: http://avimedia.livejournal.com/1583.html
       license: https://www.gnu.org/licenses/gpl.html */
    $pw=mb_convert_encoding($pw,'SJIS','UTF-8');
    $pw=str_replace('&','&amp;',$pw);
    $pw=str_replace('"','&quot;',$pw);
    $pw=str_replace("'",'&#39;',$pw);
    $pw=str_replace('<','&lt;',$pw);
    $pw=str_replace('>','&gt;',$pw);

    $salt=substr($pw.'H.',1,2);
    $salt=preg_replace('/[^.\/0-9:;<=>?@A-Z\[\\\]\^_`a-z]/','.',$salt);
    $salt=strtr($salt,':;<=>?@[\]^_`','ABCDEFGabcdef');

    $trip=substr(crypt($pw,$salt),-10);
    return $trip;
}

function login($pass){
    if($pass == ADMINPASS){
        $sess_id = hash("sha256", microtime().SECRETKEY.mktripcode(ADMINPASS));
        file_put_contents(SESSFILE, $sess_id);
        setcookie('sess_id', $sess_id, time()+60*60*60);
        $_COOKIE['sess_id'] = $sess_id;
        return 1;
    } else {
        return 0;
    }
}

function validate($cookie_key){
    return strcmp($cookie_key, file_get_contents(SESSFILE)) == 0;
}

function logout(){
    if(validate($_COOKIE['sess_id'])){
        unset($_COOKIE['sess_id']);
        setcookie('sess_id', '', time()-3600);
        file_put_contents(SESSFILE, "");
        return 1;
    }
    return 0;
}

function infopage($t, $s){
    echo '<!doctype html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><link rel="stylesheet" type="text/css" href="'.URLROOT.CSSSTYLE.'.css"><title>'.$t.'</title></head><body><h2>'.$t.'</h2><p>'.$s.'</p> [<a href="'.URLROOT.'index.html">Return]</a></body></html>';
    die();
}

function getposts(){
	$posts = explode("\x1e", file_get_contents(POSTSFILE));
	$all = array();
	foreach($posts as $post){
		$a = array();
        $p = explode("\x1f", $post);
        //print_r($p);
        //print empty($p);
        if(!empty($p)){
            $a['id'] = intval($p[0]);
            if($a['id']){
                $a['thread'] = intval($p[1]);
                $a['src'] = $p[2];
                $a['thumb'] = $p[3];
                $a['file'] = $p[4];
                $a['width'] = $p[5];
                $a['height'] = $p[6];
                $a['size'] = $p[7];
                $a['hash'] = $p[8];
                $a['name'] = $p[9];
                $a['email'] = $p[10];
                $a['comment'] = $p[11];
                $a['subject'] = $p[12];
                $a['key'] = $p[13];
                $a['ip'] = $p[14];
                $a['time'] = intval($p[15]);
                $a['bumptime'] = intval($p[16]);
                $a['admin'] = boolval($p[17]);
            array_push($all, $a);
            }
        }
	}
    //print_r($all);
	return $all;
}

function get_post($id){
    foreach(getposts() as $post){
        if($post['id'] == $id) { return $post; }
    }
    return array();
}

function num_posts($parent){
    $c = 0;
    foreach(getposts() as $p){
        if($p['thread'] == $parent)
            $c++;
    }
   return $c;
}

function postexists($id){
	foreach(getposts() as $p){
		if($p['id'] == $id){
            return 1;
		}
	}
    return 0;
}

function getthreadid($id){
    foreach(getposts() as $p){
        if($p['id'] == $id){
            if($p['thread'] == 0){
                return intval($p['id']);
            } else {
                return intval($p['thread']);
            }
        }
    }
    return 0;
}

function quotelink_cb($matches)
{
    $p = preg_match("/&gt;&gt;(\d+)/", $matches[1], $g);
    $id = intval($g[1]);
    $x = getthreadid($id);
    if($x){
        return '<a href="'.URLROOT.THREADDIR.$x.'.html#p'.$id.'">'.$matches[1].'</a>';
    } else {
        return $matches[1];
    }
}

/*function markup($c){
    $ca = explode("\n", $c);
    foreach($ca as &$tc){
        $tc = preg_replace_callback(
            "/(&gt;&gt;\d+)/",
            "quotelink_cb",
            $tc);
        $tc = preg_replace('/^(&gt;.*)/i', '<span class="implying">$1</span>', $tc);
        $tc = preg_replace('/^\s\s(.*)/i', '<code class="code">$1</code>', $tc);
        $tc = preg_replace("/``(.*)''/i", '<code class="code">$1</code>', $tc);
    }
    $g = implode("<br>", $ca);
    return preg_replace("/```(.*)'''/s", '<code>$1</code>', $g);
    }*/

function markup($c){
    $g = explode("\n", $c);
    foreach($g as &$ca){
        $ca = preg_replace_callback(
            "/(&gt;&gt;\d+)/",
            "quotelink_cb",
            $ca);
	$ca = preg_replace('/^(&gt;.*)/i', '<span class="implying">$1</span>', $ca);
	$ca = preg_replace('/^(&lt;.*)/i', '<span class="orangetext">$1</span>', $ca);

        if(FORMATCODE){
            /* double quotes for inline code */
            $ca = preg_replace("/``(.*)''/i", '<code class="code">$1</code>', $ca);
        }
    }
    $c = implode("\n", $g);

    $c = preg_replace("/\n/s", "<br>", $c);

    /* this is experimental, and will double space your code
       unless you add a CSS rule like pre > br { display: none; } */
    if(FORMATCODE)
        $c = preg_replace("/```(.*)'''/s", '<pre>$1</pre>', $c);

    return $c;
}



function delete_last_thread(){
    $posts = getposts();
    $last_time = time();
    $last_id = 0;
    foreach($posts as $post){
        if(($post['time'] < $last_time) && ($post['thread'] == 0)){
            $last_id = $post['id'];
            $last_time = $post['time'];
        }
    }
    delete_post($last_id);
}

function delete_post($id){
    $posts = getposts();
    $a = array();
    foreach($posts as $post){
        if($post['id'] != $id && $post['thread'] != $id){
            /* only keep posts where their id isn't the $id we want to delete
               also don't keep the posts that are children of the thread */
            array_push($a, $post);
        }
    }
    writeposts($a);
}

function delete_imageonly($id){
    $posts = getposts();
    $a = array();
    foreach($posts as &$post){
        if($post['id'] == $id){
            $post['src'] = "del";
            $post['thumb'] = "";
        }
        array_push($a, $post);
    }
    writeposts($a);
}

function isBanned($ip) {
  $ips = explode("\n", file_get_contents(BANFILE));
  return in_array($ip, $ips);
}

function can_post($ip){
    $posts = getposts();
    $op_time = 0;
    $reply_time = 0;
    foreach($posts as $post){
        if($post['ip'] == $ip)
            if($post['thread'] == 0)
                if($post['time'] > $op_time)
                    $op_time = intval($post['time']);
                else
                    ;
            else
                if($post['time'] > $reply_time)
                    $reply_time = intval($post['time']);
    }
    return array((time() - $op_time) > THREADWAIT, (time() - $reply_time) > POSTWAIT);
}

function old_style_text($tp){
    $time = date("y/m/d (D) H:i", $tp['time']);
    return '<input type=checkbox name="del[]" value="'.$tp['id'].'"><font color="#cc1105"><b>'.$tp['subject'].'</b></font>
Name <font color="#117743"><b '.($tp['admin'] ? 'class="adminpost"' : '').'>'.$tp['name'].' </b></font> '.$time.' ID:'.posthash($tp).' No.<a href="javascript:void(0);" onclick="javascript:quote('.$tp['id'].');">'.$tp['id'].'</a>';
}

function old_style_image($tp){
    if(strcmp($tp['src'], "del") == 0){
        $tp['src'] = URLROOT.IMGDELETE;
        $tp['thumb'] = URLROOT.THUMBDELETE;
        $tp['f'] = "(file deleted)";
    } else {
        $tp['f'] = $tp['src'];
        $tp['src'] = URLROOT.IMGDIR.$tp['src'];
        $tp['thumb'] = URLROOT.THUMBDIR.$tp['thumb'];
    }
    $g = 'Filename: <a href="'.$tp['src'].'" target="_blank">'.$tp['f'].'</a>-('.$tp['size'].' B) <small>Thumbnail</small><br>
<a href="'.$tp['src'].'" target="_blank">';
    if($tp['thread'] == 0)
        $g .= '<img style="margin-bottom:10px;" src="'.$tp['thumb'].'" border=0 align=left hspace=20 alt="'.$tp['size'].' B"></a>';
    else
        $g .= '<img src="'.$tp['thumb'].'" border=0 align=left hspace=20 alt="'.$tp['size'].' B"></a>';
    return $g;
}

function old_style_thread($thread, $full) {
	$txt = "";
    //$tp = $thread[0];
	//$replies = array_slice($thread, 1);
    //print_r($thread);
    $tp = $thread;
    $replies = $thread['replies'];
    $sortArray = array();

    if(!empty($replies)){
        foreach($replies as $r){
            foreach($r as $key=>$value){
                if(!isset($sortArray[$key])){
                    $sortArray[$key] = array();
                }
                $sortArray[$key][] = $value;
            }
        }

        array_multisort($sortArray['id'],SORT_ASC,$replies);
        //print_r($replies);
    }

	if(!$full && count($tp['replies']) > SHOWREPLIES) {
        $om = (count($tp['replies']) - SHOWREPLIES) . ' repl'.((count($tp['replies']) - SHOWREPLIES) == 1 ? 'y' : 'ies').' ommitted. Click Reply to read all.';
        } else {
		$om = "";
	}

    $txt .= '<div class="thre" id="p'.$tp['id'].'">';

	if($tp['src'] != ""){
		$txt .= old_style_image($tp);
	}

    $txt .= old_style_text($tp);
    $tm = '';
    $mc = '';
    if($full){
        $mc = markup($tp['comment']);
    } else {
        if(mb_strlen($tp['comment']) > POSTTRUNCATE){
            $mc = markup(mb_substr($tp['comment'], 0, POSTTRUNCATE))."．．．";
            $tm = "Post truncated. Click Reply to read it all.";
        } else {
            $mc = markup($tp['comment']);
        }
    }
    $txt .= ' [<a href="'.URLROOT.THREADDIR.$tp['id'].'.html" class="hsbn">Reply</a>]<blockquote>'.$mc.'</blockquote><font color="#707070">'.$tm.$om.'</font><br>';

    if(!$full){
        $replies = array_slice($replies, -SHOWREPLIES);
    }
    if(count($tp['replies']) > 0){
        foreach($replies as $reply){
            $txt .= '<table border=0 id="p'.$reply['id'].'"><tr><td class=rts>…</td><td class=rtd>';
            $txt .= old_style_text($reply);
            $txt .= "<br>";
            if($reply['src'] != ""){
                $txt .= old_style_image($reply);
                $txt .= '<blockquote>';
            } else {
                $txt .= '<blockquote>';
            }
            $txt .= markup($reply['comment']).'</blockquote></td></tr></table>';
        }
    }
    $txt .= '</div><div style="clear:left;"></div><hr>';
    return $txt;
}

function pageselect($pagenum, $pagecount) {
    $t = "";
    $t .= '<table border="1" id="pageselect"><tr><td>';
    if($pagenum == 0)
        $t .= 'First page';
    else
        $t .= '<form action="'.URLROOT.($pagenum-1).'.html" method="GET"><input type="submit" value="Previous page"></form>';
    $t .= '</td><td>';
    for($i = 0; $i < $pagecount; $i++){
        if($i == $pagenum)
            $t .= '['.$i.'] ';
        else
            $t .= '[<a href="'.$i.'.html">'.$i.'</a>] ';
    }
    $t .= '</td><td>';
    if($pagenum == $pagecount-1)
        $t .= 'Last page';
    else
        $t.= '<form action="'.URLROOT.($pagenum+1).'.html" method="GET"><input type="submit" value="Next page"></form>';
    return $t.'</table>';
}

function deleteform(){
    return '<div id="deleteform">[Delete post] [<input type=checkbox name="imageonly" value="y"> File only]<br>Deletion key <input type=text name="deletekey"> <input type=submit value="Delete"></form></div>';
}

function postform($op){
        $r = '';

    if($op > 0)
        $r .= '[<a href="'.URLROOT.'">Return</a>]
<div style="background-color:#e04000">
<font color="#FFFFFF">Reply mode</font>
</div>';
    $inf = file_get_contents(INFOFILE);
        if(!ADMINOPONLY || $op > 0){
    if(!LOCKED){
        if(num_posts($op) < MAXREPLIES){
        $r .= '<form action="'.URLROOT.SCRIPTNAME.'?mode=post" method="POST" enctype="multipart/form-data">';

        $r .= '<input type="hidden" name="thread" value="'.$op.'">';

        $r .= '<div id="pb"><table><tr><td class="fl"><b>Name</b></td><td><input type=text name=name size="28"></td></tr>
<tr><td class="fl"><b>E-mail</b></td><td><input type=text name=email size="28"></td></tr>
<tr><td class="fl"><b>Subject</b></td><td><input type=text name=subject size="35"><input type=submit value="'.($op>0?'返信する':'スレッドを立てる').'"></td></tr>
<tr><td class="fl"><b>Comment</b></td><td><textarea id="com" name=comment cols="48" rows="4" id="ftxa"></textarea></td></tr>
<tr><td class="fl"><b>添付File</b></td><td><input type=file name=img size="35">';
        if(ALLOWNOIMAGEOP){
            $r .= '[<label><input type=checkbox name="noimage" value="y">No image</label>]';
        }
        $r .= '</td></tr><tr><td class="fl"><b>Key</b></td><td><input type=password name=key size=8 maxlength=12 ></td></tr>
</table>
<div id="info">
'.$inf.'
</div>
</div>
</form>';
    } else {
        $r .= '<h2 class="error">This thread has reached its post limit</h2>';
    }
    } else {
        $r .= '<h2 class="error">The board is locked.</h2>';
    }
    }
        return $r;
}

function pagehead($op){
    $inf = file_get_contents(INFOFILE);
    $r = "";
    $r .= '<!doctype html><html><head>

<title>'.TITLE.'</title>
<script type="text/javascript">
function quote(i){
var t = document.getElementById("com");
t.value += ">>"+i+"\n";
return false;
}
</script>
</head>
<body>
<link href="'.URLROOT.CSSSTYLE.'.css" rel="stylesheet" type="text/css">
<p align=center>
<font color="#800000" size=5>
<b><SPAN>'.TITLE.'</SPAN></b></font>
<hr width="90%" size=1>';

    if($op > 0){
        if(REPLYBOX_TOP){
            $r .= postform($op);
        }
    } else {
        $r .= postform($op);
    }

    $r .= '<hr width="90%">';
    $r .= '<form action="'.URLROOT.SCRIPTNAME.'?mode=delete" method="POST">';
    return $r;
}

function all_posts_struct($posts){
    $op_posts = array();
	foreach($posts as $x){
		if($x['id'] != 0 && $x['thread'] == 0){
			array_push($op_posts, $x);
		}
	}

	foreach($op_posts as &$y){
		$y['replies'] = array();
		foreach($posts as $z){
			if($z['thread'] == $y['id']){
				array_push($y['replies'], $z);
			}
		}
	}

    $sortArray = array();
    foreach($op_posts as $o){
        foreach($o as $key=>$value){
            if(!isset($sortArray[$key])){
                $sortArray[$key] = array();
            }
            $sortArray[$key][] = $value;
        }
    }

    if(SORTBUMP)
        array_multisort($sortArray['bumptime'],SORT_DESC,$op_posts);
    else
        array_multisort($sortArray['time'],SORT_DESC,$op_posts);

    return $op_posts;
}

function footer(){
    return '<footer style="margin-top:20px;font-size:small;text-align:center;clear:both;">makiba, inspired by <a href="http://www.64ch.net/">64chan</a></footer>';
}

function generatepage($posts, $page){
    $op_posts = all_posts_struct($posts);

	if($page == 0){
		/* generate all of the /res/*.html pages */
		foreach($op_posts as $tp){
			$thread = pagehead($tp['id']);
			if(OLDSTYLE){
                $thread .= old_style_thread($tp, 1);
			}
            if(!REPLYBOX_TOP){
                $thread .= deleteform();
                $thread .= postform($tp['id']);
            } else {
                $thread .= deleteform();
            }
            $thread .= footer();
            file_put_contents(ROOT.THREADDIR.$tp['id'].".html", $thread);
		}
	}

    $z= pagehead(0);
    if(SHOWPOSTLIST){
        $z .= '<div id="postlist"><b>Post list: </b>';
        $i = 0;
        foreach($op_posts as $pp){
            $s = '';
            if($pp['subject'] == ''){
                if($pp['comment'] == ''){
                    $s = "(no subject)";
                } else {
                    $s = mb_substr($pp['comment'], 0, 10);
                }
            } else {
                $s = $pp['subject'];
            }
            $z .= '<a href="'.URLROOT.THREADDIR.$pp['id'].'.html">'.$s.'</a> ('.count($pp['replies']).')';
            if($i != count($op_posts)-1){
                $z .= ' - ';
            }
            $i++;
        }
        $z .= '</div>';
    }
	foreach(array_slice($op_posts, $page * PERPAGE, PERPAGE) as $post){
	    $z .= old_style_thread($post, 0);
	}
    $page_total = ceil(count($op_posts)/PERPAGE);

    $z .= deleteform();
    $z .= pageselect($page, $page_total);
    $z .= footer();
	file_put_contents(ROOT.$page.".html", $z);
	if($page == 0){
		file_put_contents(ROOT."index.html", $z);
	}
	if($page < $page_total-1) {
		generatepage($posts, $page+1);
	}
}

function writeposts($posts){
	$l = "";
	foreach($posts as $pinf) {
        if($pinf['id'] != 0){
            $l .= join("\x1f", array($pinf['id'],
                                     $pinf['thread'],
                                     $pinf['src'],
                                     $pinf['thumb'],
                                     $pinf['file'],
                                     $pinf['width'],
                                     $pinf['height'],
                                     $pinf['size'],
                                     $pinf['hash'],
                                     $pinf['name'],
                                     $pinf['email'],
                                     $pinf['comment'],
                                     $pinf['subject'],
                                     $pinf['key'],
                                     $pinf['ip'],
                                     $pinf['time'],
                                     $pinf['bumptime'],
                                     $pinf['admin']));
            $l .= "\x1e";
        }
    }
    file_put_contents(POSTSFILE, $l, LOCK_EX);

}

function insertpost($pinf){
    $l = "";
	$l .= join("\x1f", array($pinf['id'],
				$pinf['thread'],
				$pinf['src'],
				$pinf['thumb'],
				$pinf['file'],
				$pinf['width'],
				$pinf['height'],
				$pinf['size'],
				$pinf['hash'],
				$pinf['name'],
				$pinf['email'],
				$pinf['comment'],
				$pinf['subject'],
				$pinf['key'],
				$pinf['ip'],
				$pinf['time'],
                             $pinf['bumptime'],
    $pinf['admin']));
	file_put_contents(POSTSFILE, $l."\x1e", FILE_APPEND | LOCK_EX);
	if($pinf['email'] != "sage" && $pinf['thread'] != 0){
		$a = getposts();
		foreach($a as &$p) {
			if($p['id'] == $pinf['thread']){
				$p['bumptime'] = time();
			}
		}
		writeposts($a);
	}
	generatepage(getposts(), 0);
}

function getbadfiles(){
	return explode("\x1e", file_get_contents(BADFILESFILE));
}

function abort_error($s){
    echo '<font color=red size=5><b>'.$s.'</b></font>';
    die();
}

function newid() {
	$posts = explode("\x1e", file_get_contents(POSTSFILE));
	$maxid = 0;
	foreach($posts as $post){
		$p = explode("\x1f", $post);
		if((int)$p[0] > $maxid) { $maxid = (int)$p[0]; }
	}
	return $maxid+1;
}

function clean($s) {
    $s = safen($s);
	$s = htmlspecialchars($s, ENT_COMPAT, "UTF-8");
	return $s;
}

function safen($s){
   	$s = str_replace("\x1e", '\x1e', $s);
	$s = str_replace("\x1f", '\x1f', $s);
    return $s;
}

function ban_ip($ip) {
    $bans = fopen(BANFILE, "a");
    $result = fwrite($bans, $ip."\n");
    fclose($bans);
    return $result !== FALSE;
}



if(isset($_GET['mode'])){
	$mode = trim($_GET['mode']);

    if($mode == "regen"){
        generatepage(getposts(), 0);
        infopage("Board regeneration", "Regenerated.");
    }

    if($mode == "login"){
        if(isset($_POST['pw'])){
            if(login($_POST['pw'])){
                echo '<meta http-equiv="refresh" content="2; url='.URLROOT.SCRIPTNAME.'?mode=admin">Redirecting to admin page.';
            } else {
                infopage("Login failed", "Incorrect password.");
            }
        } else {
            infopage("Login", '<form method="POST" action="'.URLROOT.SCRIPTNAME.'?mode=login">Password: <input type="password" name="pw"> <input type="submit" value="Log in"></form>');
        }
    }

    if($mode == "admin"){
        if(!isset($_COOKIE['sess_id']) || !validate($_COOKIE['sess_id'])){
            infopage("Login failed", "Login could not be validated.");
        } else {
            $inf = file_get_contents(INFOFILE);
            $t = "<h3>New post</h3>";
            $t .= '<form action="'.URLROOT.SCRIPTNAME.'?mode=post" method="POST" enctype="multipart/form-data">';

            $t .= '<input type="hidden" name="thread" value="0">';

            $t .= '<div id="pb"><table><tr><td class="fl"><b>Name</b></td><td><input type=text name=name size="28"></td></tr>
<tr><td class="fl"><b>E-mail</b></td><td><input type=text name=email size="28"></td></tr>
<tr><td class="fl"><b>Subject</b></td><td><input type=text name=subject size="35"><input type=submit value="スレッドを立てる"></td></tr>
<tr><td class="fl"><b>Comment</b></td><td><textarea id="com" name=comment cols="48" rows="4" id="ftxa"></textarea></td></tr>
<tr><td class="fl"><b>添付File</b></td><td><input type=file name=img size="35">';
            if(ALLOWNOIMAGEOP){
                $t .= '[<label><input type=checkbox name="noimage" value="y">No image</label>]';
            }
            $t .= '</td></tr>
<tr><td class="fl"><b>Key</b></td><td><input type=password name=key size=8 maxlength=12 ></td></tr>
</table>
<div id="info">
'.$inf.'
</div>
</div>
</form>';
            $t .= '<h3>Post list</h3><form action="'.URLROOT.SCRIPTNAME.'?mode=delete" method="POST">';
            $op_posts = all_posts_struct(getposts());
            foreach($op_posts as $tp){
                if(OLDSTYLE){
                    $t .= old_style_thread($tp, 1);
                }
            }
            $t .= deleteform();
            infopage("Admin panel", $t);
        }
    }

    if($mode == "delete"){
        $deleted = array();
        $undeleted = array();
        $img_deleted = array();
        if(isset($_POST['deletekey']) && $_POST['deletekey'] != ""){
            if(!empty($_POST['del'])) {
                foreach($_POST['del'] as $d){
                    $x = get_post(intval($d));
                    if(!empty($x)){
                        if((strcmp($x['key'],clean($_POST['deletekey'])) == 0)
                           || (isset($_COOKIE['sess_id']) && validate($_COOKIE['sess_id']))){

                            if(isset($_POST['imageonly']) && strcmp($_POST['imageonly'], "y") == 0) {
                                delete_imageonly($x['id']);
                                array_push($img_deleted, $x['id']);
                            } else {
                                delete_post($x['id']);
                                array_push($deleted, $x['id']);
                            }
                        } else {
                            array_push($undeleted, $x['id']);
                        }
                    } else {
                        array_push($undeleted, $x['id']);
                    }
                }
            }  else {
            abort_error(ERR_NODELS);
            }
        } else {
            abort_error(ERR_NODELKEY);
        }
        generatepage(getposts(), 0);
        infopage("Deleted posts", "The following posts have been deleted: " . implode(", ", $deleted) . "<br>The following posts have their image deleted: " . implode(", ", $img_deleted) . "<br>The following posts have not been deleted: " . implode(", ", $undeleted));
    }


	if($mode == "post"){
        if(LOCKED){
            abort_error("This board is locked.");
        }
		$pinf = array();
		$pinf['id'] = 0;
		$pinf['thread'] = 0;
		$pinf['src'] = "";
		$pinf['thumb'] = "";
		$pinf['file'] = "";
		$pinf['width'] = 0;
		$pinf['height'] = 0;
		$pinf['size'] = 0;
		$pinf['hash'] = "";
		$pinf['name'] = "";
		$pinf['email'] = "";
		$pinf['comment'] = "";
		$pinf['subject'] = "";
		$pinf['key'] = "";
		$pinf['ip'] = $_SERVER['REMOTE_ADDR'];
		$pinf['time'] = 0;
		$pinf['bumptime'] = 0;

		if(!isset($_POST['thread'])){
			abort_error(ERR_NOTHREADID);
		} else {
			$tidi = intval($_POST['thread']);
			if($tidi == 0) { $op = 0; }
			else {
				if(postexists($tidi)) { $op = $tidi; }
				else { abort_error(ERR_NOTFOUND); }
			}
		}

                $ad = 0;
		if(isset($_POST['name']) && trim($_POST['name']) != ""){
            $_POST['name'] = trim($_POST['name']);
            if(!strstr($_POST['name'], '#')){
                $pinf['name'] = preg_replace('/![.-9A-Za-z]+$/', '!', clean($_POST['name']));;
            } else {
                $parts = explode('#', $_POST['name']);
                $n = preg_replace('/!+$/', '', clean($parts[0]));
                array_shift($parts);
                $t = implode('', $parts);
                $g = mktripcode($t);
                $pinf['name'] = $n . '!' . $g;
                if(strcmp($g, ADMINTRIP) == 0){
                    $ad = 1;
                }
            }
		} else {
			$pinf['name'] = ANONNAME;
		}

        if(ADMINOPONLY && !$ad && !$op) {
            abort_error(ERR_ADMINOPONLY);
        }

        if(!$ad){
            $cp = can_post($_SERVER['REMOTE_ADDR']);
            if(!$cp[0] && $op==0) { abort_error(ERR_THREADSOON); }
            if(!$cp[1] && $op>0) { abort_error(ERR_POSTSOON); }
            if(isbanned($pinf['ip'])) { abort_error(ERR_BANNED); }
            if(num_posts($tidi) > MAXREPLIES) { abort_error(ERR_FULLTHREAD); }
        }

		$data = array();
		if(isset($_FILES['img']) && (!isset($_POST['noimage']) || ($_POST['noimage'] != "y")) && is_uploaded_file($_FILES['img']['tmp_name'])){
            //print isset($_POST['noimage']);
			$loc = time().substr(microtime(),2,3);
			$pinf['file'] = basename($_FILES['img']['name']);
			if (!move_uploaded_file($_FILES['img']['tmp_name'], $loc.".tmp")) {
				abort_error(ERR_MOVEUPLOAD);
			}

			$fi = finfo_open(FILEINFO_MIME_TYPE);
			$pinf['size'] = filesize($loc.".tmp");

            if(!$ad){
                if(!in_array(finfo_file($fi, $loc.".tmp"), ALLOWEDTYPES)
                   || !in_array(mime_content_type($loc.".tmp"), ALLOWEDTYPES)
                   || !in_array(exif_imagetype($loc.".tmp"), ALLOWEDTYPES)) {
                    abort_error(ERR_BADTYPE);
                }

                if(in_array(hash_file("sha256", $loc.".tmp"), getbadfiles())) {
                    abort_error(ERR_BADFILE);
                }
            }

			$type = mime_content_type($loc.".tmp");

			if($pinf['size'] > MAXFSIZE && !$ad){
				abort_error(ERR_FILESIZE);
			}

			$imgsize = getimagesize($loc.".tmp");
            $width = $imgsize[0]; $height = $imgsize[1];
			if(($width > MAXWIDTH || $height > MAXHEIGHT) && !$ad) {
				abort_error(ERR_DIM);
			}

			if(in_array($type, array("image/jpeg", "image/gif", "image/png"))){
                $mw = ($op == 0) ? TWIDTH_OP : TWIDTH_REPLY;
                if (!extension_loaded('imagick')){
                    switch($type) {
                    case "image/jpeg":
                        $s = imagecreatefromjpeg($loc.".tmp");
                        break;
                    case "image/gif":
                        $s = imagecreatefromgif($loc.".tmp");
                        break;
                    case "image/png":
                        $s = imagecreatefrompng($loc.".tmp");
                        break;
                    }

                    $th = floor($height * ($mw/$width));
                    $v = imagecreatetruecolor($mw, $th);
                    imagecopyresized($v, $s, 0, 0, 0, 0, $mw, $th, $width, $height);

                    switch($type) {
                    case "image/jpeg":
                        imagejpeg($v, THUMBDIR.$loc.".jpg", 100);
                        rename($loc.".tmp", IMGDIR.$loc.".jpg");
                        $pinf['thumb'] = $loc.".jpg";
                        $pinf['src'] = $loc.".jpg";
                        break;
                    case "image/gif":
                        imagegif($v, THUMBDIR.$loc.".gif");
                        $pinf['thumb'] = $loc.".gif";
                        $pinf['src'] = $loc.".gif";
                        rename($loc.".tmp", IMGDIR.$loc.".gif");
                        break;
                    case "image/png":
                        imagepng($v, THUMBDIR.$loc.".png");
                        rename($loc.".tmp", IMGDIR.$loc.".png");
                        $pinf['thumb'] = $loc.".png";
                        $pinf['src'] = $loc.".png";
                        break;
                    }
                } else {
                    /* imagick is avail */
                    $image = $loc.".tmp";
                    $im = new Imagick();

                    /*** ping the image ***/
                    $im->pingImage($image);

                    /*** read the image into the object ***/
                    $im->readImage( $image );

                    /*** thumbnail the image ***/
                    $im->thumbnailImage( $mw, $mw, TRUE, FALSE );

                    /*** Write the thumbnail to disk ***/
                    if(strcmp($type, "image/jpeg") == 0){
                        $im->writeImage(THUMBDIR.$loc.".jpg" );
                        rename($loc.".tmp", IMGDIR.$loc.".jpg");
                        $pinf['thumb'] = $loc.".jpg";
                        $pinf['src'] = $loc.".jpg";
                    }
                    elseif(strcmp($type, "image/gif") == 0){
                        $im->writeImage(THUMBDIR.$loc.".gif" );
                        rename($loc.".tmp", IMGDIR.$loc.".gif");
                        $pinf['thumb'] = $loc.".gif";
                        $pinf['src'] =  $loc.".gif";
                    }
                    elseif(strcmp($type, "image/png") == 0){
                        $im->writeImage(THUMBDIR.$loc.".png" );
                        rename($loc.".tmp", IMGDIR.$loc.".png");

                        $pinf['thumb'] = $loc.".png";
                        $pinf['src'] =  $loc.".png";
                    }

                    $im->destroy();
                }
			} else {
				$pinf['thumb'] = THUMBICONS[$type];
			}
			$pinf['width'] = $width;
			$pinf['height'] = $height;
			$pinf['hash'] = hash_file("sha256", IMGDIR.$pinf['src']);
		} else {
            if(!isset($_FILES['img']) && !ALLOWNOIMAGEOP && !$ad){
                abort_error(ERR_NEEDIMAGE);
            }
        }

        if(!$ad){
            if(mb_strlen($pinf['email']) > MAXMAIL || mb_strlen($pinf['comment']) > MAXCOMMENT || mb_strlen($pinf['name']) > MAXNAME
               || mb_strlen($pinf['subject']) > MAXSUBJECT || mb_strlen($pinf['key']) > MAXKEY){
                abort_error(ERR_TOOLONG);
            }
        }

		if(isset($_POST['email'])){
			$pinf['email'] = clean($_POST['email']);
		} else {
			$pinf['email'] = "";
		}
		if(isset($_POST['comment']) && trim($_POST['comment']) != ""){
            if(!$ad)
                $pinf['comment'] = clean($_POST['comment']);
            else
                $pinf['comment'] = safen($_POST['comment']);
		} else {
			if((MUSTCOMMENTOP && $op) || (MUSTCOMMENTREPLY && !$op)){
				abort_error(ERR_MUSTCOMMENT);
			} else {
				$pinf['comment'] = "nc";
			}
		}
        $filters = explode("\n", file_get_contents(WORDFILTERFILE));
        foreach ($filters as $filter_line) {
            $filter = explode("\t", $filter_line);
            if (preg_match($filter[0], $pinf['comment']) === 1) {
                if ($filter[1] === "BAN") {
                    ban_ip($pinf['ip']);
                    abort_error(ERR_BANNEDWORD);
                } else if ($filter[1] === "DROP") {
                    abort_error(ERR_BANNEDWORD);
                } else {
                    preg_replace($filter[0], $filter[1], $pinf['comment']);
                }
            }
        }
		if(isset($_POST['subject'])){
			$pinf['subject'] = clean($_POST['subject']);
		} else {
			$pinf['subject'] = "";
		}
		$pinf['key'] = clean($_POST['key']);

        if(isset($_COOKIE['dk']))
            if(strcmp($_POST['key'], $_COOKIE['dk']) != 0){
                /* there's a new key so refresh it */
                unset($_COOKIE['dk']);
                setcookie("dk",$_POST['key'],time()+(60*60*24*7));
                $_COOKIES["dk"] = $_POST["key"];
            }
            else{
                setcookie("dk",$_POST['key'],time()+(60*60*24*7));
                $_COOKIES["dk"] = $_POST['key'];
            }

		/* remember to clean the key when comparing when a user wants to delete a post */
		$pinf['subject'] = clean($_POST['subject']);
		$pinf['thread'] = $op;
		$pinf['id'] = newid();

		$pinf['time'] = time();
        $pinf['bumptime'] = $pinf['time'];
        $pinf['admin'] = $ad;


        if(num_posts(0) > MAXTHREADS) {
            delete_last_thread();
        }


		insertpost($pinf);

        if($pinf['email'] == "noko"){
	    if($op>0) {
                echo '<meta http-equiv="refresh" content="2; url='.URLROOT.THREADDIR.$op.'.html#p'.$pinf['id'].'">';
            } else {
                echo '<meta http-equiv="refresh" content="2; url='.URLROOT.THREADDIR.$pinf['id'].'.html">';
            }
            if($pinf['src'] != ""){
                echo '<h2>'.$pinf['file'].' uploaded. Redirecting to the thread.';
            } else {
                echo '<h2>Comment posted. Redirecting to the thread.</h2>';
            }
        } else {
            echo '<meta http-equiv="refresh" content="2; url='.URLROOT.'0.html">';
            if($pinf['src'] != ""){
                echo '<h2>'.$pinf['file'].' uploaded.';
            } else {
                echo '<h2>Comment posted.</h2>';
            }
        }
    }
}
?>
</html>
