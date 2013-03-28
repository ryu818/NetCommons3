<?php
$this->extend('/Frame/block');
$locale = Configure::read(NC_SYSTEM_KEY.'.locale');
echo $this->Form->create('BlogPost', array('data-pjax' => '#'.$id));
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
							$post_date = $this->TimeZone->date($blog_post['BlogPost']['post_date']);
							$post_date = date(__('Y-m-d H:i'), strtotime($post_date));
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
				echo($this->Form->error('Htmlarea.content'));
				echo $this->Form->textarea('Htmlarea.content', array('escape' => false, 'class' => 'nc-wysiwyg', 'value' => $blog_post['Htmlarea']['content']));
			?>
		</li>
		<?php if(isset($blog['Blog']['trackback_transmit_flag'])): ?>
		<li>
			<dl>
				<dt>
					<?php
						echo $this->Form->label('BlogPost.trackback_link', __d('blog', 'Send trackbacks to'));
					?>
				</dt>
				<dd>
					<?php
						$settings = array(
							'type' => 'text',
							'value' => $blog_post['BlogPost']['trackback_link'],
							'label' => false,
							'div' => false,
							'size' => 30,
							'error' => array('attributes' => array(
								'selector' => true
							))
						);
						echo $this->Form->input('BlogPost.trackback_link', $settings);
					?>
					<div class="note">
						<?php echo(__d('blog', 'Separate multiple URLs with spaces.')); ?>
					</div>
				</dd>
			</dl>
		</li>
		<?php endif; ?>
		<?php /* TODO:post_password未作成 */ ?>
		<?php /* TODO:履歴情報未作成 */ ?>
	</ul>
</fieldset>
<?php
	echo $this->Form->hidden('is_temporally' , array('name' => 'is_temporally', 'value' => _OFF));
	echo $this->Html->div('submit',
		$this->Form->button(__('Save temporally'), array('name' => 'temporally', 'class' => 'common-btn',
			'type' => 'button', 'onclick' => "$('#BlogPostIsTemporally".$id."').val(1);$(this.form).submit();")).
		$this->Form->button(__('Ok'), array('name' => 'ok', 'class' => 'common-btn', 'type' => 'submit')).
		$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
			'data-pjax' => '#'.$id, 'data-url' =>  $this->Html->url(array('controller' => 'blog'))))
);
?>
</div>
<div  id="blog-post-widget-area<?php echo ($id);?>" class="blog-post-widget-area table-cell">
	<?php /* カテゴリー、タグ */ ?>
	<div class="blog-style-widget-area">
		<div class="blog-style-widget-area-title nc-panel-color">
			<h4><?php echo(__d('blog', 'Categories')); ?></h4>
			<a class="blog-style-arrow"><span class="nc-arrow"></span></a>
		</div>
		<div id="blog-post-widget-area-content<?php echo ($id);?>" class="blog-style-widget-area-content blog-post-widget-area-content">
			<?php
			echo $this->element('blog_posts/add_category');
			?>
		</div>
	</div>
	<div class="blog-style-widget-area">
		<div class="blog-style-widget-area-title nc-panel-color">
			<h4><?php echo(__d('blog', 'Tags')); ?></h4>
			<a class="blog-style-arrow"><span class="nc-arrow"></span></a>
		</div>
		<div class="blog-style-widget-area-content">
			<select id="blog-posts-tags-select<?php echo ($id);?>" name="data[BlogTermLink][tag_name][]" data-placeholder="<?php echo(__d('blog', 'Choose from the used tags')); ?>" multiple class="blog-posts-select">
			<?php foreach ($tags as $tag): ?>
				<option value="<?php echo(h($tag['BlogTerm']['name']));?>"<?php if($tag['BlogTerm']['checked']): ?> selected="selected"<?php endif; ?>><?php echo(h($tag['BlogTerm']['name']));?></option>
			<?php endforeach; ?>
			</select>
			<?php
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
				echo $this->Form->button(__('Add'), array('name' => 'ok', 'class' => 'common-btn common-btn-min', 'type' => 'button', 'onclick' => "$.BlogPosts.addTags(event, '".$id."');"));
			?>
			</div>
		</div>
	</div>
</div>
<?php
echo $this->Form->end();
echo ($this->Html->script(array('Blog.blog_posts/index', 'plugins/jquery.nc_wysiwyg.js','plugins/jquery-ui-timepicker-addon.js', 'locale/'.$locale.'/plugins/jquery-ui-timepicker.js')));
echo ($this->Html->css(array('Blog.blog_styles/index', 'Blog.blog_posts/index','plugins/jquery.nc_wysiwyg.css', 'plugins/jquery-ui-timepicker-addon.css')));
?>
<script>
$(function(){
	$('#<?php echo($id); ?>').BlogPosts('<?php echo ($id);?>', <?php if(!$this->request->is('post') || count($this->validationErrors) == 0): ?>0<?php else: ?>1<?php endif; ?>);
});
</script>
</div>