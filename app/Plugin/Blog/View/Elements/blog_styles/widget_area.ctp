<?php foreach ($blog_style_rows as $blog_style): ?>
		<?php
			$class = ' nc-title-color blog-style-widget-item';
			switch($blog_style['BlogStyle']['widget_type']) {
				case BLOG_WIDGET_TYPE_MAIN:
					$title = __d('blog', 'Blog');
					$content = $this->element('blog_styles/main', array('blog_style' => $blog_style));
					$class = ' nc-panel-color';
					break;
				case BLOG_WIDGET_TYPE_RECENT_POSTS:
					$title = __d('blog', 'Recent Posts');
					$content = $this->element('blog_styles/widget/recent_posts', array('blog_style' => $blog_style));
					break;
				case BLOG_WIDGET_TYPE_RECENT_COMMENTS:
					$title = __d('blog', 'Recent Comments');
					$content = $this->element('blog_styles/widget/recent_comments', array('blog_style' => $blog_style));
					break;
				case BLOG_WIDGET_TYPE_ARCHIVES:
					$title = __d('blog', 'Archives');
					$content = $this->element('blog_styles/widget/archives', array('blog_style' => $blog_style));
					break;
				case BLOG_WIDGET_TYPE_CATEGORIES:
					$title = __d('blog', 'Categories');
					$content = $this->element('blog_styles/widget/categories', array('blog_style' => $blog_style));
					break;
				case BLOG_WIDGET_TYPE_NUMBER_POSTS:
					$title = __('Results per page');
					$content = $this->element('blog_styles/widget/number_posts', array('blog_style' => $blog_style));
					break;
				case BLOG_WIDGET_TYPE_TAGS:
					$title = __d('blog', 'Tags');
					$content = $this->element('blog_styles/widget/tags', array('blog_style' => $blog_style));
					break;
				case BLOG_WIDGET_TYPE_CALENDAR:
					$title = __d('blog', 'Calendar');
					$content = $this->element('blog_styles/widget/calendar', array('blog_style' => $blog_style));
					break;
				case BLOG_WIDGET_TYPE_RSS:
					$title = __d('blog', 'RSS');
					$content = $this->element('blog_styles/widget/rss', array('blog_style' => $blog_style));
					break;
				default:
					continue;
			}
		?>
		<div class="blog-style-widget-area-outer" data-widget-type="<?php echo($blog_style['BlogStyle']['widget_type']); ?>">
			<div class="blog-style-widget-area-title blog-style-widget-area-title-sub <?php echo($class); ?>">
				<?php if($blog_style['BlogStyle']['display_flag'] == NC_DISPLAY_FLAG_ON): ?>
					<a class="blog-style-display-flag" href="#" title="<?php echo(__('To private')); ?>" onclick="$.BlogStyles.display(event, '<?php echo($id); ?>', this, '<?php echo($this->Js->escape($this->Html->url(array('action' => 'display')))); ?>');">
						<img class="icon" alt="<?php echo(__('To private')); ?>" src="<?php echo($this->webroot); ?>/img/icons/base/on.gif" data-alt="<?php echo(__('To public')); ?>" />
					</a>
				<?php else: ?>
					<a class="blog-style-display-flag"  href="#" title="<?php echo(__('To public')); ?>" onclick="$.BlogStyles.display(event, '<?php echo($id); ?>', this, '<?php echo($this->Js->escape($this->Html->url(array('action' => 'display')))); ?>');">
						<img class="icon" alt="<?php echo(__('To public')); ?>" src="<?php echo($this->webroot); ?>/img/icons/base/off.gif" data-alt="<?php echo(__('To private')); ?>" />
					</a>
				<?php endif; ?>
				<h4><?php echo($title); ?></h4>
				<a class="nc-widget-area-title-arrow"><span class="nc-arrow"></span></a>
			</div>
			<?php if($content != ''): ?>
			<div class="blog-style-widget-area-content" style="display:none;">
				<?php echo($content); ?>
			</div>
			<?php endif; ?>
		</div>
<?php endforeach; ?>