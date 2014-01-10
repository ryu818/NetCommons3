<?php
/**
 * PageMyPortalモデル
 *
 * <pre>
 *  Myポータルページにかかわる処理
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Takako Miyagawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */

class PageMyPortal extends AppModel {

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
	 * @param   integer|string|array $id Set this ID for this model on startup, can also be an array of options, see above.
	 * @param   string $table Name of database table to use.
	 * @param   string $ds DataSource connection name.
	 * @return  void
	 * @since   v 3.0.0.0
	 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		//ログインユーザのセット
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
		if(! is_numeric($userId)) {
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
		$data['parent_id']              = NC_TOP_MYPORTAL_ID;
		$data['thread_num']             = 1;
		$data['display_sequence']       = 0;
		$data['position_flag']          = _ON;
		$data['lang']                   = '';
		$data['is_page_meta_node']      = _OFF;
		$data['is_page_style_node']     = _OFF;
		$data['is_page_layout_node']    = _OFF;
		$data['is_page_theme_node']     = _OFF;
		$data['is_page_column_node']    = _OFF;
		$data['space_type']             = NC_SPACE_TYPE_MYPORTAL;
		$data['show_count']             = 0;
		$data['display_flag']           = NC_DISPLAY_FLAG_ON;
		$data['display_apply_subpage']  = _ON;
		$data['is_approved']            = NC_APPROVED_FLAG_ON;
		$data['lock_authority_id']      = NC_AUTH_OTHER_ID;
		$data['page_name']              = "Myportal";

		return $data;
	}

	/**
	 * マイポータルの一覧を取得する。
	 *
	 * @param   string $userId
	 * @return  array
	 * @since   v 3.0.0.0
	 */
	public function getRoomList($userId="") {
		//roomリストを取得する。
		if(! $userId) {
			if(! $this->userId) return array();
			$userId = $this->userId;
		}

		if(! $userId || ! is_numeric($userId)) {
			//パラメータエラー
			return array();
		}
		$join = array();
		$join["type"] = "INNER";
		$join['table'] = 'page_user_links';
		$join['alias'] = 'PageUserLink';
		$join['conditions']= array(
			$this->alias . ".id = PageUserLink.room_id" ,
			"PageUserLink.user_id "=>$userId
		);

		$ck = $this->find(
			'all' ,
			array(
				'conditions'=>array(
					$this->alias.'.space_type'=>NC_SPACE_TYPE_MYPORTAL,
					$this->alias.'.room_id = '.$this->alias.'.id',
				),
				'joins'=>array($join)
			)
		);
		return $this->arrayKeyChangeId($ck);
	}

	/**
	 * Userのためのマイポータルルームを作成する。
	 *
	 * @param $userId
	 * @return false or int
	 * @since   v 3.0.0.0
	 */
	public function addTopRoom($userId) {
		//userIdを確認＆格納
		if(! $this->setUserId($userId)) {
			return false;
		}
		//作成する権限があるか確認する。 TODO:privateで切り出す。
		if(! isset($this->userData['Authority'])
			|| !  isset($this->userData['Authority']['myportal_use_flag'])
			|| !  $this->userData['Authority']['myportal_use_flag']
		) {
			//作成権限がない
			return false;
		}
		//権限OKの状態
		//insert用データの調整
		$insPage[$this->alias]                  = $this->getDefault();
		$insPage[$this->alias]['display_flag']  = NC_DISPLAY_FLAG_ON;
		$insPage[$this->alias]['permalink']     = $this->userData['User']['permalink'];

		$nodePage = $insPage;
		//roomの作成
		$this->create();
		$this->set($nodePage);
		if(! $this->save($nodePage)) {
			//save失敗
			return false;
		}
		//insert idがとれている。
		if(! $this->id) {
			return false;
		}
		$newRoomId = $this->id;

		//treeの登録
		if(! $this->PageTree->addPage($newRoomId , NC_SPACE_TYPE_MYPORTAL))
		{
			return false;
		}
		//room_idに自身のidを保存更新する。
		$updNodePage = array();
		$updNodePage[$this->alias]['id']      = $newRoomId;
		$updNodePage[$this->alias]['root_id'] = $newRoomId;
		$updNodePage[$this->alias]['room_id'] = $newRoomId;
		$this->set($updNodePage);
		//room_id root_idの更新完了。
		if(! $this->save($updNodePage, false, array('root_id', 'room_id'))) {
			return false;
		}

		//topページの作成 つまりpagesには2レコード作られる。
		if(! $this->_addTopPage($newRoomId , $insPage)) {
			return false;
		}
		//PageTree保存
		if(! $this->PageTree->addPage($this->id , $newRoomId)) {
			return false;
		}
		//PageUerLink作成
		if(! $this->_addPageUserLink($newRoomId , $userId)) {
			return false;
		}
		//roomIdを返す
		return $newRoomId;
	}

	/**
	 * PageUserLinkにマイポータルとの紐付き保存。
	 * self::addTopRoom()で使われることを想定しています。
	 * @param   $roomId
	 * @param   $userId
	 * @return  bool
	 * @since   v 3.0.0.0
	 */
	private function _addPageUserLink($roomId , $userId) {
		// page_user_links Insert
		$data = array();
		$PageUserLink = ClassRegistry::init('PageUserLink');
		$data[$PageUserLink->alias]['room_id']      = $roomId;
		$data[$PageUserLink->alias]['user_id']      = $userId;
		$data[$PageUserLink->alias]['authority_id'] = NC_AUTH_CHIEF_ID;
		$PageUserLink->create();
		$PageUserLink->set($data);
		if(! $PageUserLink->save($data)) {
			return false;
		}
		return true;
	}

	/**
	 * roomの最初のページをinsertする
	 * self::addTopRoom()で使われることを想定しています。
	 * @param   $roomId
	 * @param   $insPage
	 * @since   v 3.0.0.0
	 */
	private function _addTopPage($roomId , $insPage) {

		//topページの作成つまり2レコード作られる。
		$insPage[$this->alias]['thread_num']        = 2;
		$insPage[$this->alias]['display_sequence']  = 1;
		$insPage[$this->alias]['root_id']           = $roomId;
		$insPage[$this->alias]['room_id']           = $roomId;
		$insPage[$this->alias]['parent_id']         = $roomId;
		$insPage[$this->alias]['page_name']         = 'Myportal Top';
		$insPage[$this->alias]['page_style_id']     = 0;
		$insPage[$this->alias]['lang']              = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$this->create();
		$this->set($insPage);
		$ret = $this->save($insPage, true, null);
		if(! $ret) {
			return false;
		}
		return true;
	}


}