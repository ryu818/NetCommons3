<?php
	switch($this->action) {
		case 'index':
			$authorityName = 'Administrator';
			break;
		case 'chief':
			$authorityName = 'Room Manager';
			break;
		case 'moderate':
			$authorityName = 'Moderator';
			break;
		case 'general':
			$authorityName = 'Common User';
			break;
		case 'guest':
			$authorityName = 'Guest';
			break;
	}
	echo $this->Form->create('Policy', array(
		'data-ajax' => 'this',
		'data-ajax-confirm' => __d('policy', 'Changing the Information Policy of %s.<br />Are you sure?', __($authorityName)),
		'data-confirm-reset' => __d('policy', 'Cancelling the action. OK?'),
	));
?>
<div class="top-description">
	<?php echo __d('policy', 'You can set the way to access information security policy in %s. Please change the slide bar member of any authority or to allow viewing and editing.', '<span class="policy-desc-title">'.__($authorityName).'</span>');?>
	<?php echo '<div class="require">'.__d('policy', 'Missetting may cause unwanted information leak.  Be careful when you make any change.').'</div>'; ?>
</div>

<div>
	<?php
		if(POLICY_ROW_NUM == 'auto') {
			// list毎の最大行数を求め、検索画面の一列に表示するitem数を決定する。
			$listMaxArr = array();
			$listNum = 0;
			$colNum = 0;
			$rowNum = 0;
			foreach ($items as $key => $item) {
				if(($listNum != intval($item['Item']['list_num']) - 1) || $colNum != intval($item['Item']['col_num']) - 1) {
					$rowNum = 1;
				}

				$listNum = intval($item['Item']['list_num']) - 1;
				$colNum = intval($item['Item']['col_num']) - 1;
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
			$listMax = POLICY_ROW_NUM;
		}

		$itemList = array();
		$colNum = 0;
		$rowCount = 0;
		foreach ($items as $key => $item) {
			$itemList[$colNum][$rowCount] = $item;
			$rowCount++;

			if($rowCount >= $listMax) {
				$colNum++;
				$rowCount = 0;
			}
		}
	?>

	<fieldset class="form policy-edit-list">
		<div class="table widthmax">
			<div class="table-cell widthmax policy-edit-list-outer">
				<div class="table widthmax">
				<?php
					$count = count($itemList);
					$width = floor(100 / $count);
				?>
				<?php if(count($itemList) > 0): ?>
					<?php foreach ($itemList as $colNum => $itemCol): ?>
					<div class="table-cell top policy-edit-col" style="width:<?php echo $width;?>%;">
						<ul class="lists">
							<?php foreach ($itemCol as $rowNum => $item): ?>
							<li class="clearfix policy-edit-row">
								<dl>
									<dt>
										<?php
											if(!isset($item['ItemLang']['name'])) {
												$item['ItemLang']['name'] = $item['Item']['default_name'];
											}
											if($item['Item']['tag_name'] != '' && $item['Item']['tag_name'] != 'username') {
												$name = 'User.'.$item['Item']['tag_name'];
											} else {
												$name = 'UserItemLink.'.$item['Item']['id'].'.content';
											}
											echo $this->Form->label($name, $item['ItemLang']['name']);
										?>
									</dt>
									<dd>
										<?php
											$NotAllowedEditFunction = '';
											$NotAllowedShowFunction = 'if(ui.value == 0) {return false;} ';
											if($user_authority_id != NC_AUTH_ADMIN_ID) {
												// 管理者以外は編集不可にできない。
												$NotAllowedEditFunction .= $NotAllowedShowFunction;
												$otherLabel = "<span class='disable-lbl'>".__d('policy', 'Not allowed to edit')."</span>";
											} else {
												$otherLabel = __d('policy', 'Not allowed to edit');
											}
											$otherShowLabel = "<span class='disable-lbl'>".__d('policy', 'Not allowed to view')."</span>";
											if($item['Item']['tag_name'] == 'authority_id') {
												$minAuthorityId = NC_AUTH_ADMIN_ID;
											} else if($user_authority_id <= NC_AUTH_ADMIN_ID) {
												$minAuthorityId = $user_authority_id;
											} elseif($user_authority_id <= NC_AUTH_GENERAL_ID) {
												$minAuthorityId = $user_authority_id - 1;
											} else {
												$minAuthorityId = $user_authority_id - 2;
											}
											$editId = 'policy-edit-lower-hierarchy-'.$item['Item']['id'].'-'.$user_authority_id;
											$showId = 'policy-show-lower-hierarchy-'.$item['Item']['id'].'-'.$user_authority_id;
											$LinkageFunction = "
												var editSlider = $('#%s-slider');
												var authorityId = editSlider.slider( 'option', 'value' );
												if(ui.value %s authorityId) {
													editSlider.slider( 'option', 'value', ui.value);
												}
											";
											$addClass = '';
											$sliderOptions = array();
											if($item['Item']['type'] == 'label') {
												$sliderOptions['disabled'] = true;
												$addClass .= ' disable-lbl';
											}
											$sliderOptions['slide'] = 'function( event, ui ) {' .$NotAllowedEditFunction. sprintf($LinkageFunction, $showId, '>') .'}';
											$adminLabel = __('Administrator').__(' - ').__('Clerk');
											
											echo '<div class="policy-edit-subtitle">'.__d('policy', 'Range that allows editing').':</div>';
											echo '<div class="policy-edit-orange'.$addClass.'">'. $this->Form->authoritySlider($editId, 
													array('id' => $editId, 'name' => 'ItemAuthorityLink['.$item['Item']['id'].'][edit_lower_hierarchy]', 
													'value' => $item_authority_links[$item['Item']['id']]['edit_lower_hierarchy'], 'min_authority_id' => $minAuthorityId, 
													'max_authority_id' => NC_AUTH_OTHER_ID, 'other_label' => $otherLabel, 'administrator_label' => $adminLabel, 'width' => 92)
													, $sliderOptions
											) . '</div>';
											$addClass = '';
											$sliderOptions = array();
											$sliderOptions['slide'] = 'function( event, ui ) {' .$NotAllowedShowFunction. sprintf($LinkageFunction, $editId, '<') .'}';
											if($item['Item']['tag_name'] == 'handle') {
												$sliderOptions['disabled'] = true;
												$addClass .= ' disable-lbl';
											}
											echo '<div class="policy-edit-subtitle">'.__d('policy', 'Range that allows viewing').':</div>';
											echo '<div class="policy-edit-blue'.$addClass.'">'. $this->Form->authoritySlider($showId, 
													array('id' => $showId, 'name' => 'ItemAuthorityLink['.$item['Item']['id'].'][show_lower_hierarchy]', 
													'value' => $item_authority_links[$item['Item']['id']]['show_lower_hierarchy'], 'min_authority_id' => NC_AUTH_GUEST_ID, 
													'max_authority_id' => NC_AUTH_OTHER_ID, 'other_label' => $otherShowLabel, 'administrator_label' => $adminLabel, 'width' => 92)
													, $sliderOptions
											) . '</div>';
										?>
									</dd>
								</dl>
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
	echo $this->Form->hidden('type' , array('id' => 'policy-type-'.$id, 'name' => 'type', 'value' => 'submit'));
	echo $this->Html->div('submit',
		$this->Form->button(__('Ok'), array('name' => 'regist', 'class' => 'common-btn', 'type' => 'submit', 'onclick' => "$.Policy.setConfirm('".$id."', 'submit');")).
		$this->Form->button(__('Reset'), array('name' => 'reset', 'class' => 'common-btn', 'type' => 'submit', 'onclick' => "$.Policy.setConfirm('".$id."', 'reset');"))
	);
	echo $this->Form->end();
?>
<script>
$(function(){
	$('#policy-init-tab').Policy('<?php echo $id;?>');
});
</script>