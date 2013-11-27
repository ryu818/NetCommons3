<?php
/**
 * 権限管理 権限編集・権限追加画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.User.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<div id="authority-list" class="authority-edit ui-tabs-panel ui-widget-content ui-corner-all">
	<?php
		$authorityId = null;
		$options = array('action' => 'detail', 'data-ajax' => '#authority-list');
		if(!empty($authority['Authority']['id'])) {
			$options['url'] = array($authority['Authority']['id']);
			$authorityId = $authority['Authority']['id'];
		}
		echo $this->Form->create('Authority', $options);

		if(empty($authorityId)) {
			$title = __d('authority', 'Add new authority');
		} else {
			$title = __d('authority', 'Edit authority');
		}
		$title .= ' ['.$authority['Authority']['default_name'].']';
		$backUrl = array('action' => 'edit', $authorityId);
		$backAttr = array('data-ajax' => '#authority-list', 'data-ajax-type' => 'post', 'data-ajax-serialize' => true);
		$disabled = 'false';
		if(intval($authority['Authority']['hierarchy']) <= NC_AUTH_GUEST || intval($authority['Authority']['hierarchy']) >= NC_AUTH_MIN_ADMIN) {
			$disabled = 'true';
		}
	?>
	<?php
		$bufBackAttr = $backAttr;
		$bufBackAttr['class'] = 'bold';
		echo $this->Html->link($title, $backUrl, $bufBackAttr);
	?>
	&nbsp;&gt;&gt;&nbsp;
	<h3 class="bold display-inline">
		<?php echo __d('authority', 'Set level'); ?>
	</h3>
	<div class="top-description">
		<?php echo __d('authority', 'Please set the classification(relationship among authorities).<br />After setting, please click [Next].'); ?>
	</div>
	<?php echo __d('authority', 'Classification'); ?>&nbsp;:&nbsp;
	<input id="authority-level<?php echo($id); ?>" name="data[Authority][add_hierarchy]" type="text" class="authority-level" readonly="readonly" />
	<div id="authority-slider<?php echo($id); ?>" class="authority-slider">
	</div>
	<?php echo $this->Form->error('Authority.hierarchy'); ?>
	<div class="note">
		<?php echo __d('authority', 'The scope of classification is 1~100.'); ?>
	</div>
	<?php
		$bufBackAttr = array('name' => 'back', 'class' => 'common-btn', 'type' => 'button', 'data-ajax-url' => $this->Html->url($backUrl));
		$backAttr = array_merge($backAttr, $bufBackAttr);
		echo $this->Html->div('submit align-right',
			$this->Form->button(__('&lt;&lt;Back'), $backAttr).
			$this->Form->button(__('Next&gt;&gt;'), array('name' => 'next', 'class' => 'common-btn', 'type' => 'submit')).
			$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
				'data-ajax' => '#'.$id, 'data-ajax-method' => 'inner', 'data-ajax-url' =>  $this->Html->url(array('action' => 'index', 'language' => $language))))
		);
		echo $this->element('hidden');
		echo $this->Form->end();
	?>
	<script>
	$(function(){
		$.Authority.initSetLevel('<?php echo $id; ?>', <?php echo intval($authority['Authority']['hierarchy']); ?>, <?php echo $disabled; ?>);
	});
	</script>
</div>