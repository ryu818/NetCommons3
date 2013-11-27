<?php
/**
 * 権限管理 権限一覧画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.User.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<?php if (!$on_regist): ?>
<div data-width="660" class="outer">
<?php
	echo $this->Html->css(array('Authority.index/', 'plugins/flexigrid'));
	echo $this->Html->script(array('Authority.index/', 'plugins/flexigrid'));
?>
<?php endif; ?>
<div id="authority-list">
	<?php echo $this->element('language'); ?>
	<div class="top-description">
		<?php echo __d('authority', 'You can add, edit and delete authority in your NetCommons.');?>
	</div>
	<div class="add-btn-link-outer">
		<?php
			echo $this->Html->link(__d('authority', 'Add new authority'), array('action' => 'edit', 'language' => $language), array(
				'title' => __d('authority', 'Add new authority'),
				'class' => 'add-btn-link',
				'data-ajax' =>'#authority-list',
			));
		?>
	</div>
	<table id="authority-list-grid">
		<thead>
			<tr>
				<th scope="col" width="280"><?php echo(__d('authority', 'Authority name')); ?></th>
				<th scope="col" width="330"><?php echo(__('Manage')); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($authorities as $authority): ?>
			<tr>
				<td>
					<?php
					if(isset($authority['AuthorityLang']['name'])) {
						$authorityName = $authority['AuthorityLang']['name'];
					} else {
						$authorityName = $authority['Authority']['default_name'];
					}
					echo h($authorityName);
					?>
				</td>
				<td>
					<?php
						echo $this->Html->link(__('Edit'), array('action' => 'edit', 'language' => $language, $authority['Authority']['id']), array(
							'title' => __('Edit'),
							'data-ajax' =>'#authority-list',
						));
					?>
					<?php if (!$authority['Authority']['system_flag']): ?>
						&nbsp;｜&nbsp;
						<?php
							echo $this->Html->link(__('Delete'), array('action' => 'delete', 'language' => $language, $authority['Authority']['id']), array(
								'title' => __('Delete'),
								'data-ajax' =>'#'.$id,
								'data-ajax-method' =>'inner',
								'data-ajax-type' =>'post',
								'data-ajax-data' => '{"data[_Token][key]": "'.$this->params['_Token']['key'].'"}',
								'data-ajax-confirm' =>__('Deleting %s. <br />Are you sure to proceed?', $authorityName),
							));
						?>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<script>
	$(function(){
		$.Authority.init();
	});
	</script>
</div>
<?php if (!$on_regist): ?>
<?php
	echo $this->Html->div('btn-bottom',
		$this->Form->button(__('Close'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
		'onclick' => '$(\'#'.$id.'\').dialog(\'close\'); return false;'))
	);
?>
</div>
<?php endif; ?>