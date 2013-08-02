<?php
/**
 * Asset用モデル
 * JavaScriptやCSSなど、多用なHTMLに付随するファイルを引っくるめたテーブル
 *
 * <pre>
 *  JS,CSSファイルがなければ作る（テーブルからファイル名をもとめ、なければ作成。）。
 *  hash_contentを結合し、再度、hashを求めたものをファイル名とする
 *  ファイル名：フォルダ内のファイルリスト＋ファイルの中身を列挙したhash(md5)をとる。
 *		ファイルの中身が変化すれば、同時にファイル名が変化することが保証され、ブラウザのキャッシュを活かすために重要
 *		application_(hash値).js(application_(hash値).js.gz)の２つを作成
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */

class Asset extends AppModel
{
	public $actsAs = array('TimeZone', 'File');
/**
 * URLからJs、Cssファイルのデータを読み込み
 *
 * @param  array   $options
 * 						url
 * 						plugin
 * 						realpath
 * @return array    array($last_mod, $ret)
 */
	public function findByUrls($options) {
		$urls = array();
		foreach($options as $option) {
			$urls[] = $option['url'];
		}

		$params = array(
			'conditions' => array(
				'Asset.url' => $urls,
			)
		);
		$rets = $this->_afterFind($this->find('all', $params), $options);
		return $rets;
	}
/**
 * JavaScript,CSSガーベージコレクション
 * <pre>
 * 最終アクセス時刻がNC_ASSET_GC_LIFETIMEより古いものを削除。
 * （環境により、最終アクセス時刻がとれない場合、最終更新時刻より削除）
 * </pre>
 *
 * @param  string  $path
 * @param  boolean $delete_asset_table Assetテーブルの更新日付がNC_ASSET_GC_LIFETIMEより古いものを削除するかどうか。
 * @param  boolean $is_all 保存期間関係なくすべて削除する場合、true
 * @return void
 */
	public function gc($path = null, $delete_asset_table = true, $is_all = false) {
		if(!isset($path)) {
			$path = $this->_getPath();
		}
		$rmTime = time() - NC_ASSET_GC_LIFETIME;
		$files = $this->getCurrentFile($path);

		foreach($files as $file) {
			$atime = @fileatime($path . $file);
			if (($is_all == true || ($atime !== false && $atime < $rmTime)) ||
					(($atime === false && @filemtime($path . $file) < $rmTime))) {
				if(preg_match('/^'.NC_ASSET_PREFIX.'/', $file) && substr($file, 0, 1) != '.') {
					@unlink($path . $file);
				}
			}
		}

		if($delete_asset_table) {
			if($is_all) {
				$this->getDataSource()->truncate($this->table);
			} else {
				// TODO:未テスト
				$rmTime = strtotime($this->nowDate("Y-m-d H:i:s")) - NC_ASSET_GC_LIFETIME;
				$conditions = array(
					"Asset.modified <" => date("Y-m-d H:i:s", $rmTime)
				);
				$this->deleteAll($conditions);
			}
		}
	}

	// plugin名称毎にマージして取得
	// 同じscript,cssをAjaxにより何度も読み込ませないようにするため
	protected function _afterFind($results, $options) {
		$outpus_arr = array();
		$rets_arr = array();
		$hashs_arr = array();
		$ext_arr = array();
		$create_arr = array();
		$options_arr = array();

		$doGzipCompression = false;
		if (NC_ASSET_GZIP) {
			$doGzipCompression = Configure::read(NC_CONFIG_KEY.'.'.'script_compress_gzip');
		}
		if($doGzipCompression && extension_loaded('zlib') && !empty($_SERVER['HTTP_ACCEPT_ENCODING']) && preg_match('/gzip/i', $_SERVER['HTTP_ACCEPT_ENCODING'])) {
			$postfix = '.gz';
		} else {
			$postfix = '';
		}
		$root_path = $this->_getPath();

		foreach ($results as $key => $val) {
			$rets_arr[$val['Asset']['plugin']][$val['Asset']['url']] = $val['Asset'];
		}

		foreach($options as $option) {
			$url = $option['url'];
			$plugin = $option['plugin'];
			$realPath = $option['realPath'];
			if(!isset($hashs_arr[$plugin])) {
				$hashs_arr[$plugin] = '';
				$create_arr[$plugin] = false;
				$ext_arr[$plugin] = $option['ext'];
			}
			if(file_exists($realPath) ) {
				if(!isset($rets_arr[$plugin][$url])) {
					// 指定されたURLのファイルがあるけどDBに登録されていない。
					$create_arr[$plugin] = true;
				} else {
					$hashs_arr[$plugin] .= "-" . $rets_arr[$plugin][$url]['hash_content'];
				}
			}
			$options_arr[$plugin][] = $option;

		}

		foreach($hashs_arr as $hash_plugin => $hashs) {
			$file = $this->_getAsetFile(md5($hashs), $ext_arr[$hash_plugin]);
			if($create_arr[$hash_plugin] || !file_exists($root_path . $file.$postfix) || Configure::read('debug') != _OFF) {
				$hashs = '';
				$contents = '';
				$ext = $ext_arr[$hash_plugin];
				foreach($options_arr[$hash_plugin] as $option) {
					$url = $option['url'];
					$plugin = $option['plugin'];
					$realPath = $option['realPath'];
					if(file_exists($realPath)) {
						$content = $this->_getFileLists($realPath);
						$hash = md5($content);
						if(!isset($rets_arr[$plugin][$url])) {
							$rets_arr[$plugin][$url] = array(
								'url' => $url,
								'hash_content' => null,
								'plugin' => $plugin,
							);
						}
						if($hash != $rets_arr[$plugin][$url]['hash_content']) {
							$rets_arr[$plugin][$url]['hash_content'] = $hash;
							$this->create();
							$this->save($rets_arr[$plugin][$url]);
						}
						$hashs .= "-" . $hash;
						if($content != '') {
							$contents .= "\n" . $content;
						}
					}
				}
				if($contents != '') {
					$file = $this->_createAsetFile(md5($hashs), $contents, $ext);
					$outpus_arr[$hash_plugin] = $file.$postfix;
				}
			} else {
				$outpus_arr[$hash_plugin] = $file.$postfix;
			}
		}
		return $outpus_arr;
	}

/**
 * File List取得処理
 *
 */
	protected function _getFileLists($realPath) {
		$contents = '';
		if(is_dir( $realPath )) {
			$dirArray = glob( $realPath . DS . "*" );
			if(is_array($dirArray) && count($dirArray) > 0) {
				$buf_contents = '';
				foreach( $dirArray as $child_path){
					if(is_dir( $child_path )) {
						$buf_contents .= $this->_getFileLists($child_path);
					} else {
						$contents .= $this->_getFileLists($child_path);
					}
				}
				$contents .= $buf_contents;
			}
		} else if(preg_match("/(\.js|\.css)$/i", $realPath)) {
			$content = $this->getData($realPath);
			$contents .= "\n".$content;
		}
		return $contents;
	}

	protected function _createAsetFile($hash, $content, $ext = 'js') {
		$path = $this->_getPath();
		$file_name = $this->_getAsetFile($hash, $ext);
		if(file_exists($path . $file_name)) {
			return $file_name;
		}
		$file = $this->createFile($path, $file_name, $content, true);
		return $file;
	}

	protected function _deleteAsetFile($file_name) {
		$path = $this->_getPath();
		$this->deleteFile($path . $file_name);
	}

	protected function _getAsetFile($hash, $ext = 'js') {
		$file = NC_ASSET_PREFIX . $hash . '.' . $ext;
		return $file;
	}

	protected function _getPath() {
		$path = Configure::read('App.www_root') . 'theme' . DS . 'assets' . DS;
		return $path;
	}

/**
 * コメント除去
 *
 * @param  object  $model
 * @param  string  $file_path
 * @return string
 */
	public function getData($file_path) {
		$contents = '';
		$handle = fopen($file_path, "r");
		if(!$handle) return "";
		$file_size = filesize($file_path);
		if($file_size > 0 )
			$contents = fread($handle, $file_size);
		fclose($handle);
		// コメント除去
		$pattern = array("/^\s+/s", "/\n\s+/s", "/^(\/\/).*?(?=\n)/s", "/\n\/\/.*?(?=\n)/s", "/\s+(\/\/).*?(?=\n)/s", "/^\/\*(.*?)\*\//s", "/\n\/\*(.*)\*\//Us","/\s+(?=\n)/s");
		//$pattern = array("/^\s+/us", "/\n\s+/us", "/^(\/\/).*?(?=\n)/us", "/\n\/\/.*?(?=\n)/us", "/\s+(\/\/).*?(?=\n)/us", "/^\/\*((.|\n)*?)\*\//us", "/\n\/\*(.*)\*\//Uus","/\s+(?=\n)/us");
		$replacement = array ("","\n","","\n","","","","");
		return preg_replace($pattern, $replacement, $contents);
	}
}