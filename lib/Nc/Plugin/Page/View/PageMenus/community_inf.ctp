<?php
/**
 * コミュニティー情報表示画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
$ncUser = $this->Session->read(NC_AUTH_KEY.'.'.'User');
?>
<div data-width="590">
	<div class="pages-menu-community-inf-btn-top">
	<?php
		if(isset($ncUser) && $is_participate && $community['Community']['participate_flag'] != NC_PARTICIPATE_FLAG_ONLY_USER) {
			echo $this->Form->button(__d('page', 'Resign from community'), array(
				'name' => 'resign',
				'class' => 'common-btn-min nc-button-red',
				'type' => 'button',
				'data-ajax-confirm' => __d('page', 'Do you resign from "%s" community?', $community_lang['CommunityLang']['community_name']),
				'data-ajax-type' => 'post',
				'data-ajax' => '#pages-menu-community-inf',
				'data-ajax-url' => $this->Html->url(array('action' => 'resign_community', $community['Community']['room_id'])),
				'data-ajax-data' => h('{"token": "'.$this->params['_Token']['key'].'"}'),	// JSONのエラーとなるためh関数を用いてエスケープ
				'data-ajax-callback' => 'return $.PageCommunityInf.communityOperationCallback(e, res);',
			));
		}

		if(isset($ncUser) && !$is_participate && ($community['Community']['participate_flag'] == NC_PARTICIPATE_FLAG_FREE ||
			$community['Community']['participate_flag'] == NC_PARTICIPATE_FLAG_ACCEPT)) {
			echo $this->Form->button(__d('page', 'Participate community'), array(
				'name' => 'participate',
				'class' => 'common-btn-min nc-button-blue',
				'type' => 'button',
				'data-ajax-confirm' => __d('page', 'Join the "%s", are you sure?', $community_lang['CommunityLang']['community_name']),
				'data-ajax-type' => 'post',
				'data-ajax' => '#pages-menu-community-inf',
				'data-ajax-url' => $this->Html->url(array('action' => 'participate_community', $community['Community']['room_id'])),
				'data-ajax-data' => h('{"token": "'.$this->params['_Token']['key'].'"}'),	// JSONのエラーとなるためh関数を用いてエスケープ
				'data-ajax-callback' => 'return $.PageCommunityInf.communityOperationCallback(e, res);',
			));
		}

		if(isset($ncUser) && $is_participate && $min_hierarchy >= $community['Community']['invite_hierarchy'] && $community['Community']['participate_flag'] != NC_PARTICIPATE_FLAG_ONLY_USER) {
			$url = array('action' => 'invite_community', $community['Community']['room_id']);
			if(!$this->request->is('ajax')) {
				$url['is_center'] = _ON;
			}
			echo $this->Form->button(__d('page', 'Invite to this community'), array(
				'name' => 'invite',
				'class' => 'common-btn-min nc-button-blue',
				'type' => 'button',
				'data-ajax' => '#pages-menu-community-inf',
				'data-ajax-dialog' => 'true',
				'data-ajax-force' => 'true',
				'data-ajax-effect' => 'fold',
				'data-ajax-dialog-options' => h('{"title" : "'.$this->Js->escape(__d('page', 'Invite to "%s" community', $community_lang['CommunityLang']['community_name'])).'", "resizable": true, "width":"600"}'),
				'data-ajax-url' => $this->Html->url($url),
			));
		}
	?>
	</div>
	<div class="table widthmax">
		<div class="table-cell top">
			<div class="pages-menu-community-inf-photo">
				<div class="pages-menu-community-inf-photo-inner nc-thumbnail">
					<div class="nc-thumbnail-centered">
						<?php
							if(!$community['Community']['is_upload']) {
								$imageUrl = $this->Html->url('/', true).'page/img/community/'.$community['Community']['photo'];
							} else {
								$imageUrl = $this->Html->url('/', true).'nc-downloads/'.$community['Community']['photo'];
							}
							echo '<img src="'.$imageUrl.'" />';
						?>
					</div>
				</div>
			</div>
		</div>
		<div class="table-cell">
			<fieldset class="form">
				<ul class="lists pages-menu-community-inf-lists">
					<li>
						<dl>
							<dt>
								<?php echo(__d('page', 'Community name'));?>
							</dt>
							<dd>
								<?php
									echo(h($community_lang['CommunityLang']['community_name']));
								?>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<?php echo(__d('page', 'Publication range'));?>
							</dt>
							<dd>
								<?php
									switch( $community['Community']['publication_range_flag']) {
										case NC_PUBLICATION_RANGE_FLAG_LOGIN_USER:
											echo(__d('page', 'Private(Only participant user can see content.)'));
											break;
										case NC_PUBLICATION_RANGE_FLAG_ONLY_USER:
											echo(__d('page', 'Public(All login user can see content.)'));
											break;
									}
								?>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<?php echo(__d('page', 'How to participate'));?>
							</dt>
							<dd>
								<?php
									switch($community['Community']['participate_flag']) {
										case NC_PARTICIPATE_FLAG_ONLY_USER:
											echo(__d('page', 'Only participant user'));
											break;
										case NC_PARTICIPATE_FLAG_FREE:
											echo(__d('page', 'Free(All login user can participate.)'));
											break;
										case NC_PARTICIPATE_FLAG_ACCEPT:
											echo(__d('page', 'Free(Require the approval of room manager.)'));
											break;
										case NC_PARTICIPATE_FLAG_INVITE:
											echo(__d('page', 'Invitation(Only Invite user can participate.)'));
											break;
									}
								?>
								<?php if(!empty($community['Community']['participate_force_all_users'])):?>
								<div class="pages-menu-community-inf-participate-force-outer">
									<?php echo(__d('page', 'Join to force all members.')); ?>
								</div>
								<?php endif; ?>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<?php echo(__('Creator'));?>
							</dt>
							<dd>
								<?php
									/* TODO:ハンドルリンク未作成 */
									echo(h($community['Community']['created_user_name']));
								?>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<?php echo(__('Date Created'));?>
							</dt>
							<dd>
								<?php
									echo($this->TimeZone->date($community['Community']['created'], __('Y-m-d H:i')));
								?>
							</dd>
						</dl>
					</li>
					<?php
					/* TODO:最終更新日は未作成。
					<li>
						<dl>
							<dt>
								<?php echo(__('Last update date'));?>
							</dt>
							<dd>

							</dd>
						</dl>
					</li>
					*/
					?>
				</ul>
			</fieldset>
		</div>
	</div>
	<?php if($community_lang['CommunityLang']['summary'] || $community_lang['Revision']['content'] || $community_tag['CommunityTag']['tag_value']):?>
	<fieldset class="form">
		<ul class="lists pages-menu-community-inf-detail-lists">
			<?php if($community_lang['CommunityLang']['summary']):?>
			<li>
				<dl>
					<dt>
						<?php echo(__d('page', 'Summary'));?>
					</dt>
					<dd>
						<article>
							<?php echo(h($community_lang['CommunityLang']['summary']));?>
						</article>
					</dd>
				</dl>
			</li>
			<?php endif; ?>
			<?php if($community_lang['Revision']['content']):?>
			<li>
				<dl>
					<dt>
						<?php echo(__d('page', 'Description'));?>
					</dt>
					<dd>
						<article>
							<?php echo(h($community_lang['Revision']['content']));?>
						</article>
					</dd>
				</dl>
			</li>
			<?php endif; ?>
			<?php if($community_tag['CommunityTag']['tag_value']):?>
			<li>
				<dl>
					<dt>
						<?php echo(__d('page', 'Keyword'));?>
					</dt>
					<dd>
						<?php echo(h($community_tag['CommunityTag']['tag_value']));?>
					</dd>
				</dl>
			</li>
			<?php endif; ?>
		</ul>
	</fieldset>
	<?php endif; ?>
	<?php if(isset($ncUser) && $is_participate && !$this->request->is('ajax')): ?>
	<div class="pages-menu--community-inf-top">
	<?php
		$permalink = (NC_SPACE_GROUP_PREFIX != '') ? NC_SPACE_GROUP_PREFIX  . '/'. $page['Page']['permalink'] : $page['Page']['permalink'];
		$url = Router::url('/', true). $permalink;
	?>
	<a href="<?php echo $url;?>"><?php echo __d('page', 'Community top'); ?></a>
	</div>
	<?php endif; ?>
	<?php
		if($this->request->is('ajax')) {
			echo $this->Html->div('btn-bottom',
				$this->Form->button(__('Close'), array('name' => 'close', 'class' => 'common-btn', 'type' => 'button',
					'onclick' => '$(\'#pages-menu-community-inf'.'\').dialog(\'close\'); return false;'))
			);
		}
		echo $this->Html->script('Page.community_inf');
		echo $this->Html->css('Page.community_inf');
	?>
</div>