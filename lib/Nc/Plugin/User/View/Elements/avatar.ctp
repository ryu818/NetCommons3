<div class="user-avatar">
<?php
	$imageUrl = 'user/img/avatar.gif';
	if (!empty($avatar) && is_string($avatar)) {
		$imageUrl = 'nc-downloads/'.$avatar;
		// 画像のパスが同じ場合に、画像が再描画されないため、乱数付与
		$imageUrl .= '?'. mt_rand();
	}
	if(!isset($item['ItemLang']['name'])) {
		$item['ItemLang']['name'] = $item['Item']['default_name'];
	}
?>
<img src="<?php echo $this->Html->url('/', true).$imageUrl ?>" alt="<?php echo h($item['ItemLang']['name'])?>" title="<?php echo h($item['ItemLang']['name'])?>" data-avatar="<?php if(isset($avatar)){echo $avatar;} ?>" style="visibility:hidden;" />
</div>
<?php echo $this->Form->error($name); ?>
<?php if($this->request->is('post')): ?>
<script>
$(function(){
	$('input[name="data[_Token][key]"]:first', $('#Form<?php echo $id;?>')).val('<?php echo $this->params['_Token']['key']; ?>');
});
</script>
<?php endif; ?>