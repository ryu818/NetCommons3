<div class="nc-diff-outer">
	<?php
		echo $this->Form->create(null, array('id' => 'FormApprove'.$id, 'data-ajax-inner' => '#'.$dialog_id));
	?>
	<?php if(!isset($pre_approval)): ?>
		<div class="nc-diff-outer">
			<?php
				$revisionTitle = $this->TimeZone->date($post_approval['Revision']['created'], __('Y-m-d H:i:s'));
				echo __('Post approval: %s', $revisionTitle);
			?>
		</div>
		<div class="nc-diff-outer">
			<?php echo(__('Author:')); ?>
			<?php /* TODO:後にリンクにする。 */ echo h($post_approval['Revision']['created_user_name']); ?>
		</div>
		<div class="nc-diff-outer">
			<?php echo h($post_approval['Revision']['content']); ?>
		</div>
	<?php elseif($diffText == ''): ?>
	<div class="nc-diff-outer-identical">
		<?php echo(__('This revision is identical.')); ?>
	</div>
	<?php else: ?>
	<table class="nc-diff-compare">
		<thead>
			<th scope="col" colspan="2">
				<?php
					$revisionTitle = $this->TimeZone->date($pre_approval['Revision']['created'], __('Y-m-d H:i:s'));
					echo __('Pre-approval: %s', $revisionTitle);
				?>
			</th>
			<th scope="col" colspan="2">
				<?php
					$revisionTitle = $this->TimeZone->date($post_approval['Revision']['created'], __('Y-m-d H:i:s'));
					echo __('Post approval: %s', $revisionTitle);
				?>
			</th>
		</thead>
		<tr>
			<th scope="col" colspan="2">
				<?php echo(__('Author:')); ?>
				<?php /* TODO:後にリンクにする。 */ echo h($post_approval['Revision']['created_user_name']); ?>
			</th>
			<th scope="col" colspan="2">
				<?php echo(__('Author:')); ?>
				<?php /* TODO:後にリンクにする。 */ echo h($post_approval['Revision']['created_user_name']); ?>
			</th>
		</tr>
		<?php echo($diffText); ?>
	</table>
	<?php endif; ?>
	<?php
	echo $this->Form->hidden('revision_id' , array('id' => 'RevisionRevisionId'. $id, 'name' => 'revision_id', 'value' => $post_approval['Revision']['id']));
	echo $this->Form->hidden('is_approve' , array('id' => 'RevisionIsApprove'. $id, 'name' => 'is_approve', 'value' => _ON));
/* TODO:「承認しない」->承認しない理由を記入者にメールでお知らせするほうが望ましい。未対応。 */
	if($isChief) {
		$approve = $this->Form->button(__('Approve'), array(
			'class' => 'nc-button nc-button-blue common-btn',
			'type' => 'submit',
		));
		$unapprove = $this->Form->button(__('Unapprove'), array(
			'class' => 'nc-button nc-button-red common-btn',
			'type' => 'button',
			'onclick' => "$('#RevisionIsApprove".$id."').val(0);$(this.form).submit();"
		));
	} else {
		$approve = '';
		$unapprove = '';
	}
	echo $this->Html->div('btn-bottom',
		$approve.
		$unapprove.
		$this->Form->button(__('Close'),
			array(
				'name' => 'close', 'class' => 'common-btn', 'type' => 'button',
				'onclick' => '$(\'#'.$dialog_id.'\').dialog(\'close\'); return false;'
			)
		)
	);
	echo $this->Form->end();
	echo $this->Html->script(array('dialogs/approve'));
	echo $this->Html->css(array('revisions/revisions'));
	?>
	<script>
		$(function(){
			$('#FormApprove<?php echo($id); ?>').Approve('<?php echo($id);?>');
		});
	</script>
</div>