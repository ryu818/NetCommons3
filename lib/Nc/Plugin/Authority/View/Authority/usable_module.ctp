<?php
/**
 * 権限管理 配置可能なモジュール画面
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
		$options = array('action' => 'confirm', 'data-ajax' => '#authority-list');
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
		$backUrl = array('action' => 'detail2', $authorityId);
		$backAttr = array('data-ajax' => '#authority-list', 'data-ajax-type' => 'post', 'data-ajax-serialize' => true);
	?>
	<?php
		$bufBackAttr = $backAttr;
		$bufBackAttr['class'] = 'bold';
		echo $this->Html->link($title, array('action' => 'edit', $authorityId), $bufBackAttr);
	?>
	&nbsp;&gt;&gt;&nbsp;
	<?php
		$bufBackAttr = $backAttr;
		$bufBackAttr['class'] = 'bold';
		echo $this->Html->link(__d('authority', 'Set level'), array('action' => 'set_level', $authorityId), $bufBackAttr);
	?>
	&nbsp;&gt;&gt;&nbsp;
	<?php
		echo $this->Html->link(__d('authority', 'Detail setting'), array('action' => 'detail', $authorityId), $bufBackAttr);
	?>
	&nbsp;&gt;&gt;&nbsp;
	<?php
		echo $this->Html->link(__d('authority', 'Detail setting (Part 2)'), $backUrl, $bufBackAttr);
	?>
	&nbsp;&gt;&gt;&nbsp;
	<h3 class="bold display-inline">
		<?php echo __d('authority', 'Usable modules'); ?>
	</h3>
	<div id="authority-usable-module-tab<?php echo($id); ?>">
		<ul>
			<?php if ($authority['Authority']['myportal_use_flag'] != NC_MYPORTAL_USE_NOT): ?>
			<li><a href="#authority-usable-module-myportal<?php echo($id); ?>"><?php echo __('Myportal');?></a></li>
			<?php endif; ?>
			<?php if ($authority['Authority']['private_use_flag'] != _OFF): ?>
			<li><a href="#authority-usable-module-private<?php echo($id); ?>"><?php echo __('Private room');?></a></li>
			<?php endif; ?>
		</ul>
		<?php if ($authority['Authority']['myportal_use_flag'] != NC_MYPORTAL_USE_NOT): ?>
		<div id="authority-usable-module-myportal<?php echo($id); ?>">
			<?php echo($this->element('usable_module', array('prefix' => 'Myportal', 'modules' => $modules, 'enroll_modules' => $myportal_enroll_modules))); ?>
		</div>
		<?php endif; ?>
		<?php if ($authority['Authority']['private_use_flag'] != _OFF): ?>
		<div id="authority-usable-module-private<?php echo($id); ?>">
			<?php echo($this->element('usable_module', array('prefix' => 'Private', 'modules' => $modules, 'enroll_modules' => $private_enroll_modules))); ?>
		</div>
		<?php endif; ?>
	</div>

	<?php
		$bufBackAttr = array('name' => 'back', 'class' => 'nc-common-btn', 'type' => 'button', 'data-ajax-url' => $this->Html->url($backUrl));
		$backAttr = array_merge($backAttr, $bufBackAttr);
		echo $this->Html->div('submit align-right',
			$this->Form->button(__('&lt;&lt;Back'), $backAttr).
			$this->Form->button(__('Next&gt;&gt;'), array('name' => 'next', 'class' => 'nc-common-btn', 'type' => 'submit')).
			$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'nc-common-btn', 'type' => 'button',
				'data-ajax' => '#'.$id, 'data-ajax-method' => 'inner', 'data-ajax-url' =>  $this->Html->url(array('action' => 'index', 'language' => $language))))
		);
		echo $this->element('hidden');
		echo $this->Form->end();
	?>
	<script>
	$(function(){
		$.Authority.initUsableModule('<?php echo $id; ?>');
	});
	</script>
</div>