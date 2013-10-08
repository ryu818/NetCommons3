<?php
/**
 * PageStyleモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageStyle extends AppModel
{
/**
 * Behavior
 *
 * @var array
 */
	public $actsAs = array('File', 'PageStyle');

/**
 * CSS拡張子
 *
 * @var array
 */
	private $css_extension = '.css';

/**
 * バリデート処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct() {
		parent::__construct();
		
		$defaultValidate = $this->constructDefault();

		$this->validate = array_merge($defaultValidate, array(
			'type' => array(
				'inList' => array(
					'rule' => array('inList', array(
						NC_PAGE_TYPE_FONT_ID,
						NC_PAGE_TYPE_BACKGROUND_ID,
						NC_PAGE_TYPE_DISPLAY_ID,
						NC_PAGE_TYPE_CUSTOM_ID,
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				),
				'invalidFileContent'  => array(
					'rule' => array('_invalidFileContent'),
					'last' => true,
					'message' => __('It contains an invalid string.')
				),
			),
			'align' => array(
				'inList' => array(
					'rule' => array('inList', array(
						'',
						'left',
						'center',
						'right',
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				),
			),
			'width' => array(
				'invalidSize'  => array(
					'rule' => array('_invalidSize'),
					'last' => true,
					'message' => __('It contains an invalid string.')
				),
			),
			'height' => array(
				'invalidSize'  => array(
					'rule' => array('_invalidSize'),
					'last' => true,
					'message' => __('It contains an invalid string.')
				),
			),
			'file' => array(
				'notEmpty'  => array(
					'rule' => array('notEmpty'),
					'message' => __('Please be sure to input.')
				),
				'maxLength'  => array(
					'rule' => array('maxLength', 48),
					'message' => __('The input must be up to %s characters.', 48)
				),
			),
		));
	}

/**
 * ファイル内容のチェック
 * 管理者ならばチェックしない。
 *
 * @param  array     $check
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function _invalidFileContent($check) {
		$Authority = ClassRegistry::init('Authority');
		$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
		$loginUserId = isset($loginUser['id']) ? $loginUser['id'] : _OFF;
		$isAdmin = ($Authority->getUserAuthorityId($loginUser['hierarchy']) == NC_AUTH_ADMIN_ID) ? true : false;
		
		if(!is_array($this->data[$this->alias]['style'])) {
			return false;
		}
		$propertyElements = explode(',', PROPERTY_ELEMENTS_WHITE_LIST);
		$propertyKeys = explode(',', PROPERTY_KEYS_WHITE_LIST);
		$borderStyles = explode(',', PAGES_STYLE_BORDER_STYLE);
		$fonts = explode(',', PAGES_STYLE_FONT);
		
		$backgroundAttachments = explode(',', PAGES_STYLE_BACKGROUND_ATTACHMENT);
		$backgroundRepeat = explode(',', PAGES_STYLE_BACKGROUND_REPEAT);
		$backgroundPositionX = explode(',', PAGES_STYLE_BACKGROUND_POSITION_X);
		$backgroundPositionY = explode(',', PAGES_STYLE_BACKGROUND_POSITION_Y);
		
		foreach($this->data[$this->alias]['style'] as $propertyElement => $property) {
			if(!$isAdmin && !in_array($propertyElement, $propertyElements)) {
				return false;
			}
			foreach($property as $key => $value) {
				if(!$isAdmin && !in_array($key, $propertyKeys)) {
					return false;
				}
				switch ($key) {
					case 'font-family':
						if(!in_array($value, $fonts)) {
							return false;
						}
						break;
					case 'color':
					case 'border-color':
					case 'border-top-color':
					case 'background-color':
						if(!preg_match("/^#[a-fA-F0-9]{6}$/", $value)) {
							return false;
						}
						break;
					case 'font-size':
					case 'line-height':
						if(!preg_match("/^[0-9]+%$/i", $value)) {
							return false;
						}
						break;
					case 'border-style':
					case 'border-top-style':
						if(!in_array($value, $borderStyles)) {
							return false;
						}
						break;
					case 'margin-right':
					case 'margin-left':
						if(!preg_match("/^[0-9]+px$/i", $value) && $value != 'auto') {
							return false;
						}
						break;
					case 'margin-top':
					case 'margin-bottom':
					case 'border-radius':
						if(!preg_match("/^[0-9]+px$/i", $value)) {
							return false;
						}
						break;
					case 'width':
					case 'height':
						//if(!preg_match("/^[0-9]+px$/i", $value) && !preg_match("/^[0-9]+%$/i", $value) && $value != 'auto') {
						//	return false;
						//}
						break;
					case 'background-image':
						//if($value != 'none' && !preg_match("/^url\(\"[^\)]+\"\)$/i", $value)) {
						if($value != 'none' && !preg_match("/^[^']?$/i", $value)) {
							return false;
						}
						break;
					case 'background-attachment':
						if(!in_array($value, $backgroundAttachments)) {
							return false;
						}
						break;
					case 'background-position-x':
						if(!in_array($value, $backgroundPositionX)) {
							return false;
						}
						break;
					case 'background-position-y':
						if(!in_array($value, $backgroundPositionY)) {
							return false;
						}
						break;
					case 'background-repeat':
						if(!in_array($value, $backgroundRepeat)) {
							return false;
						}
						break;
					case 'float':
						if($value != 'left' && $value != 'right' && $value != 'none') {
							return false;
						}
						break;
				}
			}
		}
		return true;
	}

/**
 * width,heightチェック
 *
 * @param  array     $check
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function _invalidSize($check) {
		$check = array_shift($check);
		if($check == '100%' || $check == 'auto') {
			return true;
		}
		return Validation::numeric($check);
	}

/**
 * 適用範囲内ページスタイルデータ取得
 * @param   string $type
 * @param   Model page $page
 * @return  array $pageStyle[pageStyle.type] = Model PageStyle
 * @since   v 3.0.0.0
 */
	public function findScopeStyle( $type = 'all', $page ) {
		$Asset = ClassRegistry::init('Asset');
		$postfix = $Asset->getPostFix();
		$path = 'theme' . DS . 'page_styles' . DS;
		$pageStyles = $this->findScope('all', $page);
		// type毎の優先順位が高いもののみ取得
		$ret = array();
		foreach($pageStyles as $pageStyle) {
			if($type == 'first' && !isset($ret[$pageStyle[$this->alias]['type']])) {
				$pageStyle[$this->alias]['file'] = $path . $pageStyle[$this->alias]['file'] . $postfix;
				$ret[$pageStyle[$this->alias]['type']] = $pageStyle;
			} else if($type == 'all'){
				$ret[$pageStyle[$this->alias]['type']][] = $pageStyle;
			}
		}
		return $ret;
	}

/**
 * 登録前処理
 * 		ファイル作成。fileデータ作成
 * @param   array $options
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function beforeSave($options = array()) {
		if(!empty($this->data[$this->alias]['id'])) {
			$pageStyle = $this->findById($this->data[$this->alias]['id']);
			if(isset($pageStyle[$this->alias])) {
				$this->deleteCssFile($pageStyle[$this->alias]['file']);
			}
		}
		if(isset($this->data[$this->alias]['content'])) {
			$this->data[$this->alias]['file'] = $this->createCssFile($this->data[$this->alias]['scope'].'-'.$this->data[$this->alias]['type'].
					'-'.$this->data[$this->alias]['lang'].'-'.$this->data[$this->alias]['space_type'].'-'.$this->data[$this->alias]['page_id'], $this->data[$this->alias]['content']);
			unset($this->data[$this->alias]['content']);
		}
		return true;
	}

/**
 * ページスタイル用CSSファイル生成
 * @param   string    $key
 * @param   string    $content
 * @return  string    $css_file
 * @since   v 3.0.0.0
 */
	public function createCssFile($key, $content) {
		$path = $this->getPath();
		$hash = md5($key);
		$file_name = $this->getFile($hash);
		$css_file = $this->createFile($path, $file_name, $content, true);
		return $css_file;
	}

/**
 * ページスタイル用CSSファイル削除
 * @param   string    $css_file
 * @since   v 3.0.0.0
 */
	public function deleteCssFile($css_file) {
		$path = $this->getPath();
		$file_path = $path . $css_file;
		// ファイルが存在すれば削除
		if (file_exists($file_path)) {
			$this->deleteFile($file_path);
		}
	}

/**
 * ページスタイル用CSSファイル格納パス取得
 * @return  string    $css_file
 * @since   v 3.0.0.0
 */
	public function getPath() {
		$path = Configure::read('App.www_root') . 'theme' . DS . 'page_styles' . DS;
		return $path;
	}

/**
 * ページスタイル用CSSファイル名取得
 * @param   string   $hash
 * @return  string   $file
 * @since   v 3.0.0.0
 */
	public function getFile($hash) {
		$file = 'application-' . $hash . $this->css_extension;
		return $file;
	}
}
