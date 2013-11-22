<?php
/**
 * ページメニュー：編集前メニュー行
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
			if($page['Page']['display_flag'] != NC_DISPLAY_FLAG_ON && $page['PageAuthority']['hierarchy'] < NC_AUTH_MIN_CHIEF) {
				// 非公開
				continue;
			}
			$active_lang = $this->Session->read(NC_SYSTEM_KEY.'.page_menu.pre_lang');
			$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
			$parameter = '';
			if(isset($active_lang) && $active_lang != $lang) {
				$parameter = '?lang='.$lang;
			}
			$class = $this->element('index/init_page', array('page' => $page, 'is_edit' => _OFF));
			$next_thread_num = $page['Page']['thread_num']+1;

			if($page['Page']['display_flag'] == NC_DISPLAY_FLAG_OFF) {
				$class .= ' nonpublic';
			} else if(!empty($page['Page']['display_to_date']) && $page['PageAuthority']['hierarchy'] >= NC_AUTH_MIN_CHIEF) {
    			$class .= ' to-nonpublic';
			}
			$tooltip_title = '';
			if($page['PageAuthority']['hierarchy'] >= NC_AUTH_MIN_CHIEF) {
				$tooltip_title = $this->TimeZone->getPublishedLabel($page['Page']['display_from_date'], $page['Page']['display_to_date']);
				if($tooltip_title != '') {
					$tooltip_title = ' title="' . $tooltip_title . '"';
				}
			}
			if($class == 'pages-menu-handle-community-light') {
				$url = $this->webroot. $page['Page']['permalink'].$parameter;
			} else {
				$url = $this->webroot. $page['Page']['permalink'].$parameter;
			}
		?>
		<li id="pages-menu-item-<?php echo(h($page['Page']['id'])); ?>" class="dd-item<?php if($tooltip_title != ''): ?> nc-tooltip<?php endif; ?>" data-id="<?php echo(h($page['Page']['id'])); ?>"<?php echo($tooltip_title); ?>>
			<div class="pages-menu-handle <?php echo($class); ?><?php if($page['Page']['id'] == $page_id): ?> highlight<?php endif; ?><?php if($page['Page']['thread_num'] == 1): ?> pages-menu-handle-top<?php endif; ?>">
			<?php if($class == 'pages-menu-handle-community-light' || $class == 'pages-menu-handle-community-light pages-menu-handle-community-light-topnode'): ?>
			<?php
				$communityInfParams = array('plugin' => 'page', 'controller' => 'page_menus', 'action' => 'community_inf', $page['Page']['root_id']);
			?>
			<a id="nc-hmenu-community-photo" class="nc-tooltip" data-ajax-effect="fold" data-ajax-force="true" data-ajax-dialog-options='{"title" : "<?php echo($this->Js->escape(__d('page', '[%s] Community information', $page['Page']['page_name'])));?>"}' data-ajax="#pages-menu-community-inf" data-ajax-dialog="true" title="<?php echo(__d('page', '[%s] Community information', $page['Page']['page_name'])); ?>" href="<?php echo($this->Html->url($communityInfParams)); ?>">
			<?php else: ?>
			<a href="<?php echo($url); ?>" title="<?php echo(h($page['Page']['page_name'])); ?>">
			<?php endif; ?>
				<?php echo(h($page['Page']['page_name'])); ?>
			</a>
			<?php if($page['Page']['display_flag'] == NC_DISPLAY_FLAG_OFF): ?>
				<span class="nonpublic-lbl"><?php echo(__('(Private)')); ?></span>
			<?php endif; ?>
			</div>
			<?php if(!empty($pages[$space_type][$next_thread_num][$page['Page']['id']])): ?>
				<ol class="dd-list">
					<?php echo($this->element('index/page', array('pages' => $pages, 'menus' => $pages[$space_type][$next_thread_num][$page['Page']['id']], 'page_id' => $page_id, 'space_type' => $space_type))); ?>
				</ol>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
<?php endif; ?>