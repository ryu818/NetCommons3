<?php
	$ret = array();
	$ret['page'] = $page_num;
	$ret['total'] = $total;
	foreach($blocks as $key => $val) {
		$activeClassColor = '';
		if($block_id == $val['Block']['id']) {
			$activeClassColor = "highlight";
		}

		$titlePostfix = '';
		if($val['Page']['room_id'] != $val['Content']['room_id']) {
			$titlePostfix .= '<span class="nc-content-shortcut-edit nc-block-header-shortcut-show"><span></span></span>';
		} elseif(!$val['Content']['is_master']) {
			$titlePostfix .= '<span class="nc-content-shortcut-edit nc-block-header-shortcut-edit"><span></span></span>';
		}

		// ブロック名称
		if($val['Block']['title'] == "{X-CONTENT}") {
			$val['Block']['title'] = $val['Content']['title'];
		}

		if($val['Authority']['hierarchy'] >= NC_AUTH_MIN_CHIEF) {
			$params = array(
				'permalink' => $val['Page']['permalink'],
				'block_type' => 'active-blocks',
				'block_id' => $val['Block']['id'],
				'#' => '_'.$val['Block']['id']
			);
			$controllerArr = explode('/', $val['Block']['controller_action'], 2);
			$params['plugin'] = $params['controller'] = $controllerArr[0];
			if(isset($controllerArr[1])) {
				$params['action'] = $controllerArr[1];
			}
			$urlParams = array(
				'permalink' => $val['Page']['permalink'],
				'plugin' => null,
				'controller' => 'pages',
				'action' => 'index',
				'#' => '_'.$val['Block']['id']
			);
			$ret['rows'][$key]['cell'][] = '<span class="nc-content-block-title '.$activeClassColor.'">'.$this->Html->link($val['Block']['title'],
				$params,
				array(
					'title' => $val['Block']['title'],
					'onclick' => '$.Content.referenceContent(event, this, "'.$this->Js->escape($val['Block']['title']).'", "' . $this->Js->escape($this->Html->url($urlParams)) . '");',
				)
			). $titlePostfix.'</span>';
		} else {
			$ret['rows'][$key]['cell'][] = $val['Block']['title'];
		}

		// ページ名称
		$ret['rows'][$key]['cell'][] = h($val['Page']['page_name']);

		// ルーム名称
		$ret['rows'][$key]['cell'][] = h($val['RoomPage']['page_name']);
	}
	echo $this->Js->object($ret);
?>