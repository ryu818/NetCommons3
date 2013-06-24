<?php
/**
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('FormAuthenticate', 'Controller/Component/Auth');

/**
 * An authentication adapter for AuthComponent.  Provides the ability to authenticate using POST
 * data.  Can be used by configuring AuthComponent to use it via the AuthComponent::$authenticate setting.
 *
 * {{{
 *	$this->Auth->authenticate = array(
 *		'Form' => array(
 *			'scope' => array('User.active' => 1)
 *		)
 *	)
 * }}}
 *
 * When configuring FormAuthenticate you can pass in settings to which fields, model and additional conditions
 * are used. See FormAuthenticate::$settings for more information.
 *
 * @package       Cake.Controller.Component.Auth
 * @since 2.0
 * @see AuthComponent::$authenticate
 */
class MyFormAuthenticate extends FormAuthenticate {

/**
 * Find a user record using the standard options.
 *
 * Sessionに保持するfieldsを絞る
 *
 * @param string $username The username/identifier.
 * @param string $password The unhashed password.
 * @return Mixed Either false on failure, or an array of user data.
 */
	protected function _findUser($username, $password) {
		$userModel = $this->settings['userModel'];
		list($plugin, $model) = pluginSplit($userModel);
		$fields = $this->settings['fields'];

		$conditions = array(
			$model . '.' . $fields['username'] => $username,
			$model . '.' . $fields['password'] => $this->_password($password),
		);
		if (!empty($this->settings['scope'])) {
			$conditions = array_merge($conditions, $this->settings['scope']);
		}
		$result = ClassRegistry::init($userModel)->find('first', array(
			'fields' => $this->settings['findFields'],
			'conditions' => $conditions,
			'recursive' => (int)$this->settings['recursive'],
			'contain' => $this->settings['contain'],
		));
		if (empty($result) || empty($result[$model])) {
			return false;
		}
		$user = $result[$model];
		unset($user[$fields['password']]);
		unset($result[$model]);
		if(isset($result['Authority'])) {
			// 権限関連をAdd
			return array_merge($user, $result['Authority']);
		}
		return array_merge($user, $result);
	}
}
