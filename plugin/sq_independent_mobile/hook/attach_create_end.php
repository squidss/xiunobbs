<?php exit;
    $notSess = param('not_sess'); // not_sess表示这个文件不应该存到原来的SESSION['tmp_files']里面
    if ($notSess) {
        empty($_SESSION['tmp_files_sq']) AND $_SESSION['tmp_files_sq'] = array();
        $sqN = count($_SESSION['tmp_files_sq']);
        $attach['aid'] = '_'.$sqN; // 将本aid改为我的
        $attach['path'] = $tmpfile;

        $_SESSION['tmp_files_sq'][$sqN] = $attach;

        unset($attach['path']);
        unset($_SESSION['tmp_files'][$n]); // 如果不存到session里面，把session对应的值去掉
    }