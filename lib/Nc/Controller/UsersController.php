<?php
/**
 * UsersControllerクラス
 *
 * <pre>
 * ログイン、ログアウト用コントローラ
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */

App::uses('AppController', 'Controller');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class UsersController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Users';

/**
 * Model name
 *
 * @var array
 */
	public $uses = array();

/**
 * Component name
 *
 * @var array
 */
	public $components = array('Security', 'Cookie', 'CheckAuth' => array('chkBlockId' => false));

/**
 * Config ログイン関連
 *
 * @var array
 */
	public $configs = null;

/**
 * コントローラの実行前処理
 * <pre>
 * ログイン処理の設定
 * </pre>
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeFilter() {
		parent::beforeFilter();
		/*
		//ログイン処理を行うactionを指定
		$this->Auth->loginAction = array('controller' => 'users', 'action' => 'login');

		$this->Auth->logoutRedirect = '/';

		//ユーザーIDとパスワードのフィールドを指定
		$this->Auth->authenticate = array('NcForm' =>
			array(
				'fields' => array('username' => 'login_id'),
				'scope' => array('User.is_active' => NC_USER_IS_ACTIVE_ON),
				'findFields' => array(
									'id', 'login_id', 'handle', 'username', 'authority_id',
									'permalink', 'myportal_page_id', 'private_page_id', 'avatar',
									'lang', 'timezone_offset', 'email', 'mobile_email',
									'Authority.hierarchy', 'Authority.myportal_use_flag', 'Authority.private_use_flag',
									'Authority.public_createroom_flag', 'Authority.group_createroom_flag', 'Authority.myportal_createroom_flag',
									'Authority.private_createroom_flag', 'Authority.allow_htmltag_flag', 'Authority.allow_layout_flag',
									'Authority.allow_attachment', 'Authority.allow_video', 'Authority.change_leftcolumn_flag',
									'Authority.change_rightcolumn_flag', 'Authority.change_headercolumn_flag', 'Authority.change_footercolumn_flag',
								),
			)
		);
		$this->Auth->allow('login');

		//権限が無いactionを実行した際のエラーメッセージ
        $this->Auth->authError = __('Forbidden permission to access the page.');
        //ログイン後にリダイレクトするURL
        //$this->Auth->loginRedirect = '/users/index';
         */

		// ログインは、ページ表示時にログイン画面を非表示にして取得されたままになっており、
		// 時間がたつと、セキュリティエラーになってしまうため、チェックしないように修正。
		// ログイン、ログアウト処理なので、Tokenチェックもする意味もあまりないと判断。
		$this->Security->csrfCheck = false;
		//$this->Security->unlockedFields = array('UploadSearch.file_type', 'UploadSearch.page');
	}

/**
 * ログイン処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function login() {
		$configs = Configure::read(NC_CONFIG_KEY);
		$sessionIni = Configure::read('Session.ini');
		$this->Cookie->name = $configs['autologin_cookie_name'];
		$this->Cookie->path = $sessionIni['session.cookie_path'];

		if(!empty($this->request->data)){
			if(!$this->Auth->login()) {
				$this->Session->setFlash(__('Incorrect sign in.Again, please enter.'), 'default', array(), 'auth');
			}

			// ログイン後もSSLを有効にする設定の場合にログイン後かどうかのフラグをCookieに保持する
			if ($configs['use_ssl'] == NC_USE_SSL_ALWAYS
				|| $configs['use_ssl'] == NC_USE_SSL_AFTER_LOGIN) {
				$this->Cookie->write('logged_in', _ON, false);
			}

			$this->Session->setFlash(__('Sign in.'));
			$user = $this->Auth->user();//認証済みユーザーを取得

			//フォームからのデータの場合
			if($configs['autologin_use'] == NC_AUTOLOGIN_ON) {
				if (isset($user) && empty($this->request->data['User']['login_save_my_info'])) {
					//パスポート不要なので削除
					$this->Common->passportDelete($user);
				} else if(isset($user)) {
					//パスポート発行する
					$this->Common->passportWrite($user);
				}
			} else if($configs['autologin_use'] == NC_AUTOLOGIN_LOGIN) {
				$cookie = array('login_id'=>$this->request->data['User']['login_id']);
				// ログインにSSLを使う場合はsecureとする
				if ($configs['use_ssl'] != NC_USE_SSL_NO_USE) {
					$this->Cookie->secure = true;
				}
				$this->Cookie->write('User', $cookie, true,"+ ".$configs['autologin_expires']);
			}
			unset($this->request->data['User']['login_save_my_info']);
			if ($user) {
				//新しいセッションＩＤの発行と、古いセッションの破棄
				//$this->Session->renew();

				// 最終ログイン日時更新
				$bufUser['User'] = $user;
				$this->User->updateLastLogin($bufUser);

				//ログインに成功したためリダイレクト
				$startPage = $this->Common->redirectStartpage($configs);
				$this->Auth->loginRedirect = $startPage;
				return $this->redirect($this->Auth->redirect($startPage));
			}
		} else {
			$user = $this->Auth->user();//認証済みユーザーを取得
		}

		if (isset($user)) {
			//認証できたユーザー。
			$startPage = $this->Common->redirectStartpage($configs);
			$this->Auth->loginRedirect = $startPage;
			$this->flash(__('You are already signed in.'), $this->Auth->redirect($startPage));
		} else if($configs['autologin_use'] == NC_AUTOLOGIN_LOGIN) {
			$cookiePassport=$this->Cookie->read('User');
			if(isset($cookiePassport['login_id'])){
				$this->request->data['User']['login_id'] = $cookiePassport['login_id'];
			}
		}
		$this->set('autologin_use', $configs['autologin_use']);
		$this->set('login_autocomplete', $configs['login_autocomplete']);
	}

/**
 * ログアウト処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	function logout() {
		$configs = Configure::read(NC_CONFIG_KEY);
		//if($configs['autologin_use'] == NC_AUTOLOGIN_OFF) {
			$this->Common->passportDelete($configs);
		//}
		$sessionIni = Configure::read('Session.ini');
		$this->Cookie->name = $configs['autologin_cookie_name'];
		$this->Cookie->path = $sessionIni['session.cookie_path'];
		$this->Cookie->write('logged_in', _OFF, false);
		$this->Session->renew();
		$this->Session->setFlash(__('You are now signed out.'));
		$this->redirect($this->Auth->logout());	//ログアウトし、トップ画面へリダイレクト
	}
}
