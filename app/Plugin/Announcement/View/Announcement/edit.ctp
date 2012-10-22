<?php
$this->extend('/Frame/block');

//$this->assign('title', "block_id:". $block_id);
?>
<textarea id="<?php echo($id); ?>_wysiwyg" class="nc_wysiwyg" rows="16" cols="70"><?php echo($content); ?></textarea>
<div style="text-align:center;padding:5px;">
<?php echo $this->Html->link("戻る", '/'.$block_type.'/'.$block_id.'/announcement/#'.$id, array('data-pjax' => '#'.$id)); ?>
</div>
<script>
$('#<?php echo($id); ?>_wysiwyg').focus();
</script>
