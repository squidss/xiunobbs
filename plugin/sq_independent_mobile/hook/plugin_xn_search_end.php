// @squid 判断如果有定义常量，使用对应目录下面的模板
	if (defined('SQ_MOBILE_PATH')) {
        /** 将图片地址添加到列表项里面 */
        foreach($threadlist as &$item) {
            if($item['images']) {
                $imageList = get_images_by_tid($item['tid'], true); // 获得缩略图
                $item['images_url'] = $imageList ? $imageList : []; 
            } else { // 如果没缩略图，看看有没有简介
                $desc = get_desc_by_tid($item['tid']);
                if($desc) {
                    $item['sq_desc'] = $desc;
                }
            }
        }

        $showSearchCond = true;

		$show_search = 2;
        include _include(APP_PATH . SQ_MOBILE_PATH . 'view/other_plugin/search.htm');

        return;
	}