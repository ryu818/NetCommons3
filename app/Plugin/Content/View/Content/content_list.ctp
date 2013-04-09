<?php
	$ret = array();
	$ret['page'] = $page_num;
	$ret['total'] = $total;
	foreach($contents as $key => $val) {
		$title = '';
		$activeClassColor = '';
		$titlePostfix = '';
		if(isset($val['Block']['id'])) {
			$activeClassColor = "nc-content-located";
			$title .= '<span class=\''.$activeClassColor.'\'>['.__d('content', 'Already located.').']</span>&nbsp;';
		}
		if(!$val['Content']['is_master']) {
			$title .= __d('content', 'Origin of content').':'.h($val['Page']['page_name']);

			$titlePostfix .= '<span class="nc-content-shortcut-edit nc-block-header-shortcut-edit"><span></span></span>';
			$confirm ='Deleting the only shortcut[%1$s]. Are you sure to proceed?';
		} else {
			$confirm ='Deleting the content[%1$s]. You can not be undone. Are you sure to proceed?';
		}
		if($title != '') {
			$title = ' title="' . $title . '"';
		}

		if(!empty($val['ActiveContent']['id'])) {
			$activeId = $val['ActiveContent']['id'];
			$activeClass = ' class="nc-content-title nc-tooltip highlight '.$activeClassColor.'"';
		} else {
			$activeId = 0;
			$activeClass = ' class="nc-content-title nc-tooltip '.$activeClassColor.'"';
		}

		// コンテンツ名称

		$settings = array(
			'id' => "nc-content-name".$val['Content']['id'].$id,
			'value' => $activeId,
			'div' => false,
			'type' =>'radio',
			'options' => array(
				$val['Content']['id'] => h($val['Content']['title'])
			),
			'onclick' => "$.Content.switchingContent(this);",
		);

		$ret['rows'][$key]['cell'][] = '<span'.$activeClass.$title.'>'.$this->Form->input('Content.id', $settings).$titlePostfix.'</span>';

		// 公開・非公開
		if($val['Content']['display_flag'] == NC_DISPLAY_FLAG_OFF)
			$display_flag = __('Private');
		else if($val['Content']['display_flag'] == NC_DISPLAY_FLAG_ON)
			$display_flag = __('Public');
		$ret['rows'][$key]['cell'][] = $display_flag;

		// 管理
		$adminStr = '';
		// 参照
		$params = array(
			'block_type' => 'active-contents',
			'content_id' => $val['Content']['id']
		);
		$params = array_merge($params, $this->Common->explodeControllerAction($val['Module']['controller_action']));
		$adminStr .= $this->Html->link(__('Reference'),
			$params,
			array(
				'title' =>__('Reference'),
				'onclick' => '$.Content.referenceContent(event, this, "'.$this->Js->escape($val['Content']['title']).'");',
			)
		);

		$adminStr .= "&nbsp;|&nbsp;";

		// 配置ブロック一覧
		if(isset($val['Block']['id'])) {
			$adminStr .= $this->Html->link(__d('content', 'List of blocks'),
				array('action' => 'block', $val['Content']['id']),
				array(
					'title' =>__d('content', 'List of blocks'),
					'data-ajax' =>'#nc-content-block-list-'.$id. '-' .$val['Content']['id'],
					'data-ajax-dialog' => true,
					'data-ajax-effect' => 'fold',
					'data-ajax-dialog-options' => '{"title" : "'.$this->Js->escape(__d('content', 'List of blocks[%1$s]', h($val['Content']['title']))).'","modal": true, "resizable": true, "autoResize": true, "width":640}',
					'data-ajax-effect' => 'fold'
				)
			);
			$adminStr .= "&nbsp;|&nbsp;";
		}

		// 編集
		$adminStr .= $this->Html->link(__('Edit'),
			array('action' => 'edit', $val['Content']['id']),
			array(
				'title' =>__('Edit'),
				'data-ajax' =>'#nc-content-edit-dialog'.$id. '-' .$val['Content']['id'],
				'data-ajax-dialog' => true,
				'data-ajax-effect' => 'fold',
				'data-ajax-dialog-options' => '{"title" : "'.$this->Js->escape(__d('content', 'Edit content[%1$s]', h($val['Content']['title']))).'","modal": true, "resizable": true, "autoResize": true, "width":440, "position":"mouse"}',
				'data-ajax-effect' => 'fold'
			)
		);
		$adminStr .= "&nbsp;|&nbsp;";



		// TODO:承認リンク未作成

		// 削除
		$adminStr .= $this->Html->link(__('Delete'),
			array('action' => 'delete', $val['Content']['id']),
			array(
				'title' =>__('Delete'), 'data-ajax-replace' => '#nc-content-top'.$id,
				'data-ajax-type' => 'POST',
				'data-ajax-confirm' => __d('content', $confirm, $val['Content']['title']),
			)
		);


		$ret['rows'][$key]['cell'][] = $adminStr;

		// 削除チェックボックス
		/*$settings = array(
			'id' => "nc-content-deletes".$val['Content']['id'].$id,
			'value' => _ON,
			'div' => false,
			'checked' => false,
			'label' => false,
			'type' => 'checkbox',
			'name' => 'data[DeleteContent]['.$val['Content']['id'].']',
		);
		$ret['rows'][$key]['cell'][] = $this->Form->input(null, $settings);*/
	}
	echo $this->Js->object($ret);
?>