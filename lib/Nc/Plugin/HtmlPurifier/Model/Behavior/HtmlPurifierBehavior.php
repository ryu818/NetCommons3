<?php
App::uses('Purifier', 'HtmlPurifier.Lib');
/**
 * HtmlPurifierBehavior
 *
 * @author Florian Krämer
 * @copyright 2012 Florian Krämer
 * @license MIT
 */
class HtmlPurifierBehavior extends ModelBehavior {
// Modify for NetCommons Extentions By Ryuji.M --START
// https://github.com/burzum/HtmlPurifier/pull/3 のpull requestをあてる。
	public function setup(Model $Model, $settings = array()) {
		$this->settings[$Model->alias] = (array)$settings;
	}
/**
 * beforeSave
 *
 * @param Model $model
 * @return boolean
 */
	//public function beforeSave(Model $Model) {
	public function beforeSave(Model $Model, $options = array()) {
		extract($this->settings[$Model->alias]);

		foreach($fields as $field) {
			if (isset($Model->data[$Model->alias][$field])) {
				$Model->data[$Model->alias][$field] = $this->purifyHtml($Model, $Model->data[$Model->alias][$field], $config);
			}
		}

		return true;
	}
// Modify for NetCommons Extentions By Ryuji.M --END
/**
 * Cleans markup
 *
 * @param Model $Model
 * @param string $markup
 * @param string $config
 */
	public function purifyHtml(Model $Model, $markup, $config) {
		return Purifier::clean($markup, $config);
	}

}