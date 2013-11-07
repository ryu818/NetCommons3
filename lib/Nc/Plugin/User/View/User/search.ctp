<?php
/**
 * 会員管理 会員絞り込み画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.User.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<div id="user-search-<?php echo $id;?>">
	<?php
		$options = array('onsubmit' => "$('#user-search-".$id."').parent().dialog('close');");
		if(isset($this->request->named['post_page_id'])) {
			// ページ設定
			$options['url'] = $this->Html->url(array(
				'plugin' => 'page',
				 'controller' => 'page_menus',
				'action' => 'participant',
				intval($this->request->named['post_page_id']),
				'#' => null,
			), true);
			$options['data-ajax'] = '#pages-menu-edit-participant-'.intval($this->request->named['post_page_id']);
		} else {
			$options['action'] = 'index';
			$options['data-ajax'] = '#user-init-tab-list';
			$options['data-ajax-method'] = 'inner';
		}

		echo $this->Form->create('User', $options);

		if(USER_SEARCH_ROW_NUM == 'auto') {
			// list毎の最大行数を求め、検索画面の一列に表示するitem数を決定する。
			$rets = array();
			$listMaxArr = array();
			$listNum = 0;
			$colNum = 0;
			$rowNum = 0;
			foreach ($items as $key => $item) {
				if(($listNum != intval($item['UserItem']['list_num']) - 1) || $colNum != intval($item['UserItem']['col_num']) - 1) {
					$rowNum = 1;
				}

				if(isset($item_publics[$item['UserItem']['id']]) && $item_publics[$item['UserItem']['id']] === false){
					// 全部の権限でログイン会員の閲覧権限がないならば、ラベルを消す。自分自身以外検索結果に表示されないため。
					$rets[$key] = '';
					continue;
				}

				$ret = $this->element('item', array('item' => $item, 'isEdit' => false));
				$rets[$key] = $ret;
				if($ret == '') {
					continue;
				}
				$listNum = intval($item['UserItem']['list_num']) - 1;
				$colNum = intval($item['UserItem']['col_num']) - 1;
				if(!isset($listMaxArr[$listNum]) || $listMaxArr[$listNum] < $rowNum) {
					$listMaxArr[$listNum] = $rowNum;
				}
				$rowNum++;
			}
			$listMax = 0;

			foreach($listMaxArr as $buflistMax) {
				$listMax += $buflistMax;
			}
		} else {
			$listMax = USER_SEARCH_ROW_NUM;
		}

		$itemList = array();
		$colNum = 0;
		$rowCount = 0;
		foreach ($items as $key => $item) {
			if(isset($rets[$key])) {
				$ret = $rets[$key];
			} else {
				$ret = $this->element('item', array('item' => $item, 'isEdit' => false));
			}
			if($ret == '') {
				continue;
			}
			$itemList[$colNum][$rowCount] = $ret;
			$rowCount++;

			if($colNum == 0 && $rowCount >= $listMax) {
				// １列目の最後に参加コミュニティーを表示（固定）
				$communityItem = array(
					'UserItem' => array(
						'id' => 0,
						'type' => 'communities',
						'tag_name' => 'communities',
						'allow_public_flag' => false,
						'allow_email_reception_flag' => false,
						'display_title' => true,
					),
					'UserItemLang' => array(
						'name' => __d('user', 'Communities'),
						'lang' => '',
					)
				);
				$itemList[$colNum][$rowCount] = $this->element('item', array('item' => $communityItem, 'isEdit' => false, 'communities' => $communities));
				$rowCount++;
			}

			if($rowCount >= $listMax) {
				$colNum++;
				$rowCount = 0;
			}
		}
	?>
	<div class="top-description">
		<?php echo __d('user', "You can search for members by specifying search condition.");?>
	</div>
	<div class="user-edit">
		<fieldset class="form user-edit-list">
			<div class="table widthmax">
				<div class="table-cell widthmax user-edit-list-outer">
					<div class="table widthmax">
					<?php
						$count = count($itemList);
						$width = floor(100 / $count);
					?>
					<?php if(count($itemList) > 0): ?>
						<?php foreach ($itemList as $colNum => $itemCol): ?>
						<div class="table-cell top user-edit-col<?php if($colNum != $count): ?> user-edit-right-line<?php endif; ?>" style="width:<?php echo $width;?>%;">
							<ul class="lists user-edit-row">
								<?php foreach ($itemCol as $rowNum => $ret): ?>
								<li>
									<?php
										echo $ret;
									?>
								</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</fieldset>
	</div>
	<?php
		echo $this->Html->div('submit',
			$this->Form->button(__('Search'), array('name' => 'search', 'class' => 'common-btn', 'type' => 'submit')).
			$this->Form->button(__('Close'), array('name' => 'close', 'class' => 'common-btn', 'type' => 'button', 'onclick' => "$('#user-search-".$id."').parent().dialog('close');return false;"))
		);
		echo $this->Form->hidden('isSearch' , array('name' => "isSearch", 'value' => _ON));
		echo $this->Form->end();
		echo $this->Html->css(array('User.search/'));
	?>
</div>