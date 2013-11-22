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
 * construct
 * @param integer|string|array $id Set this ID for this model on startup, can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);;

		$defaultValidate = $this->constructDefault();

		$this->validate = array_merge($defaultValidate, array(
			'type' => array(
				'inList' => array(
					'rule' => array('inList', array(
						NC_PAGE_TYPE_FONT_ID,
						NC_PAGE_TYPE_BACKGROUND_ID,
						NC_PAGE_TYPE_DISPLAY_ID,
						NC_PAGE_TYPE_EDIT_CSS_ID,
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
			'original_background_image' => array(
				'invalidUploadImage'  => array(
					'rule' => array('_invalidUploadImage'),
					'last' => true,
					'message' => __('It contains an invalid string.')
				),
			),
			'original_background_repeat' => array(
					'inList' => array(
							'rule' => array('inList', array(
									'',
									'no-repeat',
									'repeat',
									'repeat-x',
									'repeat-y',
									'full',
							), false),
							'allowEmpty' => false,
							'message' => __('It contains an invalid string.')
					),
			),
			'original_background_position' => array(
					'inList' => array(
							'rule' => array('inList', array(
									'',
									'left top',
									'center top',
									'right top',
									'left center',
									'center center',
									'right center',
									'left bottom',
									'center bottom',
									'right bottom',
							), false),
							'allowEmpty' => false,
							'message' => __('It contains an invalid string.')
					),
			),
			'original_background_attachment' => array(
					'inList' => array(
							'rule' => array('inList', array(
									'',
									'fixed',
									'scroll',
							), false),
							'allowEmpty' => false,
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

		if(!isset($this->data[$this->alias]['style']) || !is_array($this->data[$this->alias]['style'])) {
			return true;
		}
		$propertyElements = explode(',', PAGES_STYLE_PROPERTY_ELEMENTS_WHITE_LIST);
		$propertyKeys = explode(',', PAGES_STYLE_PROPERTY_KEYS_WHITE_LIST);
		$borderStyles = explode(',', PAGES_STYLE_BORDER_STYLE);
		$fonts = explode(',', PAGES_STYLE_FONT);

		$backgroundAttachments = explode(',', PAGES_STYLE_BACKGROUND_ATTACHMENT);
		$backgroundRepeat = explode(',', PAGES_STYLE_BACKGROUND_REPEAT);
		$backgroundPosition = explode(',', PAGES_STYLE_BACKGROUND_POSITION_DATA);
		$backgroundSize = explode(',', PAGES_STYLE_BACKGROUND_SIZE);

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

						if($value != 'none' && !preg_match("/^[a-zA-Z0-9-_\/~@.]+$/i", $value)) {
							return false;
						}
						break;
					case 'background-attachment':
						if(!in_array($value, $backgroundAttachments)) {
							return false;
						}
						break;
					case 'background-position':
						if(!in_array($value, $backgroundPosition)) {
							return false;
						}
						break;
					case 'background-repeat':
						if(!in_array($value, $backgroundRepeat)) {
							return false;
						}
						break;
					case 'background-size':
						if(!in_array($value, $backgroundSize)) {
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
 * original_background_imageチェック
 * 新規選択時に自分自身のアップロード画像か、管理者のアップロード画像ならばOK
 *
 * @param  array     $check
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function _invalidUploadImage($check) {
		$Authority = ClassRegistry::init('Authority');
		$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
		$loginUserId = isset($loginUser['id']) ? $loginUser['id'] : _OFF;
		$isAdmin = ($Authority->getUserAuthorityId($loginUser['hierarchy']) == NC_AUTH_ADMIN_ID) ? true : false;

		$created = true;

		if($isAdmin) {
			return true;
		}
		$check = array_shift($check);
		if(!empty($this->data[$this->alias]['id'])) {
			$pageStyle = $this->findById($this->data[$this->alias]['id']);
			if($pageStyle[$this->alias]['original_background_image'] == $check) {
				$created = false;
			}
		}
		if($created) {
			$checkArr = explode('.', $check);
			$Upload = ClassRegistry::init('Upload');
			$upload = $Upload->findById($checkArr[0]);
			if ($upload[$Upload->alias]['created_user_id'] != $loginUserId || $upload[$Upload->alias]['extension'] != $checkArr[1]) {
				return false;
			}
		}
		return true;
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
		$path = 'theme' . '/' . 'page_styles' . '/';
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
 * 更新後処理
 * 		 uploadLink更新処理
 * @param   boolean $created
 * @param   array   $options
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function afterSave($created, $options = array()) {
		if(!empty($this->data[$this->alias]['original_background_image'])) {
			$UploadLink = ClassRegistry::init('UploadLink');
			$uploadArr = explode('.', $this->data[$this->alias]['original_background_image']);
			$uploadId = intval($uploadArr[0]);
			$doSave = true;
			$conditions = array(
				'unique_id'=>$this->id,
				'model_name'=>'PageStyle',
				'field_name'=>'id',
			);
			$uploadLink = $UploadLink->find('first', array(
				'conditions' => $conditions
			));
			if(isset($uploadLink[$UploadLink->alias])) {
				if($uploadLink[$UploadLink->alias]['upload_id'] == $uploadId) {
					$doSave = false;
				}
				$uploadLink[$UploadLink->alias]['upload_id'] = $uploadId;
			} else {
				$uploadLink[$UploadLink->alias] = array(
					'upload_id' => $uploadId,
					'plugin'=>'Page',
					'content_id'=>0,
					'unique_id'=>$this->id,
					'model_name'=>'PageStyle',
					'field_name'=>'id',
					'access_hierarchy'=>0,
					'download_password'=>'',
					'check_component_action'=>'Page.PageDownload',
					'is_use' => _ON,
				);
			}
			if($doSave) {
				$UploadLink->create();
				$UploadLink->save($uploadLink);
			}
		}
		return parent::afterSave($created, $options);
	}

/**
 * 削除後処理：ファイル削除
 * uploadLinkを削除
 * @return  void
 * @since   v 3.0.0.0
 */
	public function afterDelete() {
		$UploadLink = ClassRegistry::init('UploadLink');
		$conditions = array(
			'unique_id'=>$this->id,
			'model_name'=>'PageStyle',
			'field_name'=>'id',
		);
		$UploadLink->deleteAll($conditions);
		return parent::afterDelete();
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
