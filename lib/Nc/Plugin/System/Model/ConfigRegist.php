<?php
/**
 * ConfigRegistモデル
 *
 * <pre>
 *  Configテーブル登録用モデル
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Block.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class ConfigRegist extends AppModel {
	public $useTable = 'configs';
	public $alias = 'ConfigRegist';

/**
 * 画面表示・非表示設定
 * $displayConditions
 * 		Valueの条件が正ならば、Keyエリアを表示しrequiredならばrequiredチェックを行う。
 * 		条件が誤ならばrequiredチェックを行わない。
 * 		条件は、typeがradio or selectのものに限る。
 * @param   string   $id
 * @param   array    $configs
 * @param   array    $requestConfigs
 * @return  void
 * @since   v 3.0.0.0
 */
	public function convertConfig($id, &$configs, &$requestConfigs) {
		$displayConditions = array(
			'autologin_use' => array(
				'autologin_cookie_name' => array(NC_AUTOLOGIN_ON, NC_AUTOLOGIN_LOGIN),
				'autologin_expires' => array(NC_AUTOLOGIN_ON, NC_AUTOLOGIN_LOGIN),
			),
			'is_maintenance' => array(
				'maintenance_text' => _ON,
			),
			'proxy_mode' => array(
				'proxy_host' => _ON,
				'proxy_port' => _ON,
				'proxy_user' => _ON,
				'proxy_pass' => _ON,
			),
			'mailmethod' => array(
				'sendmailpath' => 'sendmail',
				'smtphost' => array('smtp', 'smtpauth'),
				'smtpuser' => 'smtpauth',
				'smtppass' => 'smtpauth',
				'smtptls' => array('smtp', 'smtpauth'),
			),
			'autoregist_use' => array(
				'autoregist_approver' => _ON,
				'autoregist_use_input_key' => _ON,
				'autoregist_author' => _ON,
				'autoregist_use_items' => _ON,
				'autoregist_disclaimer' => _ON,
				'mail_approval_subject' => _ON,
				'mail_approval_body' => _ON,
			),
			'withdraw_membership_use' => array(
				'withdraw_disclaimer' => _ON,
				'withdraw_membership_send_admin' => _ON,
				'mail_withdraw_membership_subject' => _ON,
				'mail_withdraw_membership_body' => _ON,
			),
		);

		// Valueマージ
		foreach($requestConfigs as $name => $value) {
			if(!isset($configs[$name])) {
				if($name != 'first_startcommunity_id') {
					unset($requestConfigs[$name]);
				}
				continue;
			}
			$configs[$name]['preValue'] = $configs[$name]['value'];
			$configs[$name]['value'] = $value;
		}

		foreach($configs as $name => $config) {
			$configs[$name]['liId'] = Inflector::camelize($configs[$name]['name'].$id);
			if(isset($displayConditions[$name])) {
				if($configs[$name]['type'] == 'select') {
					$event = 'onchange';
				} else {
					$event = 'onclick';
				}
				$configs[$name][$event] = '';
				foreach($displayConditions[$name] as $displayKey => $displayValues) {
					$liId = Inflector::camelize($displayKey.$id);
					if(!is_array($displayValues)) {
						$configs[$name][$event] .= "if($(this).val() == '".$displayValues."') { $('#".$liId."').slideDown(); } else { $('#".$liId."').slideUp(); }";
					} else {
						$arr = '[';
						foreach($displayValues as $v) {
							if($arr != '[') {
								$arr .= ',';
							}
							$arr .= "'".$v."'";
						}
						$arr .= ']';
						$configs[$name][$event] .= "if($.inArray($(this).val(), ".$arr.") != '-1') { $('#".$liId."').slideDown(); } else { $('#".$liId."').slideUp(); }";
					}
					if((is_array($displayValues) || $configs[$name]['value'] != $displayValues) &&
						(!is_array($displayValues) || !in_array($configs[$name]['value'], $displayValues))) {
						// 非表示
						$configs[$displayKey]['display'] = 'none';
					}
				}
			}
		}
	}

/**
 * システム管理登録処理
 * @param   array    $configs
 * @param   array    $requestConfigs
 * @return  array|boolean false
 * @since   v 3.0.0.0
 */
	public function saveValues(&$configs, $requestConfigs) {
		$ret = true;

		// バリデート
		foreach($requestConfigs as $name => $value) {
			if(!isset($configs[$name])) {
				continue;
			}
			if(!isset($configs[$name]['display']) && $configs[$name]['required'] == _ON && !is_array($value) && !Validation::notEmpty($value)) {
				$this->invalidate($name, __('Please be sure to input.'));
				$ret = false;
				continue;
			}
			if($configs[$name]['minlength'] > 0 && !is_array($value) && !Validation::minLength($value, intval($configs[$name]['minlength']))) {
				$this->invalidate($name, __('The input must be at least %s characters.', $configs[$name]['minlength']));
				$ret = false;

			}
			if($configs[$name]['maxlength'] > 0 && !is_array($value) && !Validation::maxLength($value, intval($configs[$name]['maxlength']))) {
				$this->invalidate($name, __('The input must be up to %s characters.', $configs[$name]['maxlength']));
				$ret = false;
			}
			if($configs[$name]['regexp'] != '' && !is_array($value) && $value != '' && !Validation::custom($value, $configs[$name]['regexp'])) {
				$this->invalidate($name, __('It contains an invalid string.'));
				$ret = false;
			}

			if($ret == false) {
				if(isset($configs[$name]['display'])) {
					// 自動ログインの設定が「無効」、ログインの保存用のクッキーの名称「全角文字」の場合、
					// ログインの保存用のクッキーの名称を表示し、エラーメッセージを表示させる（自動ログインの設定が「無効」のまま）。
					if(isset($configs[$name]['display'])) {
						unset($configs[$name]['display']);
					}
				}
				continue;
			}

			$optionsKeys = null;
			if(is_array($configs[$name]['options']) && count($configs[$name]['options']) > 0) {
				$optionsKeys = array_keys($configs[$name]['options']);
			}
			if(isset($optionsKeys) && ($configs[$name]['type'] == 'radio' || $configs[$name]['type'] == 'select')) {
				if(!in_array($value, $optionsKeys)) {
					$this->invalidate($name, __('It contains an invalid string.'));
					$ret = false;
					continue;
				}
			} else if(isset($optionsKeys) && $configs[$name]['type'] == 'checkbox') {
				if(!is_array($value)) {
					$this->invalidate($name, __('It contains an invalid string.'));
					$ret = false;
					continue;
				} else {
					foreach($value as $v) {
						if(!in_array($v, $optionsKeys)) {
							$this->invalidate($name, __('It contains an invalid string.'));
							$ret = false;
							continue;
						}
					}
				}
			}
		}

		if($ret == true) {
			// 登録
			$ConfigLang = ClassRegistry::init('ConfigLang');

			$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
			foreach($requestConfigs as $name => $value) {
				if($name == 'first_startpage_id' && $value == '-3') {
					$value = isset($requestConfigs['first_startcommunity_id']) ? intval($requestConfigs['first_startcommunity_id']) : '-1';
				}
				if($name == 'first_startcommunity_id' || $configs[$name]['preValue'] == $value) {
					// 変更なし
					continue;
				}
				if($configs[$name]['lang_flag'] && $lang != 'en') {
					// ConfigLangに登録
					if(!$ConfigLang->saveKeys($configs[$name]['module_id'], $name, $lang, $value)) {
						$this->invalidate($name, __('Failed to register the database, (%s).', 'config_langs'));
						return false;
					}
				} else {
					// Config update
					App::uses('Sanitize','Utility');
					$fields = array(
						$this->alias.'.value' => "'".Sanitize::escape($value)."'"
					);
					$conditions = array(
						$this->alias.".id" => $configs[$name]['id'],
					);
					if(!$this->updateAll($fields, $conditions)) {
						$this->invalidate($name, __('Failed to update the database, (%s).', 'configs'));
						return false;
					}
				}
			}
		}

		return $ret;
	}

/**
 * 標準の開始ページ取得
 *
 * @param  boolean $isCommunity コミュニティーがあるかどうか
 * @return array $results
 * @since   v 3.0.0.0
 */
	public function findDefaultStartPage($isCommunity) {
		$Page = ClassRegistry::init('Page');
		$addParams = array(
			'conditions' => array(
				'Page.space_type' => NC_SPACE_TYPE_PUBLIC
			)
		);
		$pages = $Page->findViewable('thread', 'all', $addParams);

		$pages['-2'] = array('Page' => array(
			'id' => '-2',
			'page_name' => __('Myportal'),
			'thread_num' => '0',
		));
		$pages['-1'] = array('Page' => array(
			'id' => '-1',
			'page_name' => __('Private room'),
			'thread_num' => '0',
		));
		if($isCommunity) {
			$pages['-3'] = array('Page' => array(
				'id' => '-3',
				'page_name' => __('Community'),
				'thread_num' => '0',
			));
		}
		return $pages;
	}

/**
 * 標準の開始ページ取得(Group)
 *
 * @param  integer $limit
 * @param  integer $page
 * @return array($hasNext, $results)
 * @since   v 3.0.0.0
 */
	public function findDefaultStartPageGroup($limit, $page) {
		$Page = ClassRegistry::init('Page');
		$addParams = array(
			'conditions' => array(
				'Page.space_type' => NC_SPACE_TYPE_GROUP,
				'Page.thread_num' => 1
			),
			'offset' => $limit*($page - 1),
			//'page' => $page,
			'limit' => $limit + 1,
		);
		$pages = $Page->findViewableRoom('thread', 'all', $addParams);
		$count = count($pages);
		if($count == 0) {
			return array(false, $pages);
		}
		$hasNext = false;
		if($limit < $count) {
			array_pop($pages);
			$hasNext = true;
		}
		return array($hasNext, $pages);
	}
}