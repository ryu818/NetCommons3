<?php
/**
 * ページメニュー：編集後メニュー行
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<?php if(!empty($menus)): ?>
	<?php foreach ($menus as $page): ?>
		<?php
		if($page['PageAuthority']['hierarchy'] >= NC_AUTH_MIN_CHIEF) {
			// メニューの表示は親のルームで１つでも主担のものがあれば、非公開ページも表示させる。
			$buf_is_root_parent_chief = true;
		} else {
			$buf_is_root_parent_chief = false;
		}
		if($page['Page']['display_flag'] != NC_DISPLAY_FLAG_ON && (isset($is_root_parent_chief) && !$is_root_parent_chief) && !$buf_is_root_parent_chief) {
			// 非公開
			continue;
		}

		?>
		<?php echo($this->element('index/item', array('pages' => $pages, 'page' => $page, 'space_type' => $space_type,
			'page_id' => $page_id, 'is_child' => isset($is_child) ? $is_child : _OFF,
			'is_detail' => $is_detail, 'parent_page' => isset($parent_page) ? $parent_page : null,
			'community_params' => $community_params, 'is_root_parent_chief' => (isset($is_root_parent_chief) && $is_root_parent_chief) ? $is_root_parent_chief : $buf_is_root_parent_chief
		))); ?>
	<?php endforeach; ?>
<?php endif; ?>