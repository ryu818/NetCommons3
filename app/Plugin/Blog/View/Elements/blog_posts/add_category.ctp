<?php if(count($categories) > 0): ?>
<div class="blog-posts-choose-tags">
	<?php echo __d('blog', 'Select some categories'); ?>
</div>
<select id="blog-posts-categories-select<?php echo ($id);?>" name="data[BlogTermLink][category_id][]" data-placeholder="<?php echo(__d('blog', 'Select some categories')); ?>" multiple class="blog-posts-select">
<?php foreach ($categories as $category): ?>
	<option value="<?php echo(h($category[0]['BlogTerm']['id']));?>"<?php if($category[0]['BlogTerm']['checked']): ?> selected="selected"<?php endif; ?>><?php echo(h($category[0]['BlogTerm']['name']));?></option>
	<?php if(isset($category[1])): ?>
	<optgroup label="<?php echo(h($category[0]['BlogTerm']['name']));?>">
	<?php foreach ($category[1] as $child_category): ?>
		<option value="<?php echo(h($child_category['BlogTerm']['id']));?>"<?php if($child_category['BlogTerm']['checked']): ?> selected="selected"<?php endif; ?>><?php echo(h($child_category['BlogTerm']['name']));?></option>
	<?php endforeach; ?>
	</optgroup>
	<?php endif; ?>
<?php endforeach; ?>
</select>
<?php
	echo($this->Form->error('BlogTermLink.id'));
?>
<?php endif; ?>
<?php if($hierarchy >= $blog['Blog']['term_hierarchy']): ?>
	<a href='#'><span class="ui-icon ui-icon-plus float-left"></span><?php echo(__d('blog', 'Add New Category')); ?></a>
	<div id="blog-posts-add-category-outer<?php echo ($id);?>"<?php if(!$this->request->is('post') || count($this->validationErrors['BlogTerm']) == 0): ?> style="display:none;"<?php endif; ?>>
		<?php
			$settings = array(
				'type' => 'text',
				'value' => '',
				'class' => 'text blog-posts-add-text',
				'label' => false,
				'div' => false,
				'maxlength' => NC_VALIDATOR_TITLE_LEN,
				'size' => 10,
				'error' => array('attributes' => array(
					'selector' => true
				)),
				'data-ajax-url' =>  $this->Html->url(array('controller' => 'blog_terms', 'action' => 'add_category', $post_id)),
				'onkeypress' => "$.BlogPosts.addCategory(event, '".$id."');"
			);
			echo $this->Form->input('BlogTerm.name', $settings);
		?>
		<?php if(count($categories) > 0): ?>
		<?php /* 親カテゴリ */ ?>
		<div class="blog-posts-parent-category-outer">
			<select id="blog-posts-categories-parent-select<?php echo ($id);?>" name="data[BlogTerm][parent]" data-placeholder="<?php echo(__d('blog', 'Parent Category')); ?>"  class="blog-posts-parent-category">
				<option value="0"></option>
			<?php foreach ($categories as $category): ?>
				<option value="<?php echo(intval($category[0]['BlogTerm']['id']));?>"><?php echo(h($category[0]['BlogTerm']['name']));?></option>
			<?php endforeach; ?>
			</select>
			<?php
				echo($this->Form->error('BlogTerm.parent'));
			?>
		</div>
		<div class="align-right">
		<?php
			echo $this->Form->button(__('Add'), array('name' => 'ok', 'class' => 'common-btn common-btn-min', 'type' => 'button', 'onclick' => "$.BlogPosts.addCategory(event, '".$id."');"));
		?>
		</div>
		<?php endif; ?>
	</div>
<?php endif; ?>