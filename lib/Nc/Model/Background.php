<?php
/**
 * Backgroundモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class Background extends AppModel
{
/**
 * バックグランド保存ディレクトリ
 *
 * @var string
 */
	private $_backgroundsDirName = 'backgrounds';

/**
 * 背景パターン保存ディレクトリ
 *
 * @var string
 */
	private $_patternsDirName = 'patterns';

/**
 * 背景画像保存ディレクトリ
 *
 * @var string
 */
	private $_imagesDirName = 'images';

/**
 * 背景パターンType
 *
 * @var string
 */
	private $_patternType = 'pattern';

/**
 * 背景画像Type
 *
 * @var string
 */
	private $_imageType = 'image';

/**
 * default値
 *
 * @var array
 */
	private $_default = array(
		'group_id' => 0,
		'type' => '',
		'name' => '',
		'category' => 'Other',
		'color' => 'Other',
		'file_path' => '',
		'file_width' => 0,
		'file_height' => 0,
		'file_size' => 0,
	);

/**
 * GroupID
 *
 * @var integer
 */
	private $_groupID = 1;

/**
 * 適用範囲内ページスタイルデータ取得
 * @param   Model page $page
 * @return  array $pageStyle[pageStyle.type] = Model PageStyle
 * @since   v 3.0.0.0
 */
	public function findList( $type, $params) {
		$defaultParams = array(
			'fields' => array($this->alias.'.id', $this->alias.'.group_id', $this->alias.'.type', $this->alias.'.name', $this->alias.'.category', $this->alias.'.color', $this->alias.'.file_path'),
			'group' => array($this->alias.'.group_id'),
			'order' => array($this->alias.'.id')
		);
		$params = array_merge($defaultParams, $params);

		return $this->find($type, $params);
	}

/**
 * 背景一括アップデート処理
 *
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function updateAllInit() {
		App::uses('Folder', 'Utility');
		App::uses('File', 'Utility');
		$groupID = 1;
		$this->getDataSource()->truncate($this->table);
		$params = array_merge($this->_default, array(
			'group_id' => $groupID++,
			'type' => $this->_patternType,
			'name' => 'None',
		));

		$this->create();
		if(!$this->save($params)) {
			return false;
		}

		$params = array_merge($this->_default, array(
			'group_id' => $groupID++,
			'type' => $this->_imageType,
			'name' => 'None',
		));
		$this->create();
		if(!$this->save($params)) {
			return false;
		}

		$backgroundPath = 'img'. DS . $this->_backgroundsDirName;
		$paths = App::path('webroot');
		$pathArray = array(
			$this->_patternType => $this->_patternsDirName,
			$this->_imageType => $this->_imagesDirName
		);


		foreach ($paths as $path) {
			$path = $path . $backgroundPath;
			foreach($pathArray as $type => $subPath) {
				$readPath = $path . DS . $subPath;
				if (is_dir($readPath)) {
					$dir = new Folder($readPath);
					//$files = $dir->find('.*\.(jpg|gif|png)');	//(\.jpg|\.gif|\.png)
					$files = $dir->read(true, true);
					$params = array_merge($this->_default, array(
						'type' => $type
					));
					if(count($files[0]) > 0) {
						// ディレクトリ
						$insert = false;
						foreach($files[0] as $backgroundDir) {
							$params['name'] = Inflector::camelize(str_replace('_', ' ', $backgroundDir));
							$subDir = new Folder($readPath . DS . $backgroundDir);
							$subFiles = $subDir->find('.*\.(jpg|gif|png)');

							foreach($subFiles as $subFile) {
								$subFileArr = explode('.', $subFile);
								$subFileArr = explode('_', $subFileArr[0]);

								$fullPath = $readPath . DS . $backgroundDir . DS . $subFile;
								list($width, $height) = getimagesize($fullPath);

								$params['group_id'] = $groupID;
								if(count($subFileArr) > 1) {
									$params['category'] = $this->_getCategory($subFileArr[0]);
									$params['color'] = $this->_getColor($subFileArr[1]);
								} else {
									$params['color'] = 'Other';
									$params['category'] = 'Other';
								}
								$params['file_path'] = $backgroundDir . DS . $subFile;
								$params['file_width'] = $width;
								$params['file_height'] = $height;
								$params['file_size'] = filesize($fullPath);
								$this->create();
								if(!$this->save($params)) {
									return false;
								} else {
									$insert = true;
								}
							}
							if($insert) {
								$groupID++;
							}
						}
					}
					if(count($files[1]) > 0) {
						// ファイル
						foreach($files[1] as $subFile) {
							$bugName = preg_replace('/\..+$/', '', $subFile);
							$subFileArr = explode('_', $bugName);

							$fullPath = $readPath . DS . DS . $subFile;
							list($width, $height) = getimagesize($fullPath);
							$params['group_id'] = $groupID++;


							if(count($subFileArr) > 2) {
								$params['color'] = $this->_getColor($subFileArr[count($subFileArr) - 1]);
								unset($subFileArr[count($subFileArr) - 1]);
								$params['category'] = $this->_getCategory($subFileArr[count($subFileArr) - 1]);
								unset($subFileArr[count($subFileArr) - 1]);
								$params['name'] = Inflector::camelize(implode(' ', $subFileArr));
							} else {
								$params['color'] = 'Other';
								$params['category'] = 'Other';
								$bugName = implode(' ', $subFileArr);
								$subFileArr = explode('-', $bugName);
								$params['name'] = Inflector::camelize(implode(' ', $subFileArr));
							}
							$params['file_path'] = $subFile;
							$params['file_width'] = $width;
							$params['file_height'] = $height;
							$params['file_size'] = filesize($fullPath);
							$this->create();
							if(!$this->save($params)) {
								return false;
							}
						}
					}
				}
			}
		}
		return true;
	}

/**
 * カテゴリー種別取得
 *
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	private function _getCategory($category) {
		$category = Inflector::camelize($category);
		$categories = explode(',', NC_PAGES_BACKGROUND_CATEGORY_STYLE);
		if(!in_array($category, $categories)) {
			return 'Other';
		}
		return $category;
	}

/**
 * カラー種別取得
 *
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	private function _getColor($color) {
		$color = Inflector::camelize($color);
		$colors = explode(',', NC_PAGES_BACKGROUND_COLOR_STYLE);
		if(!in_array($color, $colors)) {
			return 'Other';
		}
		return $color;
	}
}