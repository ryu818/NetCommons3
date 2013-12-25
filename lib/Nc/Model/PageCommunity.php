<?php
/**
 * PageCommunityモデル
 *
 * <pre>
 *  ページのコミュニティにかかわる処理
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Takako Miyagawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */

class PageCommunity extends AppModel
{
	//table
	public $name = 'Page';

	//behavior
	public $actsAs = array('Page');

	//バリデーション設定
	public $validate = array();

	//ユーザの情報
	private $userId         = null;
	private $userData       = array();
	//ログインしているユーザの情報
	private $loginUserId    = null;
	private $loginUserData  = array();

	//Class Object
	private $User       = null;
	private $PageTree   = null;

	/**
	 * construct
	 * @param integer|string|array $id Set this ID for this model on startup, can also be an array of options, see above.
	 * @param string $table Name of database table to use.
	 * @param string $ds DataSource connection name.
	 * @return  void
	 * @since   v 3.0.0.0
	 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->_setLoginUser();
		$this->PageTree = ClassRegistry::init('PageTree');
	}

	/**
	 * ログインユーザのセット
	 * @since   v 3.0.0.0
	 */
	private function _setLoginUser() {
		//ログイン情報のセット
		$loginUser= Configure::read(NC_SYSTEM_KEY.'.user');
		if(isset($loginUser['id']) && $loginUser['id']) {
			$userId = $loginUser['id'];
			$this->setUserId($userId);
			$this->loginUserId = $this->userId;
			$this->loginUserData = $this->userData;
		}
	}

	/**
	 * userIdをセットする。
	 * 存在しないユーザだった場合はnullが格納される。
	 * @param int $userId
	 * @return bool
	 */
	public function setUserId($userId) {
		// TODO : 分離するModel/Pageで共通の処理にしたい。
		// パラメータエラー
		if(! is_numeric($userId))
		{
			$this->userId = null;
			$this->userData = array();
			return false;
		}
		//Class Object
		if(! $this->User) {
			$this->User = ClassRegistry::init('User');
		}
		//レコードを取得。取得できなければnull格納
		$this->userData = $this->User->findById($userId);
		if(! $this->userData) {
			$this->userId = null;
			return false;
		}
		$this->userId = $userId;
		return true;
	}

	/**
	 * 初期値設定
	 * 新規でページを作る際の初期データの作成を助ける。
	 *
	 * @param   integer $spaceType
	 * @return  Model Page
	 * @since   v 3.0.0.0
	 */
	public function getDefault() {
		$data = array();
		$data['root_id']                = 0;
		$data['parent_id']              = NC_TOP_GROUP_ID;
		$data['thread_num']             = 1;
		$data['display_sequence']       = 0;
		$data['position_flag']          = _ON;
		$data['lang']                   = '';
		$data['is_page_meta_node']      = _OFF;
		$data['is_page_style_node']     = _OFF;
		$data['is_page_layout_node']    = _OFF;
		$data['is_page_theme_node']     = _OFF;
		$data['is_page_column_node']    = _OFF;
		$data['space_type']             = NC_SPACE_TYPE_GROUP;
		$data['show_count']             = 0;
		$data['display_flag']           = NC_DISPLAY_FLAG_ON;
		$data['display_apply_subpage']  = _ON;
		$data['is_approved']            = NC_APPROVED_FLAG_ON;
		$data['lock_authority_id']      = NC_AUTH_OTHER_ID;
		$data['page_name']              = "Community";

		return $data;
	}

	/**
	 *ページの追加
	 * @param $parent_id
	 * @return int or null
	 */
	public function addPage($parent_id)
	{
		//parent_idの情報取得
		$parentData = $this->findById($parent_id);
		if(! $parentData)
		{
			//親ページが取得できない。
			return null;
		}

		//ページの作成権限の確認
		if(! $this->checkAuthAddPage()) {
			return null;
		}

		//ページの追加権限の確認
		$ins = $this->getDefault();
		$ins['parent_id']   = $parent_id;
		$ins['room_id']     = $parentData[$this->alias]['room_id'];
		$ins['thread_num']  = $parentData['thread_num'] + 1;
		$ins['root_id']     = $parentData['root_id'];

		$this->create();
		if(! $id = $this->save($ins)) {
			return null;
		}

		//PageTreeへ保存
		$PageTree = ClassRegistry::init('PageTree');
		if(! $PageTree->addPage($id , $parent_id)) {
			return null;
		}

		return $id;
	}


	/**
	 * ページの作成権限の確認
	 * TODO このメソッドを呼び出す先のModelでチェックされるなら、この処理は省略したい。
	 * //Community->addPage($parentId); として、Model/Communityの中で権限をチェックするのでもよいな。
	 * @return bool
	 */
	public function checkAuthAddPage()
	{
		//TODO:実装
		return true;
	}

}