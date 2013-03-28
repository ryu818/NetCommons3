<?php
	if($blog_style['BlogStyle']['display_flag'] == _OFF) {
		return;
	}
	switch($blog_style['BlogStyle']['widget_type']) {
		case BLOG_WIDGET_TYPE_MAIN:
			echo($this->element('blog/main', array('blog_style' => $blog_style, 'type' => isset($type) ? $type : null)));
			break;
		case BLOG_WIDGET_TYPE_RECENT_POSTS:
			echo($this->element('blog/widget/recent_posts', array('blog_style' => $blog_style, 'type' => isset($type) ? $type : null)));
			break;
		case BLOG_WIDGET_TYPE_RECENT_COMMENTS:
			echo($this->element('blog/widget/recent_comments', array('blog_style' => $blog_style, 'type' => isset($type) ? $type : null)));
			break;
		case BLOG_WIDGET_TYPE_ARCHIVES:
			echo($this->element('blog/widget/archives', array('blog_style' => $blog_style, 'type' => isset($type) ? $type : null)));
			break;
		case BLOG_WIDGET_TYPE_CATEGORIES:
			echo($this->element('blog/widget/categories', array('blog_style' => $blog_style, 'type' => isset($type) ? $type : null)));
			break;
		case BLOG_WIDGET_TYPE_NUMBER_POSTS:
			echo($this->element('blog/widget/number_posts', array('blog_style' => $blog_style, 'type' => isset($type) ? $type : null)));
			break;
		case BLOG_WIDGET_TYPE_TAGS:
			echo($this->element('blog/widget/tags', array('blog_style' => $blog_style, 'type' => isset($type) ? $type : null)));
			break;
		case BLOG_WIDGET_TYPE_CALENDAR:
			echo($this->element('blog/widget/calendar', array('blog_style' => $blog_style, 'type' => isset($type) ? $type : null)));
			break;
		case BLOG_WIDGET_TYPE_RSS:
			echo($this->element('blog/widget/rss', array('blog_style' => $blog_style, 'type' => isset($type) ? $type : null)));
			break;
	}
?>