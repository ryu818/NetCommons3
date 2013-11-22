<div id="nc-content-edit-top<?php echo($id); ?>-<?php echo($content['Content']['id']); ?>">
	<fieldset class="form">
		<?php
			echo $this->Form->create('Content', array(
				'id' => 'FormContentEdit'.$id.'-'.$content['Content']['id'],
				'data-ajax' => '#nc-content-edit-top'.$id.'-'.$content['Content']['id']
			));
		?>
		<ul class="nc-lists">
			<li>
				<dl>
					<dt>
						<?php
							echo $this->Form->label('Content.title', __('Content name'));
						?>
						<span class="require"><?php __('*'); ?></span>
					</dt>
					<dd>
						<?php
							$settings = array(
								'type' => 'text',
								'value' => $content['Content']['title'],
								'label' => false,
								'div' => false,
								'maxlength' => NC_VALIDATOR_TITLE_LEN,
								'class' => 'nc-content-edit-title',
								'size' => 25,
								'error' => array('attributes' => array(
									'selector' => true
								))
							);
							echo $this->Form->input('Content.title', $settings);
						?>
					</dd>
				</dl>
			</li>
			<li>
				<dl>
					<dt>
						<?php
							echo $this->Form->label('Content.display_flag', __('Publishing setting'));
						?>
					</dt>
					<dd>
						<?php
							$settings = array(
								'value' => ($content['Content']['display_flag']) ? _ON : _OFF,
								'label' => false,
								'div' => false,
								'type' =>'select',
								'class' => 'nc-content-edit-display-flag',
								'options' => array(
									_ON => __('Public'),
									_OFF => __('Private')
								),
							);
							echo $this->Form->input('Content.display_flag', $settings);
						?>
					</dd>
				</dl>
			</li>

		</ul>
		<?php
			echo $this->Html->div('submit',
				$this->Form->button(__('Ok'), array('name' => 'ok', 'class' => 'nc-common-btn', 'type' => 'submit')).
				$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'nc-common-btn', 'type' => 'button',
					'onclick' => '$(\'#nc-content-edit-dialog'.$id.'-'. $content['Content']['id'] .'\').dialog(\'close\'); return false;'))
			);
		?>
		<?php if(isset($success) && $success): ?>
			<script>
				$(function(){
					$('[name=cancel]', $('#FormContentEdit<?php echo($id); ?>-<?php echo($content['Content']['id']); ?>')).click();
					$.Common.reloadBlock(null, 'nc-content-top<?php echo($id); ?>', null, $('#FormContent<?php echo($id); ?>').attr('action'));
					if($('#<?php echo($id); ?>').get(0)) {
						$.Common.reloadBlock(null, '<?php echo($id); ?>');
					}
				});
			</script>
		<?php endif; ?>
		<?php
			echo $this->Form->end();
		?>
	</fieldset>
</div>