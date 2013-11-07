<?php
	if(!isset($block_title)) {
		// プラグイン上のテンプレートから呼ばれた場合
		$nc_not_active = Configure::read(NC_SYSTEM_KEY.'.nc_not_active');
		if(!isset($nc_not_active)) {
			Configure::write(NC_SYSTEM_KEY.'.nc_sub_title' , $title);
		}
	} else {
		// column.ctp、block_title.ctpから呼ばれた場合
		$title = $this->fetch('title');
		if(!isset($title) || $title == '') {
			$nc_sub_title = Configure::read(NC_SYSTEM_KEY.'.nc_sub_title');
			if(isset($nc_sub_title)) {
				$this->assign('title', h($nc_sub_title) . NC_TITLE_SEPARATOR . $block_title);
			} else {
				$this->assign('title', $block_title);
			}
		}
	}
?>