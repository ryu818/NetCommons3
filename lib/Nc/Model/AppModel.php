<?php
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
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
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Model', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {
/**
 * Saves model data (based on white-list, if supplied) to the database. By
 * default, validation occurs before save.
 *
 * <pre>
 * created_user_id, modified_user_id, created_user_name, modified_user_nameを自動でセット
 * </pre>
 *
 * @param array $data Data to save.
 * @param boolean|array $validate Either a boolean, or an array.
 *   If a boolean, indicates whether or not to validate before saving.
 *   If an array, allows control of validate, callbacks, and fieldList
 * @param array $fieldList List of fields to allow to be written
 * @return mixed On success Model::$data if its not empty or true, false on failure
 * @link http://book.cakephp.org/2.0/en/models/saving-your-data.html
 */
	public function save($data = null, $validate = true, $fieldList = array()) {
		$fields = array();
		$this->set($data);
		if (isset($this->data[$this->alias])) {
			$fields = array_keys($this->data[$this->alias]);
		} else {
			$fields = array_keys($data);
			$this->data[$this->alias] = $data;
		}
		$user = Configure::read(NC_SYSTEM_KEY.'.user');

		$id = isset($user['id']) ? $user['id'] : _OFF;
		$usename = isset($user['handle']) ? $user['handle'] : '';

		if ($this->hasField('created_user_id') && empty($this->data[$this->alias][$this->primaryKey])
			 && (!in_array('created_user_id', $fields) || !isset($this->data[$this->alias]['created_user_id']))) {
			if(count($fieldList) > 0)
				$fieldList[] = 'created_user_id';
			$this->data[$this->alias]['created_user_id'] = $id;
		}
		if ($this->hasField('modified_user_id')) {
			if(count($fieldList) > 0)
				$fieldList[] = 'modified_user_id';
			$this->data[$this->alias]['modified_user_id'] = $id;
		}

		if ($this->hasField('created_user_name') && empty($this->data[$this->alias][$this->primaryKey])
			&&(!in_array('created_user_name', $fields) || !isset($this->data[$this->alias]['created_user_name']))) {
			if(count($fieldList) > 0)
				$fieldList[] = 'created_user_name';
			$this->data[$this->alias]['created_user_name'] = $usename;
		}
		if ($this->hasField('modified_user_name')) {
			if(count($fieldList) > 0)
				$fieldList[] = 'modified_user_name';
			$this->data[$this->alias]['modified_user_name'] = $usename;
		}

		/*if (isset($this->data) && isset($this->data[$this->name]))
		 unset($this->data[$this->name]['modified']);
		if (isset($data) && isset($data[$this->name]))
			unset($data[$this->name]['modified']);*/
		return parent::save(null, $validate, $fieldList);
	}

/**
 * Saves the value of a single field to the database, based on the current
 * model ID.
 *
 * <pre>
 * modified_user_id, modified_user_nameを自動でセット
 * </pre>
 *
 * @param string $name Name of the table field
 * @param mixed $value Value of the field
 * @param boolean|array $validate Either a boolean, or an array.
 *   If a boolean, indicates whether or not to validate before saving.
 *   If an array, allows control of 'validate' and 'callbacks' options.
 * @return boolean See Model::save()
 * @see Model::save()
 * @link http://book.cakephp.org/2.0/en/models/saving-your-data.html#model-savefield-string-fieldname-string-fieldvalue-validate-false
 */
	public function saveField($name, $value, $validate = false) {
		$id = $this->id;
		$this->create(false);

		if (is_array($validate)) {
			$options = array_merge(array('validate' => false, 'fieldList' => array($name)), $validate);
		} else {
			$options = array('validate' => $validate, 'fieldList' => array($name));
		}
		$options['fieldList'][] = 'modified';
		$options['fieldList'][] = 'modified_user_id';
		$options['fieldList'][] = 'modified_user_name';
		return $this->save(array($this->alias => array($this->primaryKey => $id, $name => $value)), $options);
	}
	
/**
 * Updates multiple model records based on a set of conditions.
 * 
 * <pre>
 * modified, modified_user_id, modified_user_nameを自動でセット
 * </pre>
 *
 * @param array $fields Set of fields and values, indexed by fields.
 *    Fields are treated as SQL snippets, to insert literal values manually escape your data.
 * @param mixed $conditions Conditions to match, true for all records
 * @return boolean True on success, false on failure
 * @link http://book.cakephp.org/2.0/en/models/saving-your-data.html#model-updateall-array-fields-array-conditions
 */
	public function updateAll($fields, $conditions = true) {
		$user = Configure::read(NC_SYSTEM_KEY.'.user');

		$id = isset($user['id']) ? $user['id'] : _OFF;
		$usename = isset($user['handle']) ? $user['handle'] : '';
		
		if ($this->hasField('modified') && !isset($fields[$this->alias.'.modified'])) {
			$db = $this->getDataSource();
			$default = array('formatter' => 'date');
			$colType = array_merge($default, $db->columns[$this->getColumnType('modified')]);
			
			if (!array_key_exists('format', $colType)) {
				$time = strtotime('now');
			} else {
				$time = call_user_func($colType['formatter'], $colType['format']);
			}
			error_log(print_r($colType, true)."\n\n", 3, LOGS."/error.log");
			error_log(print_r($time, true)."\n\n", 3, LOGS."/error.log");
			
			$fields[$this->alias.'.modified'] = "'". $time. "'";
		}
		if ($this->hasField('modified_user_id') && !isset($fields[$this->alias.'.modified_user_id'])) {
			$fields[$this->alias.'.modified_user_id'] = "'". $id. "'";
		}

		if ($this->hasField('modified_user_name') && !isset($fields[$this->alias.'.modified_user_name'])) {
			$fields[$this->alias.'.modified_user_name'] = "'". $usename. "'";
		}
		return parent::updateAll($fields, $conditions);
	}

}
