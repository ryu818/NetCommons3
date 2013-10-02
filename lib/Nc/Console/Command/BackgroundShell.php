<?php
/**
 * 背景画像セットアップ
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Block.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BackgroundShell extends AppShell {
/**
 * Model name
 *
 * @var array
 */
	public $uses = array('Background');

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
            __d('console', 'Background Manager')
		)->addSubcommand('update_all', array(
        	'help' => __d('console', 'Updating All background.'),
			'parser' => array(
				'description' => array(
					__d('console', 'Update the list of background image to the page style. The background image, please save it in the [webroot/img/backgrounds/(images|patterns)] below. [patterns] means the background pattern, [images] means the background image. If you want to register more than one background of the same type, create a folder background name, please save in the form of [(category_name)_(color)_(serial number ).(extension)] under it. For one, please save it in the form of [(background_name)_(category_name)_(color).(extension)].')),
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
 * 背景一括アップデート処理
 *
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function update_all() {
		$this->_setlang();
		
		if(!$this->params['force']) {
			if(strtolower($this->in(__d('console', 'Updating all the backgrounds.Are you sure to proceed?'),
					array('y', 'n'), 'n')) == 'n') {
				return;
			}
		}
		
		if(!$this->Background->updateAllInit()) {
			throw new InternalErrorException(__('Failed to register the database, (%s).', 'backgrounds'));
		}
		$this->out(__d('console', 'Succeeded  to update the background.'));
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
