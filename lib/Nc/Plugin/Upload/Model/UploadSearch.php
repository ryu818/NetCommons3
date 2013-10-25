<?php
/**
 * UploadSearchモデル
 *
 * <pre>
 *  Uploadテーブル検索用モデル
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Block.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class UploadSearch extends AppModel {
	public $useTable = 'uploads';
	public $alias = 'UploadSearch';

	public $actsAs = array('TimeZone', 'File', 'Page');

/**
 * 検索条件初期値
 * @param   void
 * @return  Model UploadSearch
 * @since   v 3.0.0.0
 */
	public function findDefault() {
		$ret = array(
			'UploadSearch' => array(
				'file_type'=>'',
				'user_type'=>'',
				'plugin'=>'',
				'created'=>'',
				'order'=>'',
				'order_direction'=>'DESC',
				'text'=>'',
				'search_type'=>UPLOAD_SEARCH_CONDITION_FROM_FILE,
				'page'=> 1,
			)
		);

		return $ret;
	}

/**
 * 検索条件のプラグイン選択肢取得処理
 * @param   array $conditions
 * @param   string $conditions
 * @return  array
 * @since   v 3.0.0.0
 */
	public function findPluginOptions($conditions, $plugin = null) {
		$Module = ClassRegistry::init('Module');

		$params = array(
			'recursive' => -1,
			'fields' => array('UploadSearch.plugin'),
			'conditions' => $conditions,
			'order' => array('UploadSearch.plugin'=>'ASC'),
			'group' => array('UploadSearch.plugin'),
		);
		$plugins = $this->find('all', $params);
		if(!empty($plugin)) {
			$plugins[] = array(
				'UploadSearch' => array('plugin' => Inflector::camelize($plugin)),
			);
		}
		$uploadSearchPluginOptions = array('' => __('All'));
		foreach ($plugins as $plugin) {
			if($plugin['UploadSearch']['plugin'] == 'Upload') {
				continue;
			}
			$moduleName = $Module->loadModuleName($plugin['UploadSearch']['plugin']);
			$uploadSearchPluginOptions[$plugin['UploadSearch']['plugin']] = $moduleName;
		}
		return $uploadSearchPluginOptions;
	}

/**
 * 検索条件の日付選択肢取得処理
 * @param   array $conditions
 * @return  array
 * @since   v 3.0.0.0
 */
	public function findCreatedOptions($conditions) {
		$year = $this->nowDate('Y');
		$bufConditions = $conditions;
		$conditions = array_merge($conditions, array(
			'UploadSearch.year' => $year
		));

		$params = array(
			'recursive' => -1,
			'fields' => array('UploadSearch.year', 'UploadSearch.month'),
			'conditions' => $conditions,
			'order' => array('UploadSearch.year'=>'DESC', 'UploadSearch.month'=>'DESC'),
			'group' => array('UploadSearch.year', 'UploadSearch.month'),
		);
		$uploads = $this->find('all', $params);
		$uploadSearchCreatedOptions = array('' => __d('upload', 'Show all dates'));
		foreach ($uploads as $upload) {
			$year = $upload['UploadSearch']['year'];
			$month = $upload['UploadSearch']['month'];
			$uploadSearchCreatedOptions[$year.'-'.$month] = __('(%1$s-%2$s)', $year, $month);
		}

		$conditions = array_merge($bufConditions, array(
			'UploadSearch.year <' => $year
		));

		$params = array(
			'recursive' => -1,
			'fields' => array('UploadSearch.year', 'UploadSearch.month'),
			'conditions' => $conditions,
			'order' => array('UploadSearch.year'=>'DESC'),
			'group' => array('UploadSearch.year'),
			'limit' => UPLOAD_SEARCH_CREATED_YEARS_AGO,
		);
		$uploads = $this->find('all', $params);
		foreach ($uploads as $upload) {
			$year = $upload['UploadSearch']['year'];
			$uploadSearchCreatedOptions[$year] = __('(%s)', $year);
		}
		return $uploadSearchCreatedOptions;
	}

/**
 * 検索処理
 * @param  array $data
 * @param  boolean $isAdmin 管理者かどうか
 * @param  integer $limit
 * @return  array
 * 				次のデータが存在するかどうか
 * 				検索結果
 * @since   v 3.0.0.0
 */
	public function search($data, $isAdmin, $limit = UPLOAD_SEARCH_DEFAULT_LIMIT) {
		$conditions = array('UploadSearch.is_delete_from_library' => _ON);
		$order = array();

		// 所有者
		if (!$isAdmin || $data['UploadSearch']['user_type'] == UPLOAD_SEARCH_CONDITION_USER_MYSELF) {
			$user = Configure::read(NC_SYSTEM_KEY.'.user');
			$conditions['UploadSearch.user_id'] = $user['id'];
		} elseif ($data['UploadSearch']['user_type'] == UPLOAD_SEARCH_CONDITION_USER_WITHDRAW) {
			$conditions['UploadSearch.user_id'] = 0;
		}

		// モジュール
		if ($isAdmin && $data['UploadSearch']['user_type'] == UPLOAD_SEARCH_CONDITION_USER_ALL) {
			$data['UploadSearch']['plugin'] = $data['UploadSearch']['plugin-all'];
		} elseif ($isAdmin && $data['UploadSearch']['user_type'] == UPLOAD_SEARCH_CONDITION_USER_WITHDRAW) {
			$data['UploadSearch']['plugin'] = $data['UploadSearch']['plugin-withdraw'];
		}
		if (!empty($data['UploadSearch']['plugin'])) {
			$conditions['UploadSearch.plugin'] = $data['UploadSearch']['plugin'];
		}

		// 日付指定
		if ($isAdmin && $data['UploadSearch']['user_type'] == UPLOAD_SEARCH_CONDITION_USER_ALL) {
			$data['UploadSearch']['created'] = $data['UploadSearch']['created-all'];
		} elseif ($isAdmin && $data['UploadSearch']['user_type'] == UPLOAD_SEARCH_CONDITION_USER_WITHDRAW) {
			$data['UploadSearch']['created'] = $data['UploadSearch']['created-withdraw'];
		}
		if (!empty($data['UploadSearch']['created'])) {
			$createdArr = explode('-', $data['UploadSearch']['created']);
			$conditions['UploadSearch.year'] = $createdArr[0];
			if(!empty($createdArr[1])) {
				$conditions['UploadSearch.month'] = $createdArr[1];
			}
		}

		// カテゴリー選択
		$fileTypeArr = explode('-', $data['UploadSearch']['file_type']);
		$fileType = $fileTypeArr[0];

		// 未使用
		if (isset($fileTypeArr[1]) && $fileTypeArr[1] == 'unused') {
			$conditions['UploadSearch.is_use'] = _OFF;
		}
		if ($fileType == 'other') {
			$conditions['NOT'] = array(
				array("UploadSearch.mimetype LIKE" => "image/%"),
				array("UploadSearch.mimetype LIKE" => "audio/%"),
				array("UploadSearch.mimetype LIKE" => "video/%")
			);
		} elseif (!empty($fileType) && $fileType != 'file') {
			$conditions['UploadSearch.mimetype LIKE'] = $fileType.'/%';
		}
		// 文字列検索
		if (!empty($data['UploadSearch']['text'])) {
			if ($isAdmin && isset($data['UploadSearch']['search_type'])
					&& $data['UploadSearch']['search_type'] == UPLOAD_SEARCH_CONDITION_FROM_CREATOR) {
				$conditions['UploadSearch.created_user_name LIKE'] = '%'.$data['UploadSearch']['text'].'%';
			} else {
				$conditions['OR'] = array(
					'UploadSearch.file_name LIKE' => '%'.$data['UploadSearch']['text'].'%',
					'UploadSearch.alt LIKE' => '%'.$data['UploadSearch']['text'].'%',
					'UploadSearch.description LIKE' => '%'.$data['UploadSearch']['text'].'%',
				);
			}
		}

		// 並び替え
		if (!empty($data['UploadSearch']['order'])) {
			if($data['UploadSearch']['order'] != 'created' && $data['UploadSearch']['order'] != 'file_name'
					&& $data['UploadSearch']['order'] != 'file_size') {
				$data['UploadSearch']['order'] = 'created';
			}
			if($data['UploadSearch']['order_direction'] != 'ASC' && $data['UploadSearch']['order_direction'] != 'DESC') {
				$data['UploadSearch']['order_direction'] = 'DESC';
			}
			$order[] = array($data['UploadSearch']['order'] => $data['UploadSearch']['order_direction']);
		}
		$order[] = array('UploadSearch.id ' => $data['UploadSearch']['order_direction']);

		if (!empty($data['UploadSearch']['page'])) {
			$page = intval($data['UploadSearch']['page']);
		} else {
			$page = 1;
		}

		$params = array(
			'conditions' => $conditions,
			'order' => $order,
			'limit' => $limit + 1,
			'offset' => $limit *($page-1)
		);

		$uploads = $this->find('all', $params);

		$hasMore = false;
		if (count($uploads) == $limit + 1) {
			$hasMore = true;
			array_pop($uploads);
		}

		foreach ($uploads as $key => $upload) {
			$this->convertUpload($uploads[$key]);
		}

		return array($hasMore, $uploads);
	}

/**
 * Uploadデータ画面表示用コンバート処理
 * @param   Model $upload
 * @param   string $alias;
 * @return  void
 * @since   v 3.0.0.0
 */
	public function convertUpload(&$upload, $alias = null) {
		App::uses('CakeNumber', 'Utility');
		$alias = !isset($alias) ? $this->alias : $alias;

		if(empty($upload[$alias]['id'])) {
			return;
		}

		$upload[$alias]['real_url'] = Router::url('/', true).'nc-downloads/'.$upload[$alias]['id'].'.'.$upload[$alias]['extension'];
		$upload[$alias]['file_size'] = CakeNumber::toReadableSize($upload[$alias]['file_size']);
		$upload[$alias]['created'] = $this->date($upload[$alias]['created'], __('(Y-m-d H:i)'));
		$upload[$alias]['orientation'] = 'landscape';
		$upload[$alias]['basename'] = $this->basename($upload[$alias]['file_name'], '.'.$upload[$alias]['extension']);

		if (preg_match('/^image\/(gif|p?jpe?g|(x-)?png)/', $upload[$alias]['mimetype'], $matches)) {
			$filePath = NC_UPLOADS_DIR.$upload[$alias]['plugin'].'/'.$upload[$alias]['file_path'].$upload[$alias]['id'].'.'.$upload[$alias]['extension'];
			list($width, $height) = @getimagesize($filePath);
			$upload[$alias]['width'] = $width;
			$upload[$alias]['height'] = $height;
			$upload[$alias]['file_type'] = 'image';
			$upload[$alias]['orientation'] = $height > $width ? 'portrait' : 'landscape';
			$upload[$alias]['url'] = Router::url('/', true).'nc-downloads/'.$upload[$alias]['id'].'_library'.'.'.$upload[$alias]['extension'];
		} elseif (preg_match('/^audio\//', $upload[$alias]['mimetype'])) {
			$upload[$alias]['file_type'] = 'audio';
			$upload[$alias]['url'] = Router::url('/', true).'upload/img/audio.png';
		} elseif (preg_match('/^video\//', $upload[$alias]['mimetype'])) {
			$upload[$alias]['file_type'] = 'video';
			$upload[$alias]['url'] = Router::url('/', true).'upload/img/video.png';
		} else {
			$upload[$alias]['file_type'] = 'other';
			$upload[$alias]['url'] = Router::url('/', true).'upload/img/other.png';
		}
	}

/**
 * 使用中ファイル一覧取得
 * @param  mixed integer $uploadId|array $uploadIds
 * @return  array
 * @since   v 3.0.0.0
 */
	public function findIsUseUploads($uploadIds) {
		$Module = ClassRegistry::init('Module');

		$conditions = array(
			$this->alias.'.id' => $uploadIds,
			$this->alias.'.is_use' => _ON
		);
		$params = array(
			'fields' => array('`'.$this->alias.'`.*,`UploadLink`.*,`Content`.`title`,`Page`.*'),
			'conditions' => $conditions,
			'joins' => array(
				array(
					'type' => 'LEFT',
					'table' => 'upload_links',
					'alias' => 'UploadLink',
					'conditions' => '`'.$this->alias.'`.`id`=`UploadLink`.`upload_id`'
				),
				array(
					'type' => 'LEFT',
					'table' => 'contents',
					'alias' => 'Content',
					'conditions' => '`Content`.`id`=`UploadLink`.`content_id`'
				),
				array(
					'type' => 'LEFT',
					'table' => 'pages',
					'alias' => 'Page',
					'conditions' => '`Page`.`id`=`Content`.`room_id`'
				),
			),
		);
		$files = array();
		$results = $this->find('all', $params);
		if(count($results) > 0) {
			for($i =0; $i < count($results); $i++) {
				$results[$i]['Upload'] = $results[$i]['UploadSearch'];
				unset($results[$i]['UploadSearch']);
				$results[$i]['UploadLink']['module_name'] = $Module->loadModuleName($results[$i]['UploadLink']['plugin']);
				$results[$i]['Upload']['module_name'] = $Module->loadModuleName($results[$i]['Upload']['plugin']);

				if(!empty($results[$i]['Page']['id'])) {
					$results[$i] = $this->setPageName($results[$i]);
				}
				$uploadId = $results[$i]['Upload']['id'];
				$files[$uploadId][] = $results[$i];
			}
		}
		return $files;
	}
}