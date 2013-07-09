<?php
/**
 * ブログ投稿画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Blog.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
$this->extend('/Frame/block');
$locale = Configure::read(NC_SYSTEM_KEY.'.locale');
echo $this->Form->create('BlogPost', array('data-pjax' => '#'.$id));
if($blog['Blog']['approved_flag'] == _ON && $hierarchy  <= NC_AUTH_MODERATE) {
	$isApprovalSystem = true;
} else {
	$isApprovalSystem = false;
}
?>
<div class="table widthmax">
<div class="table-cell">
<fieldset class="form">
	<ul class="lists">
		<li>
			<dl>
				<dt>
					<?php
						echo $this->Form->label('BlogPost.post_date', __('Date-time'));
					?>
					<?php if(!empty($blog_post['BlogPost']['id']) && $blog_post['BlogPost']['status'] != NC_STATUS_PUBLISH): ?>
						<span class="temporary">
							<?php echo __('Temporary...'); ?>
						</span>
					<?php endif; ?>
					<?php if($isApprovalSystem): ?>
						<span class="temporary">
							<?php echo __('Approval system'); ?>
						</span>
					<?php endif; ?>
				</dt>
				<dd>
					<?php
						if($this->request->is('post')) {
							if($this->Form->isFieldError('BlogPost.post_date')) {
								$post_date = $blog_post['BlogPost']['post_date'];
							} else {
								$post_date = date(__('Y-m-d H:i'), strtotime($blog_post['BlogPost']['post_date']));
							}
						} else if(!empty($blog_post['BlogPost']['post_date'])) {
							$post_date = $this->TimeZone->date($blog_post['BlogPost']['post_date'], __('Y-m-d H:i'));
						} else {
							$post_date = '';
						}

						$settings = array(
							'type' => 'text',
							'value' => $post_date,
							'label' => false,
							'div' => false,
							'maxlength' => 16,
							'size' => 15,
							'class' => 'nc-datetime',
							'error' => array('attributes' => array(
								'selector' => true
							)),
						);
						echo $this->Form->input('BlogPost.post_date', $settings);
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo $this->Form->label('BlogPost.title', __('Title'));
					?>
				</dt>
				<dd>
					<?php
						// TODO:icon_nameの設定 未作成
						$settings = array(
							'type' => 'text',
							'value' => $blog_post['BlogPost']['title'],
							'label' => false,
							'div' => false,
							'maxlength' => NC_VALIDATOR_TITLE_LEN,
							'size' => 27,
							'class' => 'nc-title',
							'error' => array('attributes' => array(
								'selector' => true
							))
						);
						echo $this->Form->input('BlogPost.title', $settings);
						// TODO:permalinkの設定 未作成
					?>
				</dd>
			</dl>
		</li>
		<li>
			<?php
				echo($this->Form->error('Revision.content'));
				echo $this->Form->textarea('Revision.content', array('escape' => false, 'required' => false, 'class' => 'nc-wysiwyg', 'value' => $blog_post['Revision']['content']));
			?>
		</li>
		<?php if($blog['Blog']['trackback_transmit_flag'] == _ON): ?>
			<li>
				<dl>
					<dt>
						<?php
							echo $this->Form->label('BlogPost.to_ping', __d('blog', 'Send trackbacks to'));
						?>
					</dt>
					<dd>
						<?php
							$settings = array(
								'type' => 'text',
								'value' => $blog_post['BlogPost']['to_ping'],
								'label' => false,
								'div' => false,
								'size' => 30,
								'error' => array('attributes' => array(
									'selector' => true
								))
							);
							echo $this->Form->input('BlogPost.to_ping', $settings);
						?>
						<div class="note">
							<?php echo(__d('blog', 'Separate multiple URLs with spaces.')); ?>
						</div>
					</dd>
				</dl>
			</li>
		<?php endif; ?>
		<?php if(!empty($blog_post['BlogPost']['pinged'])): ?>
			<li>
				<dl>
					<dt>
						<?php
							echo $this->Form->label('BlogPost.pinged', __d('blog', 'Sent trackbacks'));
						?>
					</dt>
					<dd>
						<ul>
							<div class="blog-posts-pinged">
								<?php $pingeds = explode(' ', $blog_post['BlogPost']['pinged']);?>
								<?php foreach ($pingeds as $pinged) {?>
									<li>
										<?php echo h($pinged);?>
									</li>
								<?php }?>
							<div>
						</ul>
					</dd>
				</dl>
			</li>
		<?php endif;?>
		<?php /* TODO:post_password未作成 */ ?>
	</ul>
</fieldset>
<?php
	$backId = ($blog_post['BlogPost']['id'] == '0') ? $id : 'blog-post' . $id. '-' . $blog_post['BlogPost']['id'];
	$backUrl = array('controller' => 'blog', '#' => $backId);
	if(isset($this->request->query['back_query'])) {
		$backUrl = array_merge($backUrl, explode('/', $this->request->query['back_query']));
	}
	$backUrl['limit'] = isset($this->request->query['back_limit']) ? $this->request->query['back_limit'] : null;
	$backUrl['page'] = isset($this->request->query['back_page']) ? $this->request->query['back_page'] : null;

	echo $this->Form->hidden('AutoRegist.status' , array('value' => NC_STATUS_PUBLISH));
	echo $this->Html->div('submit',
		$this->Form->button(__('Save temporally'), array('name' => 'temporally', 'class' => 'common-btn',
			'type' => 'button', 'onclick' => "$('#AutoRegistStatus".$id."').val(".NC_STATUS_TEMPORARY.");$(this.form).submit();")).
		$this->Form->button(__('Ok'), array('name' => 'ok', 'class' => 'common-btn', 'type' => 'submit')).
		$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
			'data-pjax' => '#'.$id, 'data-ajax-url' =>  $this->Html->url($backUrl)))
);
?>
</div>
<div  id="blog-post-widget-area<?php echo ($id);?>" class="nc-widget-area nc-widget-area-outer table-cell">
	<?php /* カテゴリー、タグ */ ?>
	<div class="nc-widget-area">
		<div class="nc-widget-area-title nc-panel-color">
			<h4><?php echo(__d('blog', 'Categories')); ?></h4>
			<a class="nc-widget-area-title-arrow"><span class="nc-arrow"></span></a>
		</div>
		<div id="blog-post-widget-area-content<?php echo ($id);?>" class="nc-widget-area-content">
			<?php
			echo $this->element('blog_posts/add_category');
			?>
		</div>
	</div>
	<div class="nc-widget-area">
		<div class="nc-widget-area-title nc-panel-color">
			<h4><?php echo(__d('blog', 'Tags')); ?></h4>
			<a class="nc-widget-area-title-arrow"><span class="nc-arrow"></span></a>
		</div>
		<div class="nc-widget-area-content">
			<?php
				$tagsOptions = array();
				$multipleValues = array();
				foreach ($tags as $tag) {
					$tagsOptions[$tag['BlogTerm']['name']] = $tag['BlogTerm']['name'];
					if($tag['BlogTerm']['checked']) {
						$multipleValues[] = $tag['BlogTerm']['name'];
					}
				}
				$settings = array(
					'id' => "blog-posts-tags-select".$id,
					'data-placeholder' => __d('blog', 'Choose from the used tags'),
					'class' => 'blog-posts-select',
					'label' => false,
					'div' => false,
					'type' =>'select',
					'value' => $multipleValues,
					'options' => $tagsOptions,
					'multiple' => true,
				);
				echo $this->Form->input('BlogTermLink.tag_name', $settings);

				$settings = array(
					'id' => "blog-post-tag-names".$id,
					'type' => 'text',
					'value' => '',
					'class' => 'text blog-posts-add-text',
					'label' => false,
					'div' => false,

					'size' => 10,
					'error' => array('attributes' => array(
						'selector' => true
					)),
					'onkeypress' => "$.BlogPosts.addTags(event, '".$id."');"
				);
				echo $this->Form->input('BlogTerm.names', $settings);
			?>
			<div class="note">
				<?php echo(__d('blog', 'Separate tags with commas.')); ?>
			</div>
			<div class="blog-posts-tag-button-outer align-right">
			<?php
				echo $this->Form->button(__('Add'), array('name' => 'ok', 'class' => 'common-btn-min', 'type' => 'button', 'onclick' => "$.BlogPosts.addTags(event, '".$id."');"));
			?>
			</div>
		</div>
	</div>
	<?php /* 履歴情報 */ ?>
	<?php if($blog_post['BlogPost']['id'] > 0): ?>
	<div class="nc-widget-area">
		<div class="nc-widget-area-title nc-panel-color">
			<h4><?php echo(__('Revisions')); ?></h4>
			<a class="nc-widget-area-title-arrow"><span class="nc-arrow"></span></a>
		</div>
		<div id="blog-post-widget-area-content<?php echo ($id);?>" class="nc-widget-area-content">
			<?php if(isset($revisions) && count($revisions) > 0): ?>
			<?php
				echo $this->element('/common/revisions', array('url' => array($blog_post['BlogPost']['id'])));
			?>
			<?php endif; ?>
			<div class="small outer">
			<?php
				if($isApprovalSystem) {
					$disabled = true;
					$preChangeFlag = ($blog['Blog']['approved_pre_change_flag']) ? true : false;
				} else {
					if($blog_post['BlogPost']['is_approved'] == _OFF) {
						// 承認制で未承認コンテンツを主坦が更新しようとした場合
						$preChangeFlag = false;
					} else {
						$preChangeFlag = ($blog_post['BlogPost']['pre_change_flag']) ? true : false;
					}
					$disabled = false;
				}

				$settings = array(
					'value' => _ON,
					'checked' => $preChangeFlag,
					'label' =>__('You display the contents of the change before.'),
					'type' => 'checkbox',
					'div' => false,
					'disabled' => $disabled,
				);
				echo $this->Form->input('BlogPost.pre_change_flag', $settings);
			?>
			<div class="note indent"<?php if(!$this->Form->isFieldError('BlogPost.pre_change_date') && empty($blog_post['BlogPost']['pre_change_flag'])): ?> style="display:none;"<?php endif; ?>>
				<?php
					if($this->request->is('post') && !empty($blog_post['BlogPost']['pre_change_date'])) {
						if($this->Form->isFieldError('BlogPost.pre_change_date')) {
							$preChangeDate = $blog_post['BlogPost']['pre_change_date'];
						} else {
							$preChangeDate = date(__('Y-m-d H:i'), strtotime($blog_post['BlogPost']['pre_change_date']));
						}
					} else if($isApprovalSystem) {
						$preChangeDate = '';
					} else if(!empty($blog_post['BlogPost']['pre_change_date'])) {
						$preChangeDate = $this->TimeZone->date($blog_post['BlogPost']['pre_change_date'], __('Y-m-d H:i'));
					} else {
						$preChangeDate = '';
					}
					$settings = array(
						'type' => 'text',
						'value' => $preChangeDate,
						'label' => false,
						'div' => false,
						'maxlength' => 16,
						'size' => 15,
						'class' => 'nc-datetime text normal-text',
						'error' => array('attributes' => array(
							'selector' => true
						)),
						'disabled' => $disabled,
					);
					echo __('Published to %s automatically.', $this->Form->input('BlogPost.pre_change_date', $settings));
				?>
			</div>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>
<?php
echo $this->Form->end();
echo ($this->Html->script(array('Blog.blog_posts/index', 'plugins/jquery.nc_wysiwyg.js','plugins/jquery-ui-timepicker-addon.js', 'locale/'.$locale.'/plugins/jquery-ui-timepicker.js')));
echo ($this->Html->css(array('Blog.blog_posts/index','plugins/jquery.nc_wysiwyg.css', 'plugins/jquery-ui-timepicker-addon.css')));
?>
<script>
$(function(){
	$('#<?php echo($id); ?>').BlogPosts('<?php echo ($id);?>', <?php if(!$this->request->is('post') || count($this->validationErrors) == 0): ?>0<?php else: ?>1<?php endif; ?>);
});
</script>
</div>