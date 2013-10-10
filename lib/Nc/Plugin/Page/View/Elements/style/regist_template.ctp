<?php
/**
 * 追加CSSテンプレート(登録時使用)
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Upload.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<?php foreach($data as $title => $csses): ?>
<?php if(isset($data[$title])): ?>
<?php if($title == 'a'): ?>
a,a:link {
<?php else: ?>
<?php echo $title;?> {
<?php endif; ?>
<?php foreach($csses as $name => $value): ?>
<?php if(!$value): ?>
<?php elseif($name == 'font-family'): ?>
	<?php echo $name;?>:'<?php echo $value;?>', 'Lucida Grande','Hiragino Kaku Gothic ProN', sans-serif;*font-size:small;
<?php elseif($name == 'background-image'): ?>
	<?php echo $name;?>:url('<?php echo $value;?>');
<?php else: ?>
	<?php echo $name;?>:<?php echo $value;?>;
<?php endif; ?>
<?php endforeach; ?>
}
<?php endif; ?>
<?php endforeach; ?>