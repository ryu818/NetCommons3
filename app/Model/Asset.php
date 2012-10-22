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
App::uses('File', 'Utility');

class Asset extends AppModel
{
	public $name = 'Asset';
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
		return $this->_afterFind($this->find('all', $params), $options);
	}

	protected function _afterFind($results, $options) {
		$rets = array();
		$root_path = Configure::read('App.www_root') . 'theme' . DS . 'asset' . DS;

		foreach ($results as $key => $val) {
			//$url = $val['Asset']['url'];
			//unset($val['Asset']['url']);
			$rets[$val['Asset']['url']] = $val['Asset'];
		}

		$create_flag = false;
		$hashs = '';
		$ext = 'js';
		foreach($options as $option) {
			$url = $option['url'];
			$plugin = $option['plugin'];
			$realPath = $option['realPath'];
			$ext = $option['ext'];
			if(file_exists($realPath)) {
				if(!isset($rets[$url])) {
					$create_flag = true;
					break;
				}
				$hash = $rets[$url]['hash_content'];
				$hashs .= "-" . $hash;
			}
		}
		$file = $this->_getAsetFile(md5($hashs), $ext);
		if(extension_loaded('zlib') && !empty($_SERVER['HTTP_ACCEPT_ENCODING']) && preg_match('/gzip/i', $_SERVER['HTTP_ACCEPT_ENCODING'])) {
			$postfix = '.gz';
		} else {
			$postfix = '';
		}
		if($create_flag || !file_exists($root_path . $file.$postfix) || Configure::read('debug') != _OFF) {
			$hashs = '';
			$contents = '';
			foreach($options as $option) {
				$url = $option['url'];
				$plugin = $option['plugin'];
				$realPath = $option['realPath'];
				$ext = $option['ext'];
				if(file_exists($realPath)) {
					$content = $this->_getFileLists($realPath);
					$hash = md5($content);
					if(!isset($rets[$url])) {
						$rets[$url] = array(
							'url' => $url,
							'hash_content' => null,
							'plugin' => $plugin,
						);
					}
					if($hash != $rets[$url]['hash_content']) {
						$rets[$url]['hash_content'] = $hash;
						$this->create();
						$this->save($rets[$url]);
						$create_flag = true;
					}
					$hashs .= "-" . $hash;
					if($content != '') {
						$contents .= "\n" . $content;
					}
				}
			}
			if($contents != '') {
				$this->_deleteAsetFile($file);
				$file = $this->_createAsetFile(md5($hashs), $contents, $ext);
			} else {
				$file = '';
				$postfix = '';
			}
			//if($file) {
			//	$file_path = "{$this->request->webroot}asset/" . DS . $file;
			//}
		}
		return $file.$postfix;
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
		$path = Configure::read('App.www_root') . 'theme' . DS . 'asset' . DS;
		$file_path = $this->_getAsetFile($hash, $ext);
		if(file_exists($path . $file_path)) {
			return $file_path;
		}
		$file = new File($path . $file_path, true);
		if ($file->write($content)) {
			$gzcontent = gzencode($content);
			$gzfile = new File($path . $file_path.'.gz', true);
			$gzfile->write($gzcontent);
			return $file_path;
		}

		return null;
	}

	protected function _deleteAsetFile($file_name) {
		$path = Configure::read('App.www_root') . 'theme' . DS . 'asset' . DS;
		$file = new File($path . $file_name);
		if ($file->delete()) {
			$gzfile = new File($path . $file_name.'.gz');
			$gzfile->delete();
		}
	}

	protected function _getAsetFile($hash, $ext = 'js') {
		$file = "application-" . $hash . '.' . $ext;
		return $file;
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