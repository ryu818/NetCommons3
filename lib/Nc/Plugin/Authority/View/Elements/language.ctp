<?php if(!empty($languages) && count($languages) > 1): ?>
	<div class="nc-language-outer">
		<?php
			$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
			$langUrl = $this->Html->url(array('language' => $lang));
			$options = array();
			foreach($languages as $key => $value) {
				$options[$this->Html->url(array('language' => $key))] = __($value);
			}
			$settings = array(
				'id' => "authoriy-language".$id,
				'class' => "nc-language",
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
			$('#authoriy-language<?php echo $id; ?>').select2({
				minimumResultsForSearch:-1,
				width: 'element'
			}).change( function(e){
				var lang = $(this).val();
				var url = lang;
				$.get(url, function(res) {
					<?php if($this->action == 'index'): ?>
					$('#<?php echo $id; ?>').html(res);
					<?php else: ?>
					$('#authority-list').replaceWith(res);
					<?php endif; ?>
				});
			} );
		});
	</script>
<?php endif; ?>