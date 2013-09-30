<ul class="lists pages-menu-style-scopes">
	<li>
		<dl>
			<dt>
				<?php
					echo $this->Form->label('PageStyle.scope', __d('page', 'Scope'));
				?>
			</dt>
			<dd>
				<?php
					switch($page['Page']['space_type']) {
						case NC_SPACE_TYPE_PUBLIC:
							$spaceTypeTitle = __d('page', 'Entire public');
							break;
						case NC_SPACE_TYPE_MYPORTAL:
							$spaceTypeTitle = __d('page', 'Entire myportal');
							$currentSpaceTypeTitle = __d('page', 'Entire myportal of the current');
							break;
						case NC_SPACE_TYPE_PRIVATE:
							$spaceTypeTitle = __d('page', 'Entire myroom');
							$currentSpaceTypeTitle = __d('page', 'Entire myroom of the current');
							break;
						case NC_SPACE_TYPE_GROUP:
							$spaceTypeTitle = __d('page', 'Entire community');
							$currentSpaceTypeTitle = __d('page', 'Entire community of the current');
							break;
					}

					$options = array(
						NC_PAGE_SCOPE_SITE => __d('page', 'Entire site'),
						NC_PAGE_SCOPE_SPACE => $spaceTypeTitle,
					);
					if(isset($currentSpaceTypeTitle)) {
						$options[NC_PAGE_SCOPE_ROOM] = $currentSpaceTypeTitle;
					}
					if($page['Page']['display_sequence'] > 1) {
						$options[NC_PAGE_SCOPE_NODE] = __d('page', 'Node of the current');
					}
					$options[NC_PAGE_SCOPE_CURRENT] = __d('page', 'Only the current page');
					$settings = array(
						'id' => 'pages-menu-style-scope',
						'type' => 'select',
						'options' => $options,
						'value' => isset($page_style['PageStyle']['scope']) ? $page_style['PageStyle']['scope'] : NC_PAGE_SCOPE_SITE,
						'label' => false,
						'div' => false,
						'style' => 'width: 200px;',
					);
					echo $this->Form->input('PageStyle.scope', $settings);
				?>
			</dd>
		</dl>
	</li>
	<li>
		<dl>
			<dt>
				<?php
					echo $this->Form->label('PageStyle.lang', __d('page', 'Application Language'));
				?>
			</dt>
			<dd>
				<?php
					$options = array(
						'all' => __('All'),
					);
					foreach($languages as $key => $value) {
						$options[$key] = __($value);
					}
					$settings = array(
						'id' => 'pages-menu-style-lang',
						'type' => 'select',
						'options' => $options,
						'value' => isset($page_style['PageStyle']['lang']) ? $page_style['PageStyle']['lang'] : '',
						'label' => false,
						'div' => false,
						'style' => 'width: 150px;',
					);
					echo $this->Form->input('PageStyle.lang', $settings);
				?>
			</dd>
		</dl>
	</li>
</ul>