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
class NcFormHelper extends FormHelper {

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
// Add Start Ryuji.M
// id属性、name属性付け替え
		if (!isset($options['id']) && isset($this->_View->viewVars['id'])) {
			$options['id'] = $this->domId('Form');
		}
		if (!isset($options['name']) && isset($options['id'])) {
			$options['name'] = $options['id'];
		}
// Add End Ryuji.M

		$created = $id = false;
		$append = '';

		if (is_array($model) && empty($options)) {
			$options = $model;
			$model = null;
		}

		if (empty($model) && $model !== false && !empty($this->request->params['models'])) {
			$model = key($this->request->params['models']);
		} elseif (empty($model) && empty($this->request->params['models'])) {
			$model = false;
		}
		$this->defaultModel = $model;

		$key = null;
		if ($model !== false) {
			list($plugin, $model) = pluginSplit($model, true);
			$key = $this->_introspectModel($plugin . $model, 'key');
			$this->setEntity($model, true);
		}

		if ($model !== false && $key) {
			$recordExists = (
			isset($this->request->data[$model]) &&
			!empty($this->request->data[$model][$key]) &&
			!is_array($this->request->data[$model][$key])
			);

			if ($recordExists) {
				$created = true;
				$id = $this->request->data[$model][$key];
			}
		}

		$options = array_merge(array(
			'type' => ($created && empty($options['action'])) ? 'put' : 'post',
			'action' => null,
			'url' => null,
			'default' => true,
			'encoding' => strtolower(Configure::read('App.encoding')),
			'inputDefaults' => array()),
		$options);
		$this->inputDefaults($options['inputDefaults']);
		unset($options['inputDefaults']);

		if (!isset($options['id'])) {
			$domId = isset($options['action']) ? $options['action'] : $this->request['action'];
			$options['id'] = $this->domId($domId . 'Form');
		}

		if ($options['action'] === null && $options['url'] === null) {
			$options['action'] = $this->request->here(false);
		} elseif (empty($options['url']) || is_array($options['url'])) {
			if (empty($options['url']['controller'])) {
// Edit Start Ryuji.M
// model名とプラグイン名がいっしょならば、controller名を複数形にしない
				$plugin = null;
				if ($this->plugin) {
					$plugin = $this->plugin;
				}
				if (!empty($model)) {
					if($this->plugin == $model) {
						$options['url']['controller'] = Inflector::underscore($model);
					} else {
						$options['url']['controller'] = Inflector::underscore(Inflector::pluralize($model));
					}
				} elseif (!empty($this->request->params['controller'])) {
					$options['url']['controller'] = Inflector::underscore($this->request->params['controller']);
				}
				//if (!empty($model)) {
				//	$options['url']['controller'] = Inflector::underscore(Inflector::pluralize($model));
				//} elseif (!empty($this->request->params['controller'])) {
				//	$options['url']['controller'] = Inflector::underscore($this->request->params['controller']);
				//}
// Edit End Ryuji.M
			}
// Add Start Ryuji.M
// #個所付与
			if($this->plugin && !isset($options['url']['#']) && isset($this->_View->viewVars['id'])) {
				$options['url']['#'] = $this->_View->viewVars['id'];
			} else if(isset($options['url']['#']) && $options['url']['#'] == '') {
				unset($options['url']['#']);
			}
// Add End Ryuji.M
			if (empty($options['action'])) {
				$options['action'] = $this->request->params['action'];
			}

			$plugin = null;
			if ($this->plugin) {
				$plugin = Inflector::underscore($this->plugin);
			}
			$actionDefaults = array(
				'plugin' => $plugin,
				'controller' => $this->_View->viewPath,
				'action' => $options['action'],
			);
			$options['action'] = array_merge($actionDefaults, (array)$options['url']);
			if (empty($options['action'][0]) && !empty($id)) {
				$options['action'][0] = $id;
			}
		} elseif (is_string($options['url'])) {
			$options['action'] = $options['url'];
		}
		unset($options['url']);

		switch (strtolower($options['type'])) {
			case 'get':
				$htmlAttributes['method'] = 'get';
				break;
			case 'file':
				$htmlAttributes['enctype'] = 'multipart/form-data';
				$options['type'] = ($created) ? 'put' : 'post';
			case 'post':
			case 'put':
			case 'delete':
				$append .= $this->hidden('_method', array(
				'name' => '_method', 'value' => strtoupper($options['type']), 'id' => null,
				'secure' => self::SECURE_SKIP
				));
			default:
				$htmlAttributes['method'] = 'post';
				break;
		}
		$this->requestType = strtolower($options['type']);

		$action = $this->url($options['action']);
		unset($options['type'], $options['action']);

		if (!$options['default']) {
			if (!isset($options['onsubmit'])) {
				$options['onsubmit'] = '';
			}
			$htmlAttributes['onsubmit'] = $options['onsubmit'] . 'event.returnValue = false; return false;';
		}
		unset($options['default']);

		if (!empty($options['encoding'])) {
			$htmlAttributes['accept-charset'] = $options['encoding'];
			unset($options['encoding']);
		}

		$htmlAttributes = array_merge($options, $htmlAttributes);

		$this->fields = array();
		if ($this->requestType !== 'get') {
			$append .= $this->_csrfField();
		}

		if (!empty($append)) {
			$append = $this->Html->useTag('hiddenblock', $append);
		}

		if ($model !== false) {
			$this->setEntity($model, true);
			$this->_introspectModel($model, 'fields');
		}
		return $this->Html->useTag('form', $action, $htmlAttributes) . $append;
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
 * 権限[主担 モデレーター 一般]スライダー表示
 * @param   string $fieldName A field name, like "Modelname.fieldname"
 * @param   array  $options Array of HTML attributes.
 * @param   array  $sliderOptions Array of slider options
 * 	### Options:
 * - `disable`      boolean  defaul:false 無効にするかどうか
 *
 * @return  string
 * @since   v 3.0.0.0
 */
	public function authoritySlider($fieldName, $options = array(), $sliderOptions = array()) {
		return $this->_View->element('/common/authority_slider', array('fieldName' => $fieldName, 'options' => $options, 'sliderOptions' => $sliderOptions));
	}

/**
 * ルーム一覧セレクトボックス表示
 * @param string $fieldName Name attribute of the SELECT
 * @param array key:room_id => array(key:thread_num => page_name) Page->findRoomListメソッドの返り値をセット。
 * @param array $attributes The HTML attributes of the select element.
 * @return string Formatted SELECT element
 * @since   v 3.0.0.0
 */
	public function selectRooms($fieldName, $rooms, $attributes = array()) {
		$options = array();
		if($rooms === false || count($rooms) == 0) {
			return '';
		}
		$attributes = array_merge( array('escape' => false, 'empty' => false, 'class' => 'select_rooms'), $attributes);
		foreach($rooms as $key => $room) {
			if(is_array($room)) {
				$optionNameSpace = '';
				for ($i = 1; $i < $room['Page']['thread_num']; $i++) {
					$optionNameSpace .= '&nbsp;&nbsp;';
				}
				$roomName = $optionNameSpace.h($room['Page']['page_name']);
				$options[$room['Page']['id']] = $roomName;
			} else {
				$options[$key] = $room;
			}
		}

		return $this->select($fieldName, $options, $attributes);
	}

/**
 * Returns if a field is required to be filled based on validation properties from the validating object.
 *
 * @param CakeValidationSet $validationRules
 * @return boolean true if field is required to be filled, false otherwise
 * TODO:allowEmpty指定がnullの場合は、required属性は付与しないように修正。
 * チェックボックスがONならば、その下のテキストを必須入力にする場合、required=>trueのみの指定で
 * allowEmptyは未指定にする必要があるが、自動的にrequired属性がつき必須になってしまうため。
 * （Viewで'required'=> falseを各々で記述する方法もあるがメインを修正）
 * また、notEmptyバリデータを使用したものもrequired属性を付与。
 *
 */
	protected function _isRequiredField($validationRules) {
		if (empty($validationRules) || count($validationRules) === 0) {
			return false;
		}

		$isUpdate = $this->requestType === 'put';
		foreach ($validationRules as $rule) {
			$rule->isUpdate($isUpdate);
			if ($rule->skip()) {
				continue;
			}
// Edit Start Ryuji.M
			//return !$rule->allowEmpty;
			return ($rule->rule[0] == 'notEmpty' || $rule->allowEmpty === false);
// Edit End Ryuji.M
		}
		return false;
	}

/**
 * Postデータをhidden属性で持ち回り用
 * 次へ、前へ等でPOSTのデータをhiddenに保持しつつ画面遷移することができる。
 *
 * 3次元配列までは対応。
 *
 * @param string Model name
 * @param array $fieldList	fieldListが設定されていた場合、そのfieldListのみ出力。
 * @param boolean falseの場合、""のデータのものはhiddenとして出力しない。
 * @param array $data default null nullの場合、$this->dataからHidden生成、そうえない場合、$dataからHidden生成
 * @return string hidden string
 */
	public function hiddenVars($modelName, $fieldList = array(), $isEmpty = true, $data = null) {
		$ret = "";
		if(!isset($data)) {
			$data = $this->data;
		}
		if(!isset($data[$modelName])) {
			return $ret;
		}

		foreach ($data[$modelName] as $key => $val) {
			if(is_array($val)){
				foreach( $val as $key2 => $val2 ){
					if(is_array($val2)){
						foreach( $val2 as $key3 => $val3 ){
							if((count($fieldList) == 0 || in_array($key3, $fieldList)) && ($isEmpty == true || $val3 !== '')) {
								$val3 = ($val3 === false) ? _OFF : $val3;
								$ret .= $this->hidden("$modelName.$key.$key2.$key3", array('value' => $val3))."\n";
							}
						}
					} else if((count($fieldList) == 0 || in_array($key2, $fieldList)) && ($isEmpty == true || $val2 !== '')) {
						$val2 = ($val2 === false) ? _OFF : $val2;
						$ret .= $this->hidden("$modelName.$key.$key2", array('value' => $val2))."\n";
					}
				}
			} else if((count($fieldList) == 0 || in_array($key, $fieldList)) && ($isEmpty == true || $val !== '')) {
				$val = ($val === false) ? _OFF : $val;
				$ret .= $this->hidden("$modelName.$key", array('value' => $val))."\n";
			}
		}
		return $ret;
	}
}