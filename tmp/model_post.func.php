<?php



// ------------> 最原生的 CURD，无关联其他数据。

// 只用传 message, message_fmt 自动生成
function post__create($arr, $gid) {
	
	
	post_message_fmt($arr, $gid);
	
	
	
	$r = db_insert('post', $arr);
	
	return $r;
}

function post__update($pid, $arr) {
	
	$r = db_update('post', array('pid'=>$pid), $arr);
	
	return $r;
}

function post__read($pid) {
	
	$post = db_find_one('post', array('pid'=>$pid));
	
	return $post;
}

function post__delete($pid) {
	
	$r = db_delete('post', array('pid'=>$pid));
	

haya_post_like_delete_by_pid($pid);	


	return $r;
}

function post__find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	
	$postlist = db_find('post', $cond, $orderby, $page, $pagesize, 'pid');
	
	return $postlist;
}

// ------------> 关联 CURD，主要是强相关的数据，比如缓存。弱相关的大量数据需要另外处理。

// 回帖
function post_create($arr, $fid, $gid) {
	global $conf, $time;
	
	
$isSecret = param('is_secret');
$isSecret = $isSecret == 'on' ? 1 : 0;

$arr['is_secret'] = $isSecret;
	
	$pid = post__create($arr, $gid);
	if(!$pid) return $pid;
	
	$tid = $arr['tid'];
	$uid = $arr['uid'];

	// 回帖
	if($tid > 0) {
		
		// todo: 如果是老帖，不更新 lastpid
		thread__update($tid, array('posts+'=>1, 'lastpid'=>$pid, 'lastuid'=>$uid, 'last_date'=>$time));
		$uid AND user__update($uid, array('posts+'=>1));
	
		runtime_set('posts+', 1);
		runtime_set('todayposts+', 1);
		forum__update($fid, array('todayposts+'=>1));
	}
	
	//post_list_cache_delete($tid);
	
	// 更新板块信息。
	forum_list_cache_delete();
	
	// 关联附件
	$message = $arr['message'];
	attach_assoc_post($pid);
	
	// 更新用户的用户组
	user_update_group($uid);
	
	

// 如果是匿名回帖的话，隐藏该回帖信息
if ($arr['is_secret']) {
    $tablepre = $_SERVER['db']->tablepre;
    db_exec("UPDATE {$tablepre}thread SET `last_secret` = 1 WHERE tid = {$tid}");
}
	if(search_type() == 'fulltext') {
		$s = strip_tags($message);
		$words = search_cn_encode($s);
		db_create('post_search', array('pid'=>$pid, 'message'=>$words));
	}

	
	return $pid;
}

// 编辑回帖
function post_update($pid, $arr, $tid = 0) {
	global $conf, $user, $gid;

	$post = post__read($pid);
	if(empty($post)) return FALSE;
	$tid = $post['tid'];
	$uid = $post['uid'];
	$isfirst = $post['isfirst'];
	
	

	
	post_message_fmt($arr, $gid);
	
	
	
	$r = post__update($pid, $arr);
	
	attach_assoc_post($pid);
	
	

$message = $arr['message'];
if($isfirst) {
	if(search_type() == 'fulltext') {
		$thread = thread__read($tid);
		$s = strip_tags($thread['subject'].' '.$message);
		$words = search_cn_encode($s);
		db_replace('thread_search', array('tid'=>$tid, 'message'=>$words));
	}
} else {
	if(search_type() == 'fulltext') {
		$s = strip_tags($message);
		$words = search_cn_encode($s);
		db_replace('post_search', array('pid'=>$pid, 'message'=>$words));
	}
}


	return $r;
}

function post_read($pid) {
	
	$post = post__read($pid);
	post_format($post);
	
	return $post;
}

// 从缓存中读取，避免重复从数据库取数据，主要用来前端显示，可能有延迟。重要业务逻辑不要调用此函数，数据可能不准确，因为并没有清理缓存，针对 request 生命周期有效。
function post_read_cache($pid) {
	
	static $cache = array(); // 用静态变量只能在当前 request 生命周期缓存，要跨进程，可以再加一层缓存： memcached/xcache/apc/
	if(isset($cache[$pid])) return $cache[$pid];
	$cache[$pid] = post_read($pid);
	
	return $cache[$pid];
}

// $tid 用来清理缓存
function post_delete($pid) {
	global $conf;
	$post = post_read_cache($pid);
	if(empty($post)) return TRUE; // 已经不存在了。
	
	$tid = $post['tid'];
	$uid = $post['uid'];
	$thread = thread_read_cache($tid);
	$fid = $thread['fid'];
	
	
	if(!$post['isfirst']) {
		$sg_group = setting_get('sg_group');
		$uid AND user__update($uid, array('credits-'=>$sg_group['post_credits']));
	}

	db_delete('post_search', array('pid'=>$pid));

	
	if(!$post['isfirst']) {
		thread__update($tid, array('posts-'=>1));
		$uid AND user__update($uid, array('posts-'=>1));
		runtime_set('posts-', 1);
	} else {
		//post_list_cache_delete($tid);
	}
	
	($post['images'] || $post['files']) AND attach_delete_by_pid($pid);
	
	$r = post__delete($pid);

	// 更新最后的 lastpid
	if($r && !$post['isfirst'] && $pid == $thread['lastpid']) {
		thread_update_last($tid);
	}
	
	
	return $r;
}

// 此处有可能会超时
function post_delete_by_tid($tid) {
	
	$postlist = post_find_by_tid($tid);
	foreach($postlist as $post) {
		post_delete($post['pid']);
	}
	
	return count($postlist);
}

// 此处有可能会超时，并且导致统计不准确，需要重建统计数
function post_delete_by_uid($uid) {
	
	$r = db_delete('post', array('uid'=>$uid));
	
	return $r;
}

function post_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	
	$postlist = post__find($cond, $orderby, $page, $pagesize);
	$floor = 1;
	if($postlist) foreach($postlist as &$post) {
		$post['floor'] = $floor++;
		post_format($post);
	}
	
	return $postlist;
}

// 此处有缓存，是否有必要？
function post_find_by_tid($tid, $page = 1, $pagesize = 50) {
	global $conf;
	
	

$haya_post_info_config = GLOBALS('haya_post_info_config');

if ((isset($haya_post_info_config['show_see_him']) 
	&& $haya_post_info_config['show_see_him'] == 1)
	|| (isset($haya_post_info_config['show_see_first_floor']) 
	&& $haya_post_info_config['show_see_first_floor'] == 1)
	|| (isset($haya_post_info_config['show_post_sort']) 
	&& $haya_post_info_config['show_post_sort'] == 1)
) {

	$thread = GLOBALS('thread');

	if (!empty($thread)) {
		
		if ((isset($haya_post_info_config['show_see_him']) 
			&& $haya_post_info_config['show_see_him'] == 1)
			|| (isset($haya_post_info_config['show_see_first_floor']) 
			&& $haya_post_info_config['show_see_first_floor'] == 1)
		) {
			$haya_post_info_see_user = _REQUEST('user');
		} else {
			$haya_post_info_see_user = '';
		}
		
		if ((isset($haya_post_info_config['show_post_sort']) 
			&& $haya_post_info_config['show_post_sort'] == 1)
		) {
			$haya_post_info_post_default_sort = isset($haya_post_info_config['post_default_sort']) ? trim($haya_post_info_config['post_default_sort']) : '';
			$haya_post_info_orderby = _REQUEST('sort', $haya_post_info_post_default_sort);
		} else {
			$haya_post_info_orderby = 'asc';
		}

		if (strtolower($haya_post_info_orderby) == 'desc') {
			
			$haya_post_info_orderby = array('pid' => -1);
			
			$haya_post_info_cond = array('tid' => $tid);

			if (!empty($haya_post_info_see_user)) {
				$haya_post_info_cond['uid'] = intval($haya_post_info_see_user);
			}
			
			$postlist = post__find($haya_post_info_cond, $haya_post_info_orderby, $page, $pagesize);
			
			if ($page == 1) {
				$first_thread = post__read($thread['firstpid']);
				$postlist += array($thread['firstpid'] => $first_thread);
			}
			
			if (!empty($postlist)) {
				$floor = $thread['posts'] - ($page - 1) * $pagesize + 1;
				foreach ($postlist as & $post) {
					$post['floor'] = $floor--;
					post_format($post);
				}
			}
			
			return $postlist;	

		} elseif (!empty($haya_post_info_see_user)) {
			$haya_post_info_cond = array('tid' => $tid, 'uid' => intval($haya_post_info_see_user));
			
			$postlist = post__find($haya_post_info_cond, array('pid' => 1), $page, $pagesize);
			if ($page == 1) {
				$first_thread = post__read($thread['firstpid']);
				$postlist += array($thread['firstpid'] => $first_thread);
			}
			
			if (!empty($postlist)) {
				$floor = ($page - 1) * $pagesize + 1;
				foreach ($postlist as & $post) {
					$post['floor'] = $floor++;
					post_format($post);
				}
			}
			
			return $postlist;
		}
	}

}
	

	
	$postlist = post__find(array('tid'=>$tid), array('pid'=>1), $page, $pagesize);
	
	if($postlist) {
		$floor = ($page - 1)* $pagesize + 1;
		foreach($postlist as &$post) {
			$post['floor'] = $floor++;
			post_format($post);
		}
	}
	
	
	return $postlist;
}

// <img src="/view/img/face/1.gif"/>
// <blockquote class="blockquote">
function user_post_message_format(&$s) {
	if(xn_strlen($s) < 100) return;
	$s = preg_replace('#<blockquote\s+class="blockquote">.*?</blockquote>#is', '', $s);
	$s = str_ireplace(array('<br>', '<br />', '<br/>', '</p>', '</tr>', '</div>', '</li>', '</dd>'. '</dt>'), "\r\n", $s);
	$s = str_ireplace(array('&nbsp;'), " ", $s);
	$s = strip_tags($s);
	$s = preg_replace('#[\r\n]+#', "\n", $s);
	$s = xn_substr(trim($s), 0, 100);
	$s = str_replace("\n", '<br>', $s);
}


/*
function post_list_cache_delete($tid) {
	
	global $conf;
	$r = cache_delete("postlist_$tid");
	
	return $r;
}*/

// ------------> 其他方法

function post_count($cond = array()) {
	
	$n = db_count('post', $cond);
	
	return $n;
}

function post_maxid() {
	
	$n = db_maxid('post', 'pid');
	
	return $n;
}

function post_safe_info($post) {
	
	unset($post['userip']);
	if(!empty($post['user'])) {
		$post['user'] = user_safe_info($post['user']);
	}
	
	return $post;
}

function post_find_by_pids($pids, $order = array('pid'=>-1)) {
	
	if(!$pids) return array();
	$postlist = db_find('post', array('pid'=>$pids), $order, 1, 1000, 'pid');
	if($postlist) foreach($postlist as &$post) post_format($post);
	
	return $postlist;
}


function post_highlight_keyword($str, $k) {
	
	$r = str_ireplace($k, '<span class="red">'.$k.'</span>', $str);
	
	return $r;
}

// 公用的附件模板，采用函数，效率比 include 高。
function post_file_list_html($filelist, $include_delete = FALSE) {
	if(empty($filelist)) return '';
	
	
	
	$s = '<fieldset class="fieldset">'."\r\n";
	$s .= '<legend>上传的附件：</legend>'."\r\n";
	$s .= '<ul class="attachlist">'."\r\n";
	foreach ($filelist as &$attach) {
		$s .= '<li aid="'.$attach['aid'].'">'."\r\n";
		$s .= '		<a href="'.url("attach-download-$attach[aid]").'" target="_blank">'."\r\n";
		$s .= '			<i class="icon filetype '.$attach['filetype'].'"></i>'."\r\n";
		$s .= '			'.$attach['orgfilename']."\r\n";
		$s .= '		</a>'."\r\n";
		
		$include_delete AND $s .= '		<a href="javascript:void(0)" class="delete ml-3"><i class="icon-remove"></i> '.lang('delete').'</a>'."\r\n";
		
		$s .= '</li>'."\r\n";
	};
	$s .= '</ul>'."\r\n";
	$s .= '</fieldset>'."\r\n";
	
	
	
	return $s;
}

function post_format(&$post) {
	global $conf, $uid, $sid, $gid, $longip;
	if(empty($post)) return;
	$post['create_date_fmt'] = humandate($post['create_date']);
	
	$user = user_read_cache($post['uid']);
	
	
	
	$post['username'] = array_value($user, 'username');
	$post['user_avatar_url'] = array_value($user, 'avatar_url');
	$post['user'] = $user ? $user : user_guest();
	!isset($post['floor']) AND  $post['floor'] = '';
	
	$thread = thread_read_cache($post['tid']);
	
	// 权限判断
	$post['allowupdate'] = ($uid == $post['uid']) || forum_access_mod($thread['fid'], $gid, 'allowupdate');
	$post['allowdelete'] = ($uid == $post['uid']) || forum_access_mod($thread['fid'], $gid, 'allowdelete');
	
	$post['user_url'] = url("user-$post[uid]".($post['uid'] ? '' : "-$post[pid]"));
	
	if($post['files'] > 0) {
		list($attachlist, $imagelist, $filelist) = attach_find_by_pid($post['pid']);
		$post['filelist'] = $filelist;
	} else {
		$post['filelist'] = array();
	}

	$post['classname'] = 'post';
	
	
if($post['is_secret']) {
    $post['username'] = '******';
	$post['user_avatar_url'] = 'view/img/avatar.png';
	$post['user'] = $user;
}

}

// 写入时格式化
function post_message_fmt(&$arr, $gid) {
	
	

	// 超长内容截取
	$arr['message'] = xn_substr($arr['message'], 0, 2028000);
	
	// 格式转换: 类型，0: html, 1: txt; 2: markdown; 3: ubb
	$arr['message_fmt'] = htmlspecialchars($arr['message']);
	
	// 入库的时候进行转换，编辑的时候，自行调取 message, 或者 message_fmt
	$arr['doctype'] == 0 && $arr['message_fmt'] = ($gid == 1 ? $arr['message'] : xn_html_safe($arr['message']));
	$arr['doctype'] == 1 && $arr['message_fmt'] = xn_txt_to_html($arr['message']);
	
	    // 对引用进行处理
    !empty($arr['quotepid']) && $arr['quotepid'] > 0 && $arr['message_fmt'] = $arr['message_fmt'] . post_quote($arr['quotepid']);
    return;
	
	// 对引用进行处理
	!empty($arr['quotepid']) && $arr['quotepid'] > 0 && $arr['message_fmt'] = post_quote($arr['quotepid']).$arr['message_fmt'];
}

// 获取内容的简介 0: html, 1: txt; 2: markdown; 3: ubb
function post_brief($s, $len = 100) {
	
	$s = strip_tags($s);
	$s = htmlspecialchars($s);
	$more = xn_strlen($s) > $len ? ' ... ' : '';
	$s = xn_substr($s, 0, $len).$more;
	
	return $s;
}

// 对内容进行引用
function post_quote($quotepid) {
	$quotepost = post__read($quotepid);
	if(empty($quotepost)) return '';
	$uid = $quotepost['uid'];
	$s = $quotepost['message'];
	
	    $s = post_brief($s, 100);
	$userhref = url("user-$uid");
	$user = user_read_cache($uid);
	$r = '<blockquote class="blockquote">
        <span class="text-muted">回复：&nbsp;</span>
		<a href="'.$userhref.'" class="text-small text-muted user">
			'.$user['username'].'
		</a><br>
		'.$s.'</blockquote>';
	
	return $r;
	
	$s = post_brief($s, 100);
	$userhref = url("user-$uid");
	$user = user_read_cache($uid);
	$r = '<blockquote class="blockquote">
		<a href="'.$userhref.'" class="text-small text-muted user">
			<img class="avatar-1" src="'.$user['avatar_url'].'">
			'.$user['username'].'
		</a>
		'.$s.'
		</blockquote>';
	
	return $r;
}


// 对 $threadlist 权限过滤
function post_list_access_filter(&$postlist, $gid) {
	global $conf, $forumlist;
	if(empty($postlist)) return;
	
	
	
	foreach($postlist as $pid=>$post) {
		$thread = thread__read($post['tid']);
		$fid = $thread['fid'];
		if(empty($forumlist[$fid]['accesson'])) continue;
		if($thread['top'] > 0) continue;
		if(!forum_access_user($fid, $gid, 'allowread')) {
			unset($postlist[$pid]);
		}
	}
	
}


/** 发帖里面的图片添加内容 */
function post_img_list_html($filelistSq) {
    if(empty($filelistSq)) return '';

    $filelistSq = arrlist_multisort($filelistSq, 'aid');

    $deleteIcon = '<span class="img-delete-icon" onclick="sqDeleteImg(this);"></span>';
    $html = '';
    foreach ($filelistSq as &$attach) {
        $html .= '<div class="sq-img-div" data-id="' . $attach['aid'] . '" style="background-image: url(' . $attach['url'] . ')">' . $deleteIcon . '</div>';
    }
    return $html;
}

/** 根据pid获得当前pid的图片 */
function get_imgs_by_postid($pid) {
    $imgs = db_find('attach', ['pid' => $pid], ['aid' => 1], 1, 5, '', ['aid', 'filename']);
    return $imgs;
}
// 此处有缓存，是否有必要？
function post_find_by_uid($uid, $page = 1, $pagesize = 50) {
	global $conf;
	
	
	
	$arrlist = db_find('post', array('uid'=>$uid), array('pid'=>-1), $page, $pagesize, '', array('pid'));
	$pids = arrlist_values($arrlist, 'pid');
	$postlist = post_find_by_pids($pids);
	$postlist = arrlist_multisort($postlist, 'pid', FALSE);
	
	foreach($postlist as $k=>&$post) {
		user_post_message_format($post['message_fmt']);
		$post['filelist'] = array();
		$post['floor'] = 0; // 默认
		$thread = thread_read_cache($post['tid']);
		$post['subject'] = $thread['subject'];
		// 干掉主题帖
		if($post['isfirst']) {
			//unset($postlist[$k]);
		}
	}
	
	
	return $postlist;
}

?>