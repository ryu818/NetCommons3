<div class="pages-menu-community-tab">
	<div id="pages-menu-community-tab<?php echo($page['Page']['id']); ?>">
		<ul>
			<li><a href="#pages-menu-community<?php echo($page['Page']['id']); ?>"><?php echo(__d('page', 'Community setting'));?></a></li>
			<li><a href="#pages-menu-general<?php echo($page['Page']['id']); ?>"><?php echo(__('General setting'));?></a></li>
		</ul>
		<div id="pages-menu-community<?php echo($page['Page']['id']); ?>" class="display-none">
			<fieldset class="form">
				<ul class="lists pages-menu-community-lists">
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
											NC_PUBLICATION_RANGE_FLAG_ALL => __d('page', 'Public(All user can see content.)'),
											NC_PUBLICATION_RANGE_FLAG_LOGIN_USER => __d('page', 'Some public(All login user can see content.)'),
											NC_PUBLICATION_RANGE_FLAG_ONLY_USER => __d('page', 'Private(Only participant user can see content.)'),
										),
										'div' => false,
										'before' => '<div>',
										'separator' => '</div><div>',
    									'after' => '</div>',
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
									$settings = array(
										'id' => "pages-menu-community-participate-".$page['Page']['id'].'-',
										'legend' => false,
										'value' => $community_params['community']['Community']['participate_flag'],
										'type' =>'radio',
										'options' => array(
											NC_PARTICIPATE_FLAG_FREE => __d('page', 'Free（All login user can participate.)'),
											NC_PARTICIPATE_FLAG_ACCEPT => __d('page', 'Require the approval of room manager.'),
											NC_PARTICIPATE_FLAG_INVITE => __d('page', 'Invitation(Only Invite user can participate.)'),
											NC_PARTICIPATE_FLAG_ONLY_USER => __d('page', 'Only participant user')
										),
										'div' => false,
										'before' => '<div>',
										'separator' => '</div><div>',
    									'after' => '</div>',
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
											echo $this->Form->authoritySlider('Community.invite_hierarchy', array('id' => "pages-menu-community-invite-authority-".$page['Page']['id'].'-' ,'disable' => $disable, 'value' => $community_params['community']['Community']['invite_hierarchy']));
										?>
									</div>
								</div>
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
									<img id="pages-menu-community-photo-preview-<?php echo($page['Page']['id']);?>" alt="<?php echo(__d('page', 'Photo')); ?>" title="<?php echo(__d('page', 'Photo')); ?>" src="<?php echo($this->webroot); ?>/img/common/blank.gif"  style="background-image:url(<?php echo($this->webroot); ?>page/img/community/<?php echo(h($community_params['community']['Community']['photo'])); ?>);" />
								</div>

								<div class="pages-menu-community-photo-sample">
									<?php foreach($community_params['photo_samples'] as $photo_sample): ?>
										<a href="#" onclick="$.PageMenu.selectPhoto(<?php echo($page['Page']['id']);?>, this, '<?php echo(h($photo_sample)); ?>'); return false;" >
											<img class="pages-menu-community-photo-sample" src="<?php echo($this->webroot); ?>page/img/community/<?php echo($photo_sample); ?>" title="" alt="" />
										</a>
									<?php endforeach; ?>
								</div>
								<div class="align-right clearfix">
									<?php echo(__d('page','Custom'));?>:
									<input id="pages-menu-community-photo-<?php echo($page['Page']['id']);?>" type="file" name="data['Community']['file']" size="20" />
									<input class="common-btn" type="button" value="<?php echo(__d('page','Upload')); ?>" onclick="return false;" />
								</div>
								<input type="hidden" name="data[Community][photo]" value="<?php echo(h($community_params['community']['Community']['photo'])); ?>" />
								<input type="hidden" name="data[Community][upload_id]" value="<?php echo(intval($community_params['community']['Community']['upload_id'])); ?>" />
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
						<a href="#">
							<?php echo(__('Detail setting'));?>
						</a>
					</li>
				</ul>
			</fieldset>
		</div>
		<div id="pages-menu-general<?php echo($page['Page']['id']); ?>"  class="display-none">
			<?php
				echo($this->element('index/detail', array('page' => $page, 'parent_page' => $parent_page, 'is_community' => true)));
			?>
		</div>
	</div>
	<div class="btn-bottom">
		<input type="submit" class="common-btn" name="ok" value="<?php echo( __('Ok')); ?>" />
		<input type="button" class="common-btn" name="cancel" value="<?php echo(__('Cancel')); ?>" onclick="$('#pages-menu-edit-detail-<?php echo($page['Page']['id']);?>').slideUp(300);" />


		<input type="button" class="common-btn common-btn-light" name="participant" value="<?php echo(__d('page','Edit members')); ?>" data-page-edit-id=<?php echo($page['Page']['id']);?> data-ajax-url="<?php echo($this->Html->url(array('plugin' => 'page', 'controller' => 'page_menus', 'action' => 'participant', $page['Page']['id']))); ?>" data-ajax="#pages-menu-edit-participant-<?php echo($page['Page']['id']);?>" />

	</div>
</div>
<script>
$(function(){
	$.PageMenu.communityDetailInit(<?php echo($page['Page']['id']); ?>, <?php if(isset($error_flag) && $error_flag == _ON) { echo( 1 ); } else {echo( 0 );} ?>);
});
</script>
