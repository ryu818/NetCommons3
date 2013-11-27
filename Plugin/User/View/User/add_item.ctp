<?php
/**
 * 会員管理 項目設定 項目追加・編集画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.User.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<div>
<?php
// TODO: 作成途中
// ・text,textareaならば正規表現(メールでも表示)、最大文字数、大小文字数
// ・ラベル、パスワード、画像ファイルはシステムでのみ使用
// ・メールの場合のみ「各自でメールの受信可否を設定可能にする」を表示
// ・リストボックス、選択式のoptionの指定（追加、削除）
echo $this->Form->create('UserItem', array('data-pjax' => '#'.$id));
?>
<fieldset class="form">
	<ul class="lists user-add-item">
		<li>
			<dl>
				<dt>
					<?php
						echo $this->Form->label('UserItemLang.name', __d('user', 'Item name'));
					?>
					<span class="require"><?php echo __('*'); ?></span>
				</dt>
				<dd>
					<?php
						$settings = array(
							'type' => 'text',
							'value' => $item['UserItemLang']['name'],
							'label' => false,
							'div' => false,
							'maxlength' => NC_VALIDATOR_TITLE_LEN,
							'class' => 'nc-title',
							'size' => 25,
							'error' => array('attributes' => array(
								'selector' => true
							))
						);
						echo $this->Form->input('UserItemLang.name', $settings);
					?>
					<div class="hr">
					<?php
						echo $this->Form->input('UserItem.allow_self_edit',array(
							'type' => 'checkbox',
							'value' => _ON,
							'checked' => !empty($item['UserItem']['allow_self_edit']) ? true : false,
							'label' => __d('user', 'Enable individual edit.'),
						));
					?>
					<div class="note">
					<?php echo __d('user', 'Administrator and clerk editable.'); ?>
					</div>
					<?php
						echo $this->Form->input('UserItem.required',array(
							'type' => 'checkbox',
							'value' => _ON,
							'checked' => !empty($item['UserItem']['required']) ? true : false,
							'label' => __d('user', 'Designate as required items.'),
						));
						echo $this->Form->input('UserItem.allow_public_flag',array(
							'type' => 'checkbox',
							'value' => _ON,
							'checked' => !empty($item['UserItem']['allow_public_flag']) ? true : false,
							'label' => __d('user', 'Enable individual public/private setting.'),
						));
						echo $this->Form->input('UserItem.is_lang',array(
							'type' => 'checkbox',
							'value' => _ON,
							'checked' => !empty($item['UserItem']['is_lang']) ? true : false,
							'label' => __d('user', 'Setable in each language.'),
						));
						echo $this->Form->input('UserItem.allow_duplicate',array(
							'type' => 'checkbox',
							'value' => _ON,
							'checked' => !empty($item['UserItem']['allow_duplicate']) ? true : false,
							'label' => __d('user', 'Allow duplication.'),
						));
						echo $this->Form->input('UserItem.display_title',array(
							'type' => 'checkbox',
							'value' => _ON,
							'checked' => !empty($item['UserItem']['display_title']) ? true : false,
							'label' => __d('user', 'Display the title.'),
						));
					?>
					</div>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo $this->Form->label('UserItem.type', __d('user', 'Input type'));
					?>
					<span class="require"><?php echo __('*'); ?></span>
				</dt>
				<dd>
					<?php
						$settings = array(
							'value' => $item['UserItem']['type'],
							'label' => false,
							'div' => false,
							'type' =>'select',
							'options' => array(
								'text' => __d('user', 'Text'),
								'checkbox' => __d('user', 'Radio button'),
								'radio' => __d('user', 'Check box'),
								'select' => __d('user', 'Select list'),
								'textarea' => __d('user', 'Text area'),
								'email' => __d('user', 'E-mail'),
								'mobile_email' => __d('user', 'Mobile mail'),
								'label' => __d('user', 'Label'),
								'password' => __d('user', 'Password'),
								'file' => __d('user', 'Image file'),
							),
							'onchange' => '$.User.changeAddItemType();',
						);
						echo $this->Form->input('UserItem.type', $settings);

						echo $this->Form->input('UserItem.allow_email_reception_flag',array(
							'type' => 'checkbox',
							'value' => _ON,
							'checked' => !empty($item['UserItem']['allow_email_reception_flag']) ? true : false,
							'label' => __d('user', 'Enable individual email receipt / non-receipt setting.'),
						));
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo $this->Form->label('UserItemLang.description', __d('user', 'Description'));
					?>
				</dt>
				<dd>
					<?php
						$settings = array(
							'type' => 'textarea',
							'escape' => false,
							'value' => $item['UserItemLang']['description'],
							'label' => false,
							'div' => false,
							'error' => array('attributes' => array(
								'selector' => true
							)),
							'cols' => 18,
							'rows' => 3,
							'class' => 'user-add-item-textarea',
						);
						echo $this->Form->input('UserItemLang.description', $settings);
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo $this->Form->label('UserItem.attribute', __d('user', 'Attribute'));
					?>
				</dt>
				<dd>
					<?php
						$settings = array(
							'type' => 'text',
							'value' => $item['UserItem']['attribute'],
							'label' => false,
							'div' => false,
							'maxlength' => NC_VALIDATOR_VARCHAR_LEN,
							'class' => 'nc-title',
							'size' => 25,
							'error' => array('attributes' => array(
								'selector' => true
							))
						);
						echo $this->Form->input('UserItem.attribute', $settings);
					?>
					<div class="note">
					<?php
						echo __d('user', 'Example： size=&#039;30&#039; style=&#039;padding:0px 3px&#039;');
					?>
					</div>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo $this->Form->label('UserItem.regexp', __d('user', 'Regexp'));
					?>
				</dt>
				<dd>
					<?php
						$settings = array(
							'type' => 'text',
							'value' => $item['UserItem']['regexp'],
							'label' => false,
							'div' => false,
							'maxlength' => NC_VALIDATOR_VARCHAR_LEN,
							'class' => 'nc-title',
							'size' => 25,
							'error' => array('attributes' => array(
								'selector' => true
							))
						);
						echo $this->Form->input('UserItem.regexp', $settings);
					?>
					<div class="note">
					<?php
						echo __d('user', 'You can enter the error check by pattern matching.');
					?>
					</div>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo $this->Form->label('UserItem.minlength', __d('user', 'Minimum number of characters'));
					?>
				</dt>
				<dd>
					<?php
						$settings = array(
							'type' => 'text',
							'value' => $item['UserItem']['minlength'],
							'label' => false,
							'div' => false,
							'maxlength' => 5,
							'class' => 'align-right',
							'size' => 5,
							'error' => array('attributes' => array(
								'selector' => true
							))
						);
						echo $this->Form->input('UserItem.minlength', $settings);
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo $this->Form->label('UserItem.maxlength', __d('user', 'Maximum number of characters'));
					?>
				</dt>
				<dd>
					<?php
						$settings = array(
							'type' => 'text',
							'value' => $item['UserItem']['maxlength'],
							'label' => false,
							'div' => false,
							'maxlength' => 5,
							'class' => 'align-right',
							'size' => 5,
							'error' => array('attributes' => array(
								'selector' => true
							))
						);
						echo $this->Form->input('UserItem.maxlength', $settings);
					?>
				</dd>
			</dl>
		</li>
	</ul>
</fieldset>
<?php
	echo $this->Html->div('submit',
		$this->Form->button(__('Ok'), array('name' => 'ok', 'class' => 'common-btn', 'type' => 'submit')).
		$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
			'onclick' => '$(\'#user-add-item'.$id.'\').dialog(\'close\'); return false;'))
	);
	echo $this->Form->end();
?>
</div>