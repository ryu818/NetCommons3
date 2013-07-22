<?php if(!empty($languages) && count($languages) > 1): ?>
	<div class="language-outer">
		<?php
			$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
			$langUrl = $this->Html->url(array('language' => $lang));
			$options = array();
			foreach($languages as $key => $value) {
				$options[$this->Html->url(array('language' => $key))] = __($value);
			}
			$settings = array(
				'id' => "system-language".$id,
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
			$('#system-language<?php echo $id; ?>').chosen({disable_search : true}).change( function(e){
				var lang = $(this).val();
				var url = lang;
				$.get(url, function(res) {
					$('#<?php echo $id; ?>').html(res);
				});
			} );
		});
	</script>
<?php endif; ?>