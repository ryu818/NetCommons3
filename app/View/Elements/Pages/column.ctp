<?php
	$nc_mode = $this->Session->read(NC_SYSTEM_KEY.'.'.'mode');
	$ret = array();
	$exist_flag = false;
	if(isset($blocks[$parent_id])) {
		foreach ($blocks[$parent_id] as $col => $block_col) {
			foreach ($block_col as $row => $block) {
				if(empty($block['Block']['hierarchy'])) {
					$block['Block']['hierarchy'] = NC_AUTH_OTHER;
				}

				if(!empty($this->params['active_plugin']) && $block['Block']['id'] == $this->params['block_id']) {
					$active_controller = empty($this->params['active_controller']) ? $block['Block']['controller_action'].'/'.$this->params['active_action'] : $this->params['active_plugin']. '/'. $this->params['active_controller']. '/' . $this->params['active_action'];
					//$url = 'active-blocks'.'/'.$block['Block']['id'].'/'.$this->params['active_plugin'].'/'.$active_controller.'/';
					$url = '/active-blocks'.'/'.$block['Block']['id'].'/'.$active_controller;
				} else {
					$url = '/active-blocks'.'/'.$block['Block']['id'].'/'.$block['Block']['controller_action'].'/';
				}
				if($block['Block']['controller_action'] == "group") {
					$c = trim($this->requestAction($url, array('block' => $block, 'page' => $page, 'blocks' => $blocks, 'return')));
				} else {
					$c = trim($this->requestAction($url, array('block' => $block, 'page' => $page, 'return')));
				}
				if(preg_match(NC_DOCTYPE_STR, $c)) {
					// モジュール内部にエラー等、DOCTYPEから出力するものあり
					exit;
				}
				$ret[$col][$row] = $c;
				if(!empty($c)) {
					$exist_flag = true;
				}
			}
		}
	}
?>
<?php if((!empty($blocks[$parent_id]) && $exist_flag) || $nc_mode == NC_BLOCK_MODE): ?>
	<?php if(isset($blocks[$parent_id])): ?>
		<?php foreach ($blocks[$parent_id] as $col => $block_col): ?>
		<div<?php if(!empty($id_name)) { echo(' id="'.$id_name.'"');} ?> class="nc_column table_cell<?php if(!empty($class_name)){echo(' '.$class_name);}?>"<?php if(!empty($attr)){echo(' '.$attr);}?>>
			<?php foreach ($block_col as $row => $block): ?>
				<?php /* ブロック */ ?>
				<?php echo($ret[$col][$row]); ?>
			<?php endforeach; ?>
		</div>
		<?php endforeach; ?>
	<?php else: ?>
		<div class="nc_column table_cell">
		</div>
	<?php endif; ?>
<?php endif; ?>
