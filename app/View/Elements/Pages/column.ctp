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
				$params = array('block_type' => 'active-blocks', 'block_id' => $block['Block']['id']);
				if(!empty($this->params['active_plugin']) && $block['Block']['id'] == $this->params['block_id']) {
					$title = $this->fetch('title');
					$title = (isset($title) && $title != '') ? $title : h($block['Block']['title']);
					$this->assign('title', $title);

					$params['plugin'] = $this->params['active_plugin'];
					$params['controller'] = empty($this->params['active_controller']) ? $params['plugin'] : $this->params['active_controller'];
					$params['action'] = empty($this->params['active_action']) ? '' : $this->params['active_action'];
					if ($this->params->is('post')) {
						$requestActionParam = array('data' => $this->params['data'], 'query' => $this->params['query'], 'return');
					} else {
						$requestActionParam = array('query' => $this->params['query'], 'return');
					}
				} else {
					$controller_arr = explode('_', $block['Block']['controller_action'], 2);
					$params['plugin'] = $params['controller'] = $controller_arr[0];
					if(isset($controller_arr[1])) {
						$params['action'] = $controller_arr[1];
					}
					$requestActionParam = array('return');
				}

				Configure::write(NC_SYSTEM_KEY.'.block', $block);
				Configure::write(NC_SYSTEM_KEY.'.page' , $page);
				if($block['Block']['controller_action'] == "group") {
					Configure::write(NC_SYSTEM_KEY.'.blocks', $blocks);
					$c = trim($this->requestAction($params, $requestActionParam));
				} else {
					//$url = '/active-blocks'.'/'.$block['Block']['id'].'/'.$block['Block']['controller_action'].'/';
					$c = trim($this->requestAction($params, $requestActionParam));

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
