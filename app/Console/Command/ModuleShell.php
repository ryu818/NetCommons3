<?php
/**
 * モジュールインストール、アップデート、アンインストールシェル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Block.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class ModuleShell extends AppShell {
/**
 * Model name
 *
 * @var array
 */
	public $uses = array('Module.ModuleAdmin');

/**
 * 言語
 *
 * @var string
 */
	private $_lang = 'jpn';		// 固定：optionsに言語を含めても、getOptionParser後に言語セットされるため、addOptionsで指定できない。

/**
 * Main
 *
 * @param   void
 * @return  string
 * @since   v 3.0.0.0
 */
	public function main() {
		return $this->out($this->OptionParser->help());
	}

/**
 * Gets the option parser instance and configures it.
 * By overriding this method you can configure the ConsoleOptionParser before returning it.
 *
 * @return ConsoleOptionParser
 * @since   v 3.0.0.0
 */
	public function getOptionParser() {
		$this->_setlang();

		$parser = parent::getOptionParser();
		return $parser->description(
            __d('console', 'Module Manager')
		)->addSubcommand('install', array(
        	'help' => __d('module', 'Install'),
			'parser' => array(
				'description' => array(
					__d('console', 'Install the module.')),
			'options' => array(
				'plugin' => array(
					'short' => 'p',
					'help' => __d('console', 'The plugin to use.'),
					'default' => '',
					'required' => true
				),
				'force' => array(
					'short' => 'f',
					'help' => __d('console', 'Force "module" to install.'),
					'boolean'=> true
				)
			)
		)))->addSubcommand('update', array(
			'help' => __d('module', 'Update'),
			'parser' => array(
				'description' => array(
					__d('console', 'Updating the module.')),
			'options' => array(
				'plugin' => array(
					'short' => 'p',
					'help' => __d('console', 'The plugin to use.'),
					'default' => '',
					//'required' => true
				),
				'all' => array(
					'short' => 'a',
					'help' => __d('console', 'Updating All module.'),
					'boolean'=> true
				),
				'force' => array(
					'short' => 'f',
					'help' => __d('console', 'Force "module" to update.'),
					'boolean'=> true
				)
			)
		)))->addSubcommand('uninstall', array(
			'help' => __d('module', 'Uninstall'),
			'parser' => array(
				'description' => array(
					__d('console', 'Uninstall the module.')),
			'options' => array(
				'plugin' => array(
					'short' => 'p',
					'help' => __d('console', 'The plugin to use.'),
					'default' => '',
					'required' => true
				),
				'force' => array(
					'short' => 'f',
					'help' => __d('console', 'Force "module" to uninstall.'),
					'boolean'=> true
				)
			)
		)))->addSubcommand('update_all', array(
				'help' => __d('module', 'Update-All'),
				'parser' => array(
					'description' => array(
							__d('console', 'Updating All module.')),
				'options' => array(
					'force' => array(
							'short' => 'f',
							'help' => __d('console', 'Force "module" to update.'),
							'boolean'=> true
					)
				)
		)));
	}

/**
 * モジュールインストール処理
 *
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function install() {
		$this->_setlang();

		$err_post_fix = '[example: --plugin Announcement]';
		if(empty($this->params['plugin'])) {
			$this->error(__d('console', 'Error'), __d('console', 'Please specify the module name.').$err_post_fix);
		}

		if(!$this->params['force']) {
			if(strtolower($this->in(str_replace('<br />', '', __d('module', 'Installing the module %s.<br />Are you sure to proceed?', $this->params['plugin'])),
					array('y', 'n'), 'n')) == 'n') {
				return;
			}
		}

		list($success_mes, $error_mes) = $this->ModuleAdmin->installModule($this->params['plugin']);
		foreach($success_mes as $success) {
			$this->out(str_replace('<br />', '',$success));
		}
		foreach($error_mes as $error) {
			$this->err(str_replace('<br />', '',$error));
		}
		if(count($error_mes) > 0) {
			$this->error(__d('console', 'Error'), __d('console', 'Failed to %s.', __d('module', 'Install')));
		}
	}

/**
 * モジュールアップデート処理
 *
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function update() {
		$this->_setlang();

		if(!empty($this->params['all'])) {
			// 一括アップロード
			$this->update_all();
			return;
		}

		$err_post_fix = '[example: --plugin Announcement]';
		if(empty($this->params['plugin'])) {
			$this->error(__d('console', 'Error'), __d('console', 'Please specify the module name.').$err_post_fix);
		}

		if(!$this->params['force']) {
			if(strtolower($this->in(str_replace('<br />', '', __d('module', 'Updating the module %s.<br />Are you sure to proceed?', $this->params['plugin'])),
					array('y', 'n'), 'n')) == 'n') {
				return;
			}
		}

		list($success_mes, $error_mes) = $this->ModuleAdmin->updateModule($this->params['plugin']);
		foreach($success_mes as $success) {
			$this->out(str_replace('<br />', '',$success));
		}
		foreach($error_mes as $error) {
			$this->err(str_replace('<br />', '',$error));
		}
		if(count($error_mes) > 0) {
			$this->error(__d('console', 'Error'), __d('console', 'Failed to %s.', __d('module', 'Update')));
		}
	}

/**
 * モジュール一括アップデート処理
 *
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function update_all() {
		$this->_setlang();

		if(!$this->params['force']) {
			if(strtolower($this->in(str_replace('<br />', '', __d('module', 'Updating all the modules<br />Are you sure to proceed?')),
					array('y', 'n'), 'n')) == 'n') {
				return;
			}
		}

		list($success_mes, $error_mes) = $this->ModuleAdmin->updateAllModule();
		foreach($success_mes as $success) {
			$this->out(str_replace('<br />', '',$success));
		}
		foreach($error_mes as $error) {
			$this->err(str_replace('<br />', '',$error));
		}
		if(count($error_mes) > 0) {
			$this->error(__d('console', 'Error'), __d('console', 'Failed to %s.', __d('module', 'Update-All')));
		}
	}

/**
 * モジュールインストール処理
 *
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function uninstall() {
		$this->_setlang();

		$err_post_fix = '[example: --plugin Announcement]';
		if(empty($this->params['plugin'])) {
			$this->error(__d('console', 'Error'), __d('console', 'Please specify the module name.').$err_post_fix);
		}

		if(!$this->params['force']) {
			if(strtolower($this->in(str_replace('<br />', '', __d('module', 'Uninstalling the module %s.<br />Are you sure to proceed?', $this->params['plugin'])),
					array('y', 'n'), 'n')) == 'n') {
				return;
			}
		}

		list($success_mes, $error_mes) = $this->ModuleAdmin->uninstallModule($this->params['plugin']);
		foreach($success_mes as $success) {
			$this->out(str_replace('<br />', '',$success));
		}
		foreach($error_mes as $error) {
			$this->err(str_replace('<br />', '',$error));
		}
		if(count($error_mes) > 0) {
			$this->error(__d('console', 'Error'), __d('console', 'Failed to %s.', __d('module', 'Uninstall')));
		}
	}

/**
 * 言語設定
 *
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	private function _setlang() {
		App::uses('L10n', 'I18n');
		$L10n = new L10n();
		$catalog = $L10n->catalog($this->_lang);
		Configure::write(NC_CONFIG_KEY.'.'.'language', $this->_lang);
		Configure::write(NC_SYSTEM_KEY.'.locale', $catalog['locale']);
	}
}
