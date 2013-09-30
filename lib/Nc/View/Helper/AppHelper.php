<?php
/**
 * Application level View Helper
 *
 * This file is application-wide helper file. You can put all
 * application-wide helper-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Helper
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('Helper', 'View');

/**
 * Application helper
 *
 * Add your application-wide methods in the class below, your helpers
 * will inherit them.
 *
 * @package       app.View.Helper
 */
class AppHelper extends Helper {
/**
 * Generates a DOM ID for the selected element, if one is not set.
 * Uses the current View::entity() settings to generate a CamelCased id attribute.
 *
 * @param array|string $options Either an array of html attributes to add $id into, or a string
 *   with a view entity path to get a domId for.
 * @param string $id The name of the 'id' attribute.
 * @return mixed If $options was an array, an array will be returned with $id set. If a string
 *   was supplied, a string will be returned.
 */
	public function domId($options = null, $id = 'id') {
		if (is_array($options) && array_key_exists($id, $options) && $options[$id] === null) {
			unset($options[$id]);
			return $options;
		} elseif (!is_array($options) && $options !== null) {
			$this->setEntity($options);
			return $this->domId();
		}
		
		$entity = $this->entity();
		$model = array_shift($entity);
		$dom = $model . implode('', array_map(array('Inflector', 'camelize'), $entity));
// Add for NetCommons Extentions By Ryuji.M --START
		if(isset($this->_View->viewVars['id'])) {
			$dom .= $this->_View->viewVars['id'];
		}
// Add for NetCommons Extentions By Ryuji.M --E N D
		if (is_array($options) && !array_key_exists($id, $options)) {
			$options[$id] = $dom;
		} elseif ($options === null) {
			return $dom;
		}
		return $options;
	}

/**
 * Finds URL for specified action.
 *
 * Returns an URL pointing at the provided parameters.
 *
 * @param string|array $url Either a relative string url like `/products/view/23` or
 *    an array of url parameters. Using an array for URLs will allow you to leverage
 *    the reverse routing features of CakePHP.
 * @param boolean $full If true, the full base URL will be prepended to the result
 * @return string Full translated URL with base path.
 * @link http://book.cakephp.org/2.0/en/views/helpers.html
 */
	public function url($url = null, $full = false) {
// Add for NetCommons Extentions By Ryuji.M --START
		if (!empty($url)) {
			return parent::url($url, $full);
		}

		$block_type = Configure::read(NC_SYSTEM_KEY.'.block_type');
		if(isset($block_type) && $block_type == 'blocks') {
			$here = $this->request->here;
			$here = preg_replace('/(.*)\/active-blocks\/([0-9]*)/i', '$1/blocks/$2', $here);
		
			$url = isset($here) ? $here : '/';
		}
			
		if ($full && defined('FULL_BASE_URL')) {
			$url = FULL_BASE_URL . $url;
		}
		return $url;
// Add for NetCommons Extentions By Ryuji.M --E N D
	}
}
