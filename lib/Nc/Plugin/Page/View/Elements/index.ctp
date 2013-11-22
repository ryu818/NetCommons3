<?php
/**
 * ページメニュー
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<?php
$ncUser = $this->Session->read(NC_AUTH_KEY.'.'.'User');
$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
if(isset($is_edit) && $is_edit == _ON){
	$setting = __d('page', 'Exit page editor');
	$tooltip_setting = '';
	$setting_class = 'nc-hmenu-setting-end-btn';
} else {
	$setting = __d('page', 'Switching on page editor');
	$tooltip_setting = __d('page', 'I can add pages, community, edit, and delete.');
	$setting_class = 'nc-hmenu-setting-btn';
	$is_edit = _OFF;
}
?>
<div class="nc-pages-setting-title nc-panel-color clearfix" data-pages-header="true">
	<?php echo(__d('page', 'Pages menu')); ?>
	<?php if(!empty($languages) && count($languages) > 1): ?>
	<div class="pages-menu-language-outer">
		<select id="pages-menu-language" class="language">
		<?php
			foreach($languages as $key => $value) {
				$selected = ($key == $lang) ? ' selected="selected"' : '';
				echo("<option value=\"".$this->Html->url(array('plugin' => 'page', 'controller' => 'page', 'action' => 'index', 'active_lang' => $key))."\"".$selected.">".h(__($value))."</option>\n");
			}
		?>
		</select>
	</div>
	<?php endif; ?>
	<?php if(isset($ncUser)): ?>
	<a id="pages-menu-edit-btn" class="nc-tooltip" title="<?php echo(h($setting)); ?>" data-tooltip-desc="<?php echo(h($tooltip_setting)); ?>" href="<?php echo($this->Html->url(array('plugin' => 'page', 'controller' => 'page', 'action' => 'index', '?' => array('is_edit' => !$is_edit)))); ?>" data-ajax="#nc-pages-setting-dialog">
		<span class="<?php echo($setting_class); ?>"></span>
	</a>
	<?php endif; ?>
	<?php
		echo $this->element('index/other_operation', array('copy_page_id' => $copy_page_id, 'copy_page' => (isset($copy_page) ? $copy_page : null)));
	?>
</div>
<?php if($is_edit): ?>
<div class="nc-pages-setting-menu nc-panel-color" data-pages-header="true">
	<?php
	if($is_add_community) {
		$class_postfix = ($active_tab == 0) ? ' pages-menu-btn-disable' : '';
		echo $this->Html->link(__d('page', 'Add community'), array('plugin' => 'page', 'controller' => 'page_menus', 'action' => 'add_community'),
			array('title' => __d('page', 'Add community'), 'id' => 'pages-menu-add-community-btn' ,
			'class' => 'pages-menu-btn'. $class_postfix,
			'data-ajax' => '#pages-menu-add-community-temp', 'data-ajax-type' => 'POST'));
	}
	 ?>
	<?php
	$class_postfix = (!$is_add) ? ' pages-menu-btn-disable' : '';
	echo $this->Html->link(__d('page', 'Add page'), array('plugin' => 'page', 'controller' => 'page_menus', 'action' => 'add'),
		array('title' => __d('page', 'Add page'), 'id' => 'pages-menu-add-btn' ,
		'class' => 'pages-menu-btn'. $class_postfix,
		'data-ajax' => '#pages-menu-add-temp', 'data-ajax-type' => 'POST'));
	echo $this->Form->hidden('token' , array('id' => "pages-menu-token", 'value' => $this->params['_Token']['key']));
	 ?>
</div>
<?php endif; ?>
<div id="pages-menu-tab" data-ajax-url="<?php echo $this->Html->url(array('plugin' => 'page', 'controller' => 'page', 'action' => 'index'));?>">
	<ul data-pages-header="true">
		<li><a href="#pages-menu-page"><span><?php echo(__d('page', 'Page list'));?></span></a></li>
		<li><a href="#pages-menu-community"><span><?php echo(__d('page', 'Community list'));?></span></a></li>
	</ul>

	<div id="pages-menu-page" class="nc-pages-setting-content">
		<div class="pages-menu-expand-all-outer">
		<?php
			echo $this->Form->button(__('Expand All'), array('class' => 'pages-menu-expand-all', 'name' => 'expand'));
			echo $this->Form->button(__('Collapse All'), array('class' => 'pages-menu-collapse-all', 'name' => 'collapse'));
		?>
		</div>
		<?php if($is_edit): ?>
		<?php
			$thread_num = 1;
		?>
		<ol class="pages-menu-list dd-list">
			<?php if(!empty($pages[NC_SPACE_TYPE_PUBLIC])): ?>
				<?php $parent_id = NC_TOP_PUBLIC_ID; ?>
				<?php echo($this->element('index/edit_page', array('pages' => $pages, 'menus' => $pages[NC_SPACE_TYPE_PUBLIC][$thread_num][$parent_id],
						'space_type' => NC_SPACE_TYPE_PUBLIC, 'page_id' => $page_id,
						'is_detail' => $is_detail, 'parent_page' => isset($parent_page) ? $parent_page : null,
						'community_params' => $community_params))); ?>
			<?php endif; ?>
			<?php if(!empty($pages[NC_SPACE_TYPE_MYPORTAL])): ?>
				<?php $parent_id = NC_TOP_MYPORTAL_ID;?>
				<?php echo($this->element('index/edit_page', array('pages' => $pages, 'menus' => $pages[NC_SPACE_TYPE_MYPORTAL][$thread_num][$parent_id],
						'space_type' => NC_SPACE_TYPE_MYPORTAL, 'page_id' => $page_id,
						'is_detail' => $is_detail, 'parent_page' => isset($parent_page) ? $parent_page : null,
						'community_params' => $community_params))); ?>
			<?php endif; ?>
			<?php if(!empty($pages[NC_SPACE_TYPE_PRIVATE])): ?>
				<?php $parent_id = NC_TOP_PRIVATE_ID;?>
				<?php echo($this->element('index/edit_page', array('pages' => $pages, 'menus' => $pages[NC_SPACE_TYPE_PRIVATE][$thread_num][$parent_id],
						'space_type' => NC_SPACE_TYPE_PRIVATE, 'page_id' => $page_id,
						'is_detail' => $is_detail, 'parent_page' => isset($parent_page) ? $parent_page : null,
						'community_params' => $community_params))); ?>
			<?php endif; ?>
		</ol>
		<?php else: ?>
		<?php
			/* $thread_num = 2; */
		?>
		<ol class="dd-list">
			<?php if(!empty($pages[NC_SPACE_TYPE_PUBLIC])): ?>
				<?php $thread_num = 2; $parent_id = $pages[NC_SPACE_TYPE_PUBLIC][1][NC_TOP_PUBLIC_ID][0]['Page']['id']; ?>
				<?php if(!empty($pages[NC_SPACE_TYPE_PUBLIC][$thread_num][$parent_id])){ echo($this->element('index/page', array('pages' => $pages, 'menus' => $pages[NC_SPACE_TYPE_PUBLIC][$thread_num][$parent_id], 'space_type' => NC_SPACE_TYPE_PUBLIC, 'page_id' => $page_id)));} ?>
			<?php endif; ?>
			<?php if(!empty($pages[NC_SPACE_TYPE_MYPORTAL])): ?>
				<?php $thread_num = 1; $parent_id = NC_TOP_MYPORTAL_ID; /*$pages[NC_SPACE_TYPE_MYPORTAL][1][NC_TOP_MYPORTAL_ID][0]['Page']['id'];*/?>
				<?php echo($this->element('index/page', array('pages' => $pages, 'menus' => $pages[NC_SPACE_TYPE_MYPORTAL][$thread_num][$parent_id], 'space_type' => NC_SPACE_TYPE_MYPORTAL, 'page_id' => $page_id))); ?>
			<?php endif; ?>
			<?php if(!empty($pages[NC_SPACE_TYPE_PRIVATE])): ?>
				<?php $thread_num = 1; $parent_id = NC_TOP_PRIVATE_ID; /*$pages[NC_SPACE_TYPE_PRIVATE][1][NC_TOP_PRIVATE_ID][0]['Page']['id'];*/?>
				<?php echo($this->element('index/page', array('pages' => $pages, 'menus' => $pages[NC_SPACE_TYPE_PRIVATE][$thread_num][$parent_id], 'space_type' => NC_SPACE_TYPE_PRIVATE, 'page_id' => $page_id))); ?>
			<?php endif; ?>
		</ol>
		<?php endif; ?>
	</div>
	<div id="pages-menu-community" class="nc-pages-setting-content">
		<div class="clearfix">
			<?php
				/* 参加コミュニティー、コミュニティー検索切替  */
				$serachText = isset($this->request->data['CommunitySearch']['text']) ? $this->request->data['CommunitySearch']['text'] : '';
				$serachDisclosedCommunities = !empty($this->request->data['CommunitySearch']['disclosed_communities']) ? true : false;
				$urlJoinedArr = $urlSearchArr = array('community_type' => 'joined', 'is_paginator' => _ON, '?' => array('is_edit' => $is_edit));
				$urlSearchArr['community_type'] = 'search';
				$urlSearchArr['search_communities_text'] = $serachText;
				$urlSearchArr['disclosed_communities'] = $serachDisclosedCommunities;
			?>
			<a data-ajax="#nc-pages-setting-dialog" class="nc-pages-community-type<?php if($community_type != 'search'): ?> nc-pages-community-type-current<?php endif; ?>" href="<?php echo $this->Html->url($urlJoinedArr); ?>" data-community-type="joined"><?php echo( __d('page', 'Communities have joined'));?></a>
			<a data-ajax="#nc-pages-setting-dialog" class="nc-pages-community-type<?php if($community_type == 'search'): ?> nc-pages-community-type-current<?php endif; ?>" href="<?php echo $this->Html->url($urlSearchArr); ?>" data-file-type="search"><?php echo(__d('page', 'Search communities'));?></a>
		</div>
		<?php if($community_type == 'search'): ?>
		<div class="nc-pages-community-search-outer nc-panel-color">
		<?php
			echo $this->Form->create('CommunitySearch', array('data-ajax' => '#nc-pages-setting-dialog'));
			echo $this->Form->input('text', array(
				'id' => 'nc-pages-setting-community-search-text',
				'type' => 'text',
				'class' => 'text nc-pages-community-search-text',
				'label' => false,
				'div' => false,
				'error' => array('attributes' => array(
					'selector' => true
				)),
				'value' => $serachText,
			));
			echo $this->Form->button(__('Search'), array(
				'type' => 'submit',
				'class' => 'common-btn-min',
			));
			/* 公開コミュニティーから検索 */
			echo $this->Form->input('disclosed_communities',array(
				'type' => 'checkbox',
				'value' => _ON,
				'checked' => $serachDisclosedCommunities,
				'label' => __d('page', 'Search from disclosed communities'),
			));
			echo $this->Form->end();
		?>
		</div>
		<?php endif; ?>
		<div class="pages-menu-expand-all-outer">
		<?php
			echo $this->Form->button(__('Expand All'), array('class' => 'pages-menu-expand-all', 'name' => 'expand'));
			echo $this->Form->button(__('Collapse All'), array('class' => 'pages-menu-collapse-all', 'name' => 'collapse'));
			$paginatorQueryOptions = array('url' => $urlSearchArr, 'data-ajax' => '#nc-pages-setting-dialog');
		?>
		</div>
		<div class="pages-menu-counter-outer">
			<?php echo $this->Paginator->counter(__('{:start} - {:end} of {:count}')); ?>
			<?php echo(__('Results per page')); ?>
			<select id="pages-menu-community-limit" name="limit">
				<?php foreach ($limit_select_values as $v): ?>
				<?php
					$limitUrl = array('plugin' => 'page', 'controller' => 'page', 'action' => 'index', 'limit' => $v);
					$limitUrl = array_merge($limitUrl, $urlSearchArr);
				?>
				<option value="<?php echo($this->Html->url($limitUrl)); ?>"<?php if($limit == $v) { echo(' selected="selected"');}?>><?php echo(__('%s cases', $v)); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php echo($this->element('/common/paginator', array('options' => $paginatorQueryOptions))); ?>
		<?php
			$thread_num = 1;
			$parent_id = NC_TOP_GROUP_ID;
		?>
		<?php if($is_edit): ?>
			<ol class="pages-menu-list dd-list">
				<?php if(!empty($pages_group[NC_SPACE_TYPE_GROUP])): ?>
					<?php /* $parent_id = 1; */ ?>
					<?php echo($this->element('index/edit_page', array('pages' => $pages_group, 'menus' => $pages_group[NC_SPACE_TYPE_GROUP][$thread_num][$parent_id],
							'space_type' => NC_SPACE_TYPE_GROUP, 'page_id' => $page_id,
							'is_detail' => $is_detail, 'parent_page' => isset($parent_page) ? $parent_page : null,
							'community_params' => $community_params))); ?>
				<?php endif; ?>
			</ol>
		<?php else: ?>
			<ol class="dd-list">
				<?php if(!empty($pages_group[NC_SPACE_TYPE_GROUP])): ?>
					<?php /* $parent_id = $pages_group[NC_SPACE_TYPE_GROUP][1][1][0]['id']; */ ?>
					<?php echo($this->element('index/page', array('pages' => $pages_group, 'menus' => $pages_group[NC_SPACE_TYPE_GROUP][$thread_num][$parent_id], 'space_type' => NC_SPACE_TYPE_GROUP, 'page_id' => $page_id))); ?>
				<?php endif; ?>
			</ol>
		<?php endif; ?>
		<?php echo($this->element('/common/paginator', array('options' => $paginatorQueryOptions))); ?>
	</div>
</div>
<?php
	echo $this->Html->css(array('plugins/jquery.nestable.css', 'Page.index/index.css'), null, array('inline' => true));
	echo $this->Html->script(array('plugins/jquery.nestable.js', 'Page.index/index.js'), array('inline' => true));
?>
<script>
$(function(){
	$('#pages-menu-tab').PageMenu(<?php echo($is_edit);?>, <?php echo($page_id);?>, <?php echo($active_tab);?>, <?php echo($sel_active_tab);?>, <?php if($this->request->is('post')) {echo 'true';} else{echo 'false';} ?>, <?php echo($copy_page_id);?><?php if(!empty($this->request->query['participant_page_id'])): ?>, <?php echo intval($this->request->query['participant_page_id']); ?><?php endif; ?>);
});
</script>