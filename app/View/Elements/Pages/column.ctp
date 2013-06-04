<?php
	$nc_mode = $this->Session->read(NC_SYSTEM_KEY.'.'.'mode');
	$ret = array();
	$exist_flag = false;
	if(isset($blocks[$parent_id])) {
		foreach ($blocks[$parent_id] as $col => $block_col) {
			foreach ($block_col as $row => $block) {

				if(empty($block['Block']['hierarchy'])) {
					$block['Block']['hierarchy'] = $page['Authority']['hierarchy'];
				}
				$params = array('block_type' => 'active-blocks', 'block_id' => $block['Block']['id']);
				$requestActionOptions = array('return', 'requested' => _OFF);	// Tokenがrequested=1の場合、セットされないため1をセット
				if(!empty($this->params['active_plugin']) && $block['Block']['id'] == $this->params['block_id']) {
					$is_active = true;
					Configure::delete(NC_SYSTEM_KEY.'.nc_not_active');
					$params['plugin'] = $this->params['active_plugin'];
					$params['controller'] = empty($this->params['active_controller']) ? $params['plugin'] : $this->params['active_controller'];
					$params['action'] = empty($this->params['active_action']) ? '' : $this->params['active_action'];
					$requestActionOptions['query'] = $this->params->query;
					$requestActionOptions['named'] = $this->params->named;
					$requestActionOptions['pass'] = $this->params->pass;
					if ($this->request->is('post')) {
						$requestActionOptions['data'] = $this->request->data;
					}
				} else {
					$is_active = false;
					Configure::write(NC_SYSTEM_KEY.'.nc_not_active' , true);
					$params = array_merge($params, $this->Common->explodeControllerAction($block['Block']['controller_action']));
				}

				Configure::write(NC_SYSTEM_KEY.'.block', $block);
				Configure::write(NC_SYSTEM_KEY.'.page' , $page);
				if($block['Block']['controller_action'] == "group") {
					Configure::write(NC_SYSTEM_KEY.'.blocks', $blocks);
				}

				$c = trim($this->requestAction($params, $requestActionOptions));
				if($is_active) {
					$block_title = $this->fetch('block_title');
					$block_title = (isset($block_title) && $block_title != '') ? $block_title : h($block['Block']['title']);
					$this->element('Pages/title_assign', array('block_title' => $block_title));
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
		<div<?php if(!empty($id_name)) { echo(' id="'.$id_name.'"');} ?> class="nc-column table-cell<?php if(!empty($class_name)){echo(' '.$class_name);}?>"<?php if(!empty($attr)){echo(' '.$attr);}?>>
			<?php foreach ($block_col as $row => $block): ?>
				<?php /* ブロック */ ?>
				<?php echo($ret[$col][$row]); ?>
			<?php endforeach; ?>
		</div>
		<?php endforeach; ?>
	<?php else: ?>
		<div class="nc-column table-cell">
		</div>
	<?php endif; ?>
<?php endif; ?>
