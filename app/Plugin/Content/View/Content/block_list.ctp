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

		if($val['Content']['shortcut_type'] == NC_SHORTCUT_TYPE_SHOW_ONLY) {
			$titlePostfix .= '<span class="nc-content-shortcut nc-block-header-shortcut-show"><span></span></span>';
		} elseif($val['Content']['shortcut_type'] == NC_SHORTCUT_TYPE_SHOW_AUTH) {
			$titlePostfix .= '<span class="nc-content-shortcut nc-block-header-shortcut-edit"><span></span></span>';
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
			$params = array_merge($params, $this->Common->explodeControllerAction($val['Block']['controller_action']));

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