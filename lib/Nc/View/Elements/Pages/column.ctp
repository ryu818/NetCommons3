<?php
	$ncMode = $this->Session->read(NC_SYSTEM_KEY.'.'.'mode');
	$ret = array();
	$existFlag = false;
	$isActiveColumn = false;
	if(!empty($this->params['column']) && $this->params['column'] == $column) {
		$isActiveColumn = true;
	}

	if($isActiveColumn && empty($this->params['block_id'])) {
		/* カラム全体表示 */
		$params = array(
			'block_type' => 'active-blocks',
			'block_id' => 0,
			'plugin' => $this->params['active_plugin'],
			'controller' => empty($this->params['active_controller']) ? $params['plugin'] : $this->params['active_controller'],
			'action' => empty($this->params['active_action']) ? '' : $this->params['active_action'],
		);
		// Tokenがrequested=1の場合、セットされないため1をセット
		$requestActionOptions = array(
			'return',
			'requested' => _OFF,
			'query' => $this->params->query,
			'named' => $this->params->named,
			'pass' => $this->params->pass,
		);
		if ($this->request->is('post')) {
			$requestActionOptions['data'] = $this->request->data;
		}
		Configure::delete(NC_SYSTEM_KEY.'.block');
		Configure::write(NC_SYSTEM_KEY.'.page' , $page);

		Configure::write(NC_SYSTEM_KEY.'.nc_active_column.0', $this->params['column']);

		$block_title = $this->fetch('block_title');
		$this->element('Pages/title_assign', array('block_title' => $block_title));
		echo $this->requestAction($params, $requestActionOptions);
		return;
	} else if(isset($blocks[$parent_id])) {
		foreach ($blocks[$parent_id] as $col => $block_col) {
			foreach ($block_col as $row => $block) {
				if($isActiveColumn && $block['Block']['id'] != $this->params['block_id']) {
					/* カラム全体表示のため、そのほかのブロックは非表示。 */
					$ret[$col][$row] = '';
					continue;
				}
				if(empty($block['Block']['hierarchy'])) {
					$block['Block']['hierarchy'] = $page['PageAuthority']['hierarchy'];
				}
				$params = array('block_type' => 'active-blocks', 'block_id' => $block['Block']['id']);
				$requestActionOptions = array('return', 'requested' => _OFF);	// Tokenがrequested=1の場合、セットされないため1をセット
				if(!empty($this->params['active_plugin']) && $block['Block']['id'] == $this->params['block_id']) {
					$isActive = true;
					if($isActiveColumn) {
						$activeCol = $col;
						Configure::write(NC_SYSTEM_KEY.'.nc_active_column.'.$block['Block']['id'], $this->params['column']);
					}

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
					$isActive = false;
					$params = array_merge($params, $this->Common->explodeControllerAction($block['Block']['controller_action']));
					$requestActionOptions['chktoken'] = false;
				}

				Configure::write(NC_SYSTEM_KEY.'.block', $block);
				Configure::write(NC_SYSTEM_KEY.'.page' , $page);
				if($block['Block']['controller_action'] == "group") {
					Configure::write(NC_SYSTEM_KEY.'.blocks', $blocks);
				}

				$c = trim($this->requestAction($params, $requestActionOptions));
				if($isActive) {
					$block_title = $this->fetch('block_title');
					$block_title = (isset($block_title) && $block_title != '') ? $block_title : h($block['Block']['title']);
					$this->element('Pages/title_assign', array('block_title' => $block_title, 'isActive' => $isActive));
				}
				if(preg_match(NC_DOCTYPE_STR, $c)) {
					// モジュール内部にエラー等、DOCTYPEから出力するものあり
					exit;
				}
				$ret[$col][$row] = $c;
				if(!empty($c)) {
					$existFlag = true;
				}
			}
		}
	}
?>
<?php if((!empty($blocks[$parent_id]) && $existFlag) || $ncMode == NC_BLOCK_MODE): ?>
	<?php if(isset($blocks[$parent_id])): ?>
		<?php foreach ($blocks[$parent_id] as $col => $block_col): ?>
		<?php if(!isset($activeCol) || $activeCol == $col): ?>
		<div<?php if(!empty($id_name)) { echo(' id="'.$id_name.'"');} ?> class="nc-column table-cell<?php if(!empty($class_name)){echo(' '.$class_name);}?>"<?php if(!empty($attr)){echo(' '.$attr);}?>>
			<?php foreach ($block_col as $row => $block): ?>
				<?php /* ブロック */ ?>
				<?php echo($ret[$col][$row]); ?>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
		<?php endforeach; ?>
	<?php else: ?>
		<div class="nc-column table-cell">
		</div>
	<?php endif; ?>
<?php endif; ?>
