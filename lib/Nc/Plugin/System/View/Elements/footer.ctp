<?php
/**
 * システム管理 設定画面共通 フッター
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.User.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
	<?php
		echo $this->Html->div('submit',
			$this->Form->button(__('Ok'), array('name' => 'ok', 'class' => 'nc-common-btn', 'type' => 'submit')).
			$this->Form->button(__('Reset'), array('name' => 'reset', 'class' => 'nc-common-btn', 'type' => 'reset'))
		);
		echo $this->Form->end();
	?>
</div>