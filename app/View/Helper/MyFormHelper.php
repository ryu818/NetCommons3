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
}