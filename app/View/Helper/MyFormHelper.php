<?php
/**
 * FormのNc版
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.View.Helper
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
App::uses('FormHelper', 'View/Helper');
class MyFormHelper extends FormHelper {

	/**
	 * Returns an HTML FORM element.
	 *
	 * ・FormのidをForm+ (top_id)に変換
	 * ・Formのname属性がなければidと同等のものを設定。
	 *
	 * ### Options:
	 *
	 * - `type` Form method defaults to POST
	 * - `action`  The controller action the form submits to, (optional).
	 * - `url`  The url the form submits to. Can be a string or a url array.  If you use 'url'
	 *    you should leave 'action' undefined.
	 * - `default`  Allows for the creation of Ajax forms. Set this to false to prevent the default event handler.
	 *   Will create an onsubmit attribute if it doesn't not exist. If it does, default action suppression
	 *   will be appended.
	 * - `onsubmit` Used in conjunction with 'default' to create ajax forms.
	 * - `inputDefaults` set the default $options for FormHelper::input(). Any options that would
	 *   be set when using FormHelper::input() can be set here.  Options set with `inputDefaults`
	 *   can be overridden when calling input()
	 * - `encoding` Set the accept-charset encoding for the form.  Defaults to `Configure::read('App.encoding')`
	 *
	 * @param string $model The model object which the form is being defined for.  Should
	 *   include the plugin name for plugin forms.  e.g. `ContactManager.Contact`.
	 * @param array $options An array of html attributes and options.
	 * @return string An formatted opening FORM tag.
	 * @link http://book.cakephp.org/2.0/en/core-libraries/helpers/form.html#options-for-create
	 */
	public function create($model = null, $options = array()) {
		if (!isset($options['id']) && isset($this->_View->viewVars['id'])) {
			$options['id'] = $this->domId('Form');
		}
		if (!isset($options['name']) && isset($options['id'])) {
			$options['name'] = $options['id'];
		}
		return parent::create($model, $options);
	}

/**
 * Returns a formatted error message for given FORM field, NULL if no errors.
 *
 * ・オプションを追加。
 *
 * ### Options:
 *  Add Start Ryuji.M
 * - `popup`      bool   default false ポップアップ表示するかどうか。位置の指定は現状できない。
 * - `selector`   string default null targetのjquery.selector targetがtext,textareaならば、変更されたら削除。targetがselectならば、change,その他のinputタグならば、click時に削除。
 * 							trueと指定すれば、それ自身
 * - `close`      bool   default true trueならば×ボタンを付与。
 *
 *  Add End
 * - `escape`  bool  Whether or not to html escape the contents of the error.
 * - `wrap`  mixed  Whether or not the error message should be wrapped in a div. If a
 *   string, will be used as the HTML tag to use.
 * - `class` string  The classname for the error message
 *
 * @param string $field A field name, like "Modelname.fieldname"
 * @param string|array $text Error message as string or array of messages.
 * If array contains `attributes` key it will be used as options for error container
 * @param array $options Rendering options for <div /> wrapper tag
 * @return string If there are errors this method returns an error message, otherwise null.
 * @link http://book.cakephp.org/2.0/en/core-libraries/helpers/form.html#FormHelper::error
 */
	public function error($field, $text = null, $options = array()) {
		if (is_array($text)) {
			if (isset($text['attributes']) && is_array($text['attributes'])) {
				$options = array_merge($options, $text['attributes']);
				unset($text['attributes']);
			}
		}
		$defaults = array('wrap' => true, 'class' => 'error-message', 'escape' => true, 'popup' => false, 'selector' => '', 'close' => true);
		$options = array_merge($defaults, $options);
		$close = $options['close'];
		$popup = $options['popup'];
		$selector = $options['selector'];
		unset($options['close']);
		unset($options['popup']);
		unset($options['selector']);
		$popup_options = array();
		if($selector) {
			$uuid = String::uuid();
			if($popup != true) {
				$options['id'] = $uuid;
			} else {
				$popup_options['id'] = $uuid;
			}
		}

		$buf_wrap = $options['wrap'];
		$options['wrap'] = false;

		$class = $options['class'];
		$options['class'] .= ' clearfix';

		$str = parent::error($field, $text, $options);
		if( !$str ) {
			return $str;
		}

		if($close == true) {
			if($popup == true) {
				$class = 'popup-message';
			} else {
				$class = $class;
			}
			$str = '<div class="display-inline">'.$str. '</div><a href="#" class="message-close" role="button" onclick="$(this).parents(\'.'.$class.':first\').remove(); return false;"><span class="ui-icon ui-icon-closethick">close</span></a>' ;
		}
		if($buf_wrap !== false) {
			$tag = is_string($buf_wrap) ? $buf_wrap : 'div';
			unset($options['wrap']);
			$options['escape'] = false;
			$str = $this->Html->tag($tag, $str, $options);
		}

		if($popup == true) {
			$str = $this->Html->div('popup-message', $str, $popup_options);
		}
		if($selector) {
			if($selector === true) {
				$selector = "$('#".$uuid."').prev()";
			}
			$str .= $this->Html->scriptBlock("$.Common.closeAlert(".$selector.", $('#".$uuid."'));");
			//$str .= $this->Html->scriptBlock("$.Common.closeAlert('".$this->Js->escape($selector)."', $('#".$uuid."'));");
		}
		return $str;
	}

/**
 * 権限[主坦 モデレータ 一般]スライダー表示
 * @param   string $fieldName A field name, like "Modelname.fieldname"
 * @param   array  $options Array of HTML attributes.
 * 	### Options:
 * - `disable`      boolean  defaul:false 無効にするかどうか
 *
 * @return  string
 * @since   v 3.0.0.0
 */
	public function authoritySlider($fieldName, $options = array()) {
		return $this->_View->element('/common/authority_slider', array('fieldName' => $fieldName, 'options' => $options));
	}
}