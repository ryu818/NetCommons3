<?php if(count($categories) > 0): ?>
<div class="blog-posts-choose-tags">
	<?php echo __d('blog', 'Select some categories'); ?>
</div>
<?php
	$categoriesOptions = array();
	$multipleValues = array();
	foreach ($categories as $category) {
		$categoriesOptions[intval($category[0]['BlogTerm']['id'])] = $category[0]['BlogTerm']['name'];
		if($category[0]['BlogTerm']['checked']) {
			$multipleValues[] = $category[0]['BlogTerm']['id'];
		}
		if(isset($category[1])) {
			foreach ($category[1] as $child_category) {
				$categoriesOptions[$category[0]['BlogTerm']['name']][intval($child_category['BlogTerm']['id'])] = $child_category['BlogTerm']['name'];
				if($child_category['BlogTerm']['checked']) {
					$multipleValues[] = $child_category['BlogTerm']['id'];
				}
			}
		}
	}
	$settings = array(
		'id' => "blog-posts-categories-select".$id,
		'data-placeholder' => __d('blog', 'Select some categories'),
		'class' => 'blog-posts-select',
		'label' => false,
		'div' => false,
		'type' =>'select',
		'options' => $categoriesOptions,
		'multiple' => true,
		'value' => $multipleValues,
		'showParents'=>true,
	);
	echo $this->Form->input('BlogTermLink.category_id', $settings);
?>
<?php endif; ?>
<?php if($hierarchy >= $blog['Blog']['term_hierarchy']): ?>
	<a class="nowrap" href='#'><span class="ui-icon ui-icon-plus float-left"></span><?php echo(__d('blog', 'Add New Category')); ?></a>
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
				'onkeypress' => "$.BlogPosts.addCategory(event, '".$id."');",
				'required' => false,
			);
			echo $this->Form->input('BlogTerm.name', $settings);
		?>
		<?php if(count($categories) > 0): ?>
		<?php /* 親カテゴリ */ ?>
		<div class="blog-posts-parent-category-outer">
			<?php
				$categoriesOptions = array(0 => "");
				foreach ($categories as $category) {
					$categoriesOptions[intval($category[0]['BlogTerm']['id'])] = $category[0]['BlogTerm']['name'];
				}
				$settings = array(
					'id' => "blog-posts-categories-parent-select".$id,
					'data-placeholder' => __d('blog', 'Parent Category'),
					'class' => 'blog-posts-parent-category',
					'label' => false,
					'div' => false,
					'type' =>'select',
					'options' => $categoriesOptions,
				);
				echo $this->Form->input('BlogTerm.parent', $settings);
			?>
		</div>
		<div class="align-right">
		<?php
			echo $this->Form->button(__('Add'), array('name' => 'ok', 'class' => 'nc-common-btn-min', 'type' => 'button', 'onclick' => "$.BlogPosts.addCategory(event, '".$id."');"));
		?>
		</div>
		<?php endif; ?>
	</div>
<?php endif; ?>