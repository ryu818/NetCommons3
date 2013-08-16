<?php if(!empty($languages) && count($languages) > 1): ?>
	<div class="language-outer">
		<?php
			$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
			$user_id = empty($user_id) ? null : $user_id;
			$langUrl = $this->Html->url(array('language' => $lang, $user_id));
			$options = array();
			foreach($languages as $key => $value) {
				$options[$this->Html->url(array('language' => $key, $user_id))] = __($value);
			}
			$settings = array(
				'id' => "user-language".$id,
				'class' => "language",
				'name' => "language",
				'value' => $langUrl,
				'label' => false,
				'div' => false,
				'type' =>'select',
				'options' => $options
			);
			echo $this->Form->input('language', $settings);
			echo $this->Form->hidden('activeLang' , array('name' => "activeLang", 'value' => $lang));
		?>
	</div>
	<script>
		$(function(){
			$('#user-language<?php echo $id; ?>').select2({
				minimumResultsForSearch:-1
			}).change( function(e){
				var lang = $(this).val();
				var url = lang;
				$.get(url, function(res) {
					<?php if($this->action == 'index'): ?>
					$('#<?php echo $id; ?>').html(res);
					<?php else: ?>
					$('#<?php echo $id; ?>').replaceWith(res);
					<?php endif; ?>
				});
			} );
		});
	</script>
<?php endif; ?>