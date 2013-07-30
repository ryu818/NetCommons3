<?php
/**
 * システム管理 設定画面共通 ヘッダー
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.User.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<div id="<?php echo $id ?>">
	<?php echo $this->element('language'); ?>
	<?php
		echo $this->Form->create('ConfigRegist', array('data-ajax' => '#'.$id, 'data-ajax-method' =>'inner'));
	?>