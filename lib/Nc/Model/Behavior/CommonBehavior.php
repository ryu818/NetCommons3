<?php
/**
 * File Behavior
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model.Behavior
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */

class CommonBehavior extends ModelBehavior {

/**
 * 特定テーブルの特定列のデクリメント処理
 *
 * @param  model        $Model
 * @param  string|array $scope テーブルの更新条件
 *                             id列のみが更新条件の場合は更新対象のid値を指定し、
 *                             それ以外の条件の場合（複合主キー等）にはarrayで指定する
 * @param  string       $targetColname デクリメント対象の列名称
 * @param  string       $decCnt デクリメントする数　もし空の場合は1
 * @return boolean true or false
 * @since   v 3.0.0.0
 */
	public function decrementSeq(Model $Model, $scope, $targetColname, $decCnt = 1){
		$decCnt = -1*$decCnt;
		return $this->_calcOperation($Model, $scope, $targetColname, $decCnt);
	}

/**
 * 特定テーブル特定列のインクリメント処理
 *
 * @param  model        $Model
 * @param  string|array $scope テーブルの更新条件
 *                             id列のみが更新条件の場合は更新対象のid値を指定し、
 *                             それ以外の条件の場合（複合主キー等）にはarrayで指定する
 * @param  string       $targetColname インクリメント対象の列名称
 * @param  string       $incCnt インクリメントする数　もし空の場合は1
 * @return boolean true or false
 * @since   v 3.0.0.0
 */
	public function incrementSeq(Model $Model, $scope, $targetColname, $incCnt = 1){
		return $this->_calcOperation($Model, $scope, $targetColname, $incCnt);
	}

/**
 * 投票処理
 *
 * @param  model        $Model
 * @param  string|array $scope テーブルの更新条件
 *                             id列のみが更新条件の場合は更新対象のid値を指定し、
 *                             それ以外の条件の場合（複合主キー等）にはarrayで指定する
 * @param  string       $voterId 投票するユーザのID　もし空の場合はnull
 * @param  string       $countTargetColname 投票件数を格納している列の名称　もし空の場合は'vote_count'
 * @param  string       $valueTargetColname 投票済みユーザのIDを格納している列の名称　もし空の場合は'vote'
 * @return boolean true or false
 * @since   v 3.0.0.0
 */
	public function voting(Model $Model, $scope, $voterId = null, $countTargetColname = 'vote_count', $valueTargetColname = 'vote'){
		return $this->_calcOperation($Model, $scope, $countTargetColname, 1, $valueTargetColname, $voterId);
	}

/**
 * 特定テーブルの特定列に引数の値を加える処理。
 * 投票済みユーザのIDの格納列名称と、今回投票するユーザのIDを両方ともに引数に指定した場合には、
 * 指定したIDを格納列の末尾に結合する（区切り文字は "," カンマ）
 *
 * @param  model        $Model
 * @param  string|array $scope テーブルの更新条件
 *                             id列のみが更新条件の場合は更新対象のid値を指定し、
 *                             それ以外の条件の場合（複合主キー等）にはarray（列=>条件）で指定する
 * @param  string       $countTargetColname テーブルの数値操作対象列の名称
 * @param  integer      $incCnt 加算する数値（マイナスを指定した場合は値が減少する）
 * @param  string       $valueTargetColname 投票済みユーザのIDを格納している列の名称　もし空の場合はnull
 * @param  string       $voterId          投票するユーザのID　もし空の場合はnull
 * @return boolean true or false
 * @since   v 3.0.0.0
 */
	protected function _calcOperation(Model $Model, $scope, $countTargetColname, $incCnt = 1, $valueTargetColname = null, $voterId = null) {

		if (is_array($scope)) {
			$conditions = $scope;
		} elseif (is_string($scope)) {
			$conditions = array($Model->alias.'.'.$Model->primaryKey => $scope);
		} else {
			return false;
		}
		$fields = array(
				$Model->alias.'.'.$countTargetColname =>
					$Model->alias.'.'.$countTargetColname.'+'.$incCnt
		);

		// 投票者のidと投票済みIDの格納列名が両方ともに指定されている場合には投票したユーザのIDを末尾に追加
		if(isset($valueTargetColname) && isset($voterId)){
			$fields[$Model->alias.'.'.$valueTargetColname] = "CONCAT_WS(',',". $Model->alias.".".$valueTargetColname .",".$voterId.")";
		}
		return $Model->updateAll($fields, $conditions);
	}
}