<?php
/**
 * コミュニティー追加・編集画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
$ncUser = $this->Session->read(NC_AUTH_KEY.'.'.'User');
$isCreatePublicCommunity = in_array($ncUser['allow_creating_community'], array(NC_ALLOW_CREATING_COMMUNITY_ALL_USER, NC_ALLOW_CREATING_COMMUNITY_FORCE_ALL, NC_ALLOW_CREATING_COMMUNITY_ADMIN));
$isParticipateForceAllUsers = in_array($ncUser['allow_creating_community'], array(NC_ALLOW_CREATING_COMMUNITY_FORCE_ALL, NC_ALLOW_CREATING_COMMUNITY_ADMIN));
$isOnlyParticipant = !empty($ncUser['allow_new_participant']) ? true : false;
$isParticipateFlag = (!$isOnlyParticipant && $community_params['community']['Community']['participate_flag'] == NC_PARTICIPATE_FLAG_ONLY_USER) ? false :true;
// もし、既にparticipate_flag==NC_PARTICIPATE_FLAG_ONLY_USERに設定されていれば表示
if($community_params['community']['Community']['participate_flag'] == NC_PARTICIPATE_FLAG_ONLY_USER) {
	$isOnlyParticipant = true;
}
?>
<div class="pages-menu-community-tab">
	<div id="pages-menu-community-tab<?php echo($page['Page']['id']); ?>">
		<ul>
			<li><a href="#pages-menu-community<?php echo($page['Page']['id']); ?>"><?php echo(__d('page', 'Community setting'));?></a></li>
			<li><a href="#pages-menu-general<?php echo($page['Page']['id']); ?>"><?php echo(__('General setting'));?></a></li>
		</ul>
		<div id="pages-menu-community<?php echo($page['Page']['id']); ?>" class="display-none">
			<fieldset class="form">
				<ul class="nc-lists pages-menu-community-lists">
					<li>
						<dl>
							<dt>
								<label for="pages-menu-community-publication-range-<?php echo($page['Page']['id']);?>-<?php echo(NC_PUBLICATION_RANGE_FLAG_ONLY_USER);?>">
									<?php echo(__d('page', 'Publication range'));?>
								</label>
							</dt>
							<dd>
								<?php
									$settings = array(
										'id' => "pages-menu-community-publication-range-".$page['Page']['id'].'-',
										'legend' => false,
										'value' => $community_params['community']['Community']['publication_range_flag'],
										'type' =>'radio',
										'options' => array(
											NC_PUBLICATION_RANGE_FLAG_LOGIN_USER => __d('page', 'Public(All login user can see content.)'),
											NC_PUBLICATION_RANGE_FLAG_ONLY_USER => __d('page', 'Private(Only participant user can see content.)'),
										),
										'div' => false,
										'before' => '<div>',
										'separator' => '</div><div>',
    									'after' => '</div>',
										'disabled' => $isCreatePublicCommunity ? false : true,
									);
									if(isset($is_child)) {
										$settings['error'] = false;
									} else {
										$settings['error'] = array('attributes' => array(
											'selector' => $this->Js->escape("$('[name=data\\[Community\\]\\[publication_range_flag\\]]', $('#PagesMenuForm-".$page['Page']['id']."'))")
										));
									}
									echo $this->Form->input('Community.publication_range_flag', $settings);
								?>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<label for="pages-menu-community-participate-<?php echo($page['Page']['id']);?>-<?php echo(NC_PARTICIPATE_FLAG_FREE);?>">
									<?php echo(__d('page', 'How to participate'));?>
								</label>
							</dt>
							<dd>
								<?php
									$options = array(
										NC_PARTICIPATE_FLAG_FREE => __d('page', 'Free(All login user can participate.)'),
										NC_PARTICIPATE_FLAG_ACCEPT => __d('page', 'Free(Require the approval of room manager.)'),
										NC_PARTICIPATE_FLAG_INVITE => __d('page', 'Invitation(Only Invite user can participate.)'),
									);
									if($isOnlyParticipant) {
										$options[NC_PARTICIPATE_FLAG_ONLY_USER] = __d('page', 'Only participant user');
									}
									$settings = array(
										'id' => "pages-menu-community-participate-".$page['Page']['id'].'-',
										'legend' => false,
										'value' => $community_params['community']['Community']['participate_flag'],
										'type' =>'radio',
										'options' => $options,
										'div' => false,
										'before' => '<div>',
										'separator' => '</div><div>',
    									'after' => '</div>',
										'disabled' => $isParticipateFlag ? false : true,
									);
									if(isset($is_child)) {
										$settings['error'] = false;
									} else {
										$settings['error'] = array('attributes' => array(
											'selector' => $this->Js->escape("$('[name=data\\[Community\\]\\[participate_flag\\]]', $('#PagesMenuForm-".$page['Page']['id']."'))")
										));
									}
									echo $this->Form->input('Community.participate_flag', $settings);
								?>
								<?php if(!empty($community_params['community']['Community']['participate_force_all_users']) || $isParticipateForceAllUsers):?>
								<div id="pages-menu-community-participate-force-outer-<?php echo $page['Page']['id']; ?>" class="pages-menu-community-participate-force-outer"<?php if($community_params['community']['Community']['participate_flag'] != NC_PARTICIPATE_FLAG_ONLY_USER):?>style="display:none;"<?php endif; ?>>
								<?php
									/* 全会員を強制的に参加させる。 */
									echo $this->Form->input('Community.participate_force_all_users',array(
										'type' => 'checkbox',
										'value' => _ON,
										'checked' => !empty($community_params['community']['Community']['participate_force_all_users']) ? true : false,
										'label' => __d('page', 'Join to force all members.'),
										'disabled' => $isParticipateForceAllUsers ? false : true,
									));
								?>
								</div>
								<?php endif; ?>
								<div class="hr clearfix">
									<div class="float-left">
										<?php echo(__d('page', 'Authority to invite'));?>
									</div>
									<div class="float-left pages-menu-community-slider-outer">
										<?php
											$disable = false;
											if($community_params['community']['Community']['participate_flag'] == NC_PARTICIPATE_FLAG_ONLY_USER) {
												$disable = true;
											}
											echo $this->Form->authoritySlider('Community.invite_hierarchy', array('id' => "pages-menu-community-invite-authority-".$page['Page']['id'] , 'value' => $community_params['community']['Community']['invite_hierarchy']),  array('disabled' => $disable));
										?>
									</div>
								</div>
								<?php
									if($isOnlyParticipant) {
										echo "<div class=\"note\">" . __d('page', 'Withdrawal of the community is not used when I make "Only participant user".') . "</div>";
									}
								?>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<label for="pages-menu-community-photo-<?php echo($page['Page']['id']);?>">
									<?php echo(__d('page', 'Photo'));?>
								</label>
							</dt>
							<dd>
								<div class="pages-menu-community-photo">
									<div class="pages-menu-community-photo-inner nc-thumbnail">
										<div class="nc-thumbnail-centered">
											<?php
												if(!$community_params['community']['Community']['is_upload']) {
													$imageUrl = $this->Html->url('/', true).'page/img/community/'.$community_params['community']['Community']['photo'];
												} else {
													$imageUrl = $this->Html->url('/', true).'nc-downloads/'.$community_params['community']['Community']['photo'];
												}
												echo '<img id="pages-menu-community-photo-' . $page['Page']['id'] . '" src="'.$imageUrl.'" />';
											?>
										</div>
									</div>
								</div>

								<div class="pages-menu-community-photo-sample">
									<?php foreach($community_params['photo_samples'] as $photo_sample): ?>
										<a href="#" onclick="$.PageMenu.selectCommunityFile(<?php echo($page['Page']['id']);?>, 0, '<?php echo($photo_sample); ?>', '<?php echo($this->webroot); ?>page/img/community/<?php echo($photo_sample); ?>'); return false;" >
											<img class="pages-menu-community-photo-sample" src="<?php echo($this->webroot); ?>page/img/community/<?php echo($photo_sample); ?>" title="" alt="" />
										</a>
									<?php endforeach; ?>
								</div>
								<div class="align-right clearfix">
									<?php echo(__d('page','Custom'));?>:
									<?php echo $this->Form->button(__('Select file'), array(
										'name' => 'select_file',
										'type' => 'button',
										'class' => 'nc-common-btn pages-menu-select-file-btn',
										'onclick' => "$.Common.showUploadDialog('dialog-".$id."', {'el' : this, 'action' : 'library', 'callback' : function(fileName, url, libraryUrl){\$.PageMenu.selectCommunityFile(".$page['Page']['id'].", 1, fileName , url, libraryUrl);}});"
									));?>
								</div>
								<?php
								echo $this->Form->hidden('Community.photo' , array('id' => 'pages-menu-community-photo-hidden-'.$page['Page']['id'], 'value' => $community_params['community']['Community']['photo']));
								echo $this->Form->hidden('Community.is_upload' , array('id' => 'pages-menu-community-is-upload-hidden-'.$page['Page']['id'], 'value' => intval($community_params['community']['Community']['is_upload'])));
								?>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<label for="pages-menu-community-summary-<?php echo($page['Page']['id']);?>">
									<?php echo(__d('page', 'Summary'));?>
								</label>
							</dt>
							<dd>
								<?php
									$settings = array(
										'id' => "pages-menu-community-summary-".$page['Page']['id'].'-',
										'class' => 'pages-menu-community-summary',
										'value' => isset($community_params['community_lang']['CommunityLang']['summary']) ? $community_params['community_lang']['CommunityLang']['summary'] : ''
									);
									echo $this->Form->textarea('CommunityLang.summary', $settings);
									if (!isset($is_child) && $this->Form->isFieldError('CommunityLang.summary')) {
    									echo $this->Form->error('CommunityLang.summary');
									}
								?>
							</dd>
						</dl>
					</li>
					<li class="pages-menu-community-detail-setting">
						<a href="#" id="pages-menu-community-detail-setting-<?php echo($page['Page']['id']);?>">
							<?php echo(__('Detail setting'));?>
						</a>
					</li>
				</ul>
				<ul id="pages-menu-community-detail-<?php echo($page['Page']['id']);?>" class="nc-lists pages-menu-community-lists" <?php if(!isset($error_flag) || $error_flag != 3):?>style="display:none;"<?php endif; ?>>
					<li>
						<dl>
							<dt class="clear">
								<label for="pages-menu-community-description-<?php echo($page['Page']['id']);?>">
									<?php echo(__d('page', 'Description'));?>
								</label>
							</dt>
							<dd class="pages-menu-community-description">
								<?php
									echo $this->Form->error('Revision.content');
									echo $this->Form->textarea('Revision.content', array('id' => "pages-menu-community-description-". $page['Page']['id'], 'required' => false, 'class' => 'nc-wysiwyg pages-menu-community-description', 'value' => $community_params['community_lang']['Revision']['content']));
								?>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<label for="pages-menu-community-keyword-<?php echo($page['Page']['id']);?>">
									<?php echo(__d('page', 'Keyword'));?>
								</label>
							</dt>
							<dd>
								<?php
									$settings = array(
										'id' => "pages-menu-community-keyword-". $page['Page']['id'],
										'type' => 'text',
										'class' => 'pages-menu-community-keyword',
										'value' => $community_params['community_tag']['CommunityTag']['tag_value'],
										'label' => false,
										'maxlength' => NC_VALIDATOR_VARCHAR_LEN,
										'size' => 31,
										'error' => array('attributes' => array(
											'selector' => true
										)),
										'required' => false,
									);
									echo $this->Form->input('CommunityTag.tag_value', $settings);
								?>
								<div class="note">
									<?php echo __d('page', 'Please input keyword as comma-deliminated.'); ?>
								</div>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<?php echo(__d('page', 'Deliver e-mail when participated?'));?>
							</dt>
							<dd>
								<?php
									echo $this->Form->input('Community.is_participate_notice',array(
										'type' => 'radio',
										'options' => array(_ON => __d('page', 'Yes'), _OFF => __d('page', 'No')),
										'value' => intval($community_params['community']['Community']['is_participate_notice']),
										'div' => false,
										'legend' => false,
									));
								?>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<?php echo(__d('page', 'Deliver e-mail when resigned?'));?>
							</dt>
							<dd>
								<?php
									echo $this->Form->input('Community.is_resign_notice',array(
										'type' => 'radio',
										'options' => array(_ON => __d('page', 'Yes'), _OFF => __d('page', 'No')),
										'value' => intval($community_params['community']['Community']['is_resign_notice']),
										'div' => false,
										'legend' => false,
									));
								?>
							</dd>
						</dl>
					</li>
				</ul>
			</fieldset>
		</div>
		<div id="pages-menu-general<?php echo($page['Page']['id']); ?>" class="display-none">
			<?php
				echo($this->element('index/detail', array('page' => $page, 'parent_page' => $parent_page, 'is_community' => true)));
			?>
		</div>
	</div>
	<div class="nc-btn-bottom">
		<?php
			echo $this->Form->hidden('is_participant' , array('id' => 'pages-menu-is-participant-'.$page['Page']['id'], 'name' => 'is_participant', 'value' => _OFF));
		?>
		<input onclick="$('#pages-menu-is-participant-<?php echo $page['Page']['id'];?>').val(0);" type="submit" class="nc-common-btn" name="ok" value="<?php echo( __('Ok')); ?>" />
		<input type="button" class="nc-common-btn" name="cancel" value="<?php echo(__('Cancel')); ?>" onclick="$('#pages-menu-edit-detail-<?php echo($page['Page']['id']);?>').slideUp(300);" />
		<input onclick="$('#pages-menu-is-participant-<?php echo $page['Page']['id'];?>').val(1);" type="submit" class="nc-common-btn nc-button-blue" name="participant" value="<?php echo(__d('page','Edit members')); ?>" />
	</div>
</div>
<?php
echo ($this->Html->script(array('plugins/jquery.nc_wysiwyg.js')));
echo ($this->Html->css(array('plugins/jquery.nc_wysiwyg.css')));
?>
<script>
$(function(){
	$.PageMenu.communityDetailInit(<?php echo($page['Page']['id']); ?>, <?php if(isset($error_flag) && $error_flag == _ON) { echo( 1 ); } else {echo( 0 );} ?>);
});
</script>
