<?php
/**
 * MailComponentクラス
 *
 * <pre>
 * NetCommons用メール送信コンポーネント
 * メールsubject,body変換処理、承認記事通知、承認完了通知、記事投稿通知に対応
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controllers.Components
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class MailComponent extends Component {
/**
 * Controller
 *
 * @var     object
 */
	protected $_controller = null;

/**
 * contentId
 *
 * @var     integer
 */
	public $contentId = null;

/**
 * $pageId
 *
 * @var     integer
 */
	public $pageId = null;

/**
 * userId
 *
 * @var     integer
 */
	public $userId = null;

/**
 * conditions
 *
 * @var     array
 */
	public $conditions = array();

/**
 * どの権限以上に送信するか
 *
 * @var     integer
 */
	public $moreThanHierarchy = null;

/**
 * The subject of the email
 *
 * @var string
 */
	public $subject = null;

/**
 * The body of the email
 *
 * @var string
 */
	public $body = null;

/**
 * The send result text body of the email
 *
 * @var string
 */
	public $sendTextBody = null;

/**
 * The send result html body of the email
 *
 * @var string
 */
	public $sendHtmlBody = null;

/**
 * Mailリスト
 *
 * @var array
 */
	public $mails = null;

/**
 * subject,body変換文字列の配列
 *
 * @var array
 */
	public $assignedTags = array();

/**
 * $emailFormatがhtmlの場合のhtmlspecialcharsしなくてよい変換文字列の配列
 *
 * @var array
 */
	public $unEscapeTags = array();

/**
 * Constructor
 *
 * @param ComponentCollection $collection A ComponentCollection this component can use to lazy load its components
 * @param array $settings Array of configuration settings.
 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		$this->_controller = $collection->getController();
		parent::__construct($collection, $settings);
		$this->reset();
	}

/**
 * メール送信処理
 *
 * @param string $emailFormat html or text or
 * 		auto($contentIdまたは、$roomId、$userIdの検索結果から送信する場合は、Config.htmlmail,Config.mobile_htmlmailの値から送信。$mails指定ならば、Config.htmlmail)
 * @param boolean $isMobile default false
 * @return void
 * @throws SocketException
 */
	public function send($emailFormat = 'auto', $isMobile = false) {
		App::uses('CakeEmail', 'Network/Email');
		$Email = new CakeEmail('default');
		$htmlmail = Configure::read(NC_CONFIG_KEY.'.'.'htmlmail');
		$mobileHtmlmail = Configure::read(NC_CONFIG_KEY.'.'.'mobile_htmlmail');
		if($emailFormat == 'auto') {
			if($isMobile) {
				$emailFormat = ($mobileHtmlmail == _ON) ? 'html' : 'text';
			} else {
				$emailFormat = ($htmlmail == _ON) ? 'html' : 'text';
			}
		}

		$subject = $this->replaceTags($this->subject, 'text');

		$this->sendTextBody = $this->replaceTags($this->body, 'text');
		$this->sendHtmlBody = $this->replaceTags($this->body, 'html');

		if(isset($this->mails)) {
			$mails = $this->mails;
		} else {
			$conditions = array();
			if(isset($this->userId)) {
				$conditions = array('User.id' => $this->userId);
			} else if(count($this->conditions) > 0) {
				$conditions = $this->conditions;
			}
			if(isset($this->contentId)) {
				$emailRes = $this->_controller->User->getSendMails($this->contentId, $this->moreThanHierarchy, $conditions);
			} else if(isset($this->pageId)) {
				$emailRes = $this->_controller->User->getSendMailsByPageId($this->pageId, $this->moreThanHierarchy, $conditions);
			} else {
				return;
			}
			if(count($emailRes['mobileEmail']) > 0) {
				$mobileEmailFormat = $emailFormat;
				if($emailFormat == 'auto') {
					$mobileEmailFormat = ($mobileHtmlmail == _ON) ? 'html' : 'text';
				}
				try{
					$body = ($mobileEmailFormat == 'html') ? $this->sendHtmlBody : $this->sendTextBody;
					$Email->to($emailRes['mobileEmail'])
						->emailFormat($mobileEmailFormat)
						->subject($subject)
						->send($body);
				} catch(Exception $e){
					return false;
				}
			}
			if(count($emailRes['email']) > 0) {
				$mails = $emailRes['email'];
			} else {
				return;
			}
		}
		try{
			$body = ($emailFormat == 'html') ? $this->sendHtmlBody : $this->sendTextBody;
			$Email->to($mails)
				->emailFormat($emailFormat)
				->subject($subject)
				->send($body);
			return true;
		}catch(Exception $e){
			return false;
		}
	}

/**
 * 投稿記事メール送信チェック
 *
 * @param boolean $isEdit 編集記事かいなか
 * @param boolean $status 編集後のstatus NC_STATUS_PUBLISH or NC_STATUS_TEMPORARY or NC_STATUS_TEMPORARY_BEFORE_RELEASED
 * @param boolean $mailFlag 記事メール送信フラグ
 * @param boolean $isApproved 編集後の承認フラグ
 * @param boolean $beforeIsApproved 編集前の承認フラグ
 *
 * @return array("Post" or "Approved" or "Unapproved")
 * @throws SocketException
 */
	public function checkPost($isEdit, $mailFlag = false, $status = NC_STATUS_PUBLISH, $beforeStatus = NC_STATUS_PUBLISH, $isApproved = null, $beforeIsApproved = null) {
		$ret = array();
		if(isset($isApproved) && $isApproved == _ON && $beforeIsApproved == _OFF) {
			// 承認完了通知
			$ret['Approved'] = true;
		} else if(isset($isApproved) && $isApproved == _OFF && $status == NC_STATUS_PUBLISH) {
			// 未承認記事　主担通知
			$ret['Unapproved'] = true;
		}
		if(!isset($ret['Unapproved']) && $mailFlag && $beforeStatus == NC_STATUS_TEMPORARY_BEFORE_RELEASED && $status == NC_STATUS_PUBLISH) {
			// メール通知
			$ret['Post'] = true;
		}
		return $ret;
	}

/**
 * データリセット
 *
 * @param void
 * @return void
 */
	public function reset() {
		$this->contentId = null;
		$this->pageId = null;
		$this->userId = null;
		$this->conditions = array();
		$this->moreThanHierarchy = null;
		$this->subject = null;
		$this->body = null;
		$this->mails = null;
		$this->sendTextBody = null;
		$this->sendHtmlBody = null;
		$this->assignedTags = array(
			'{X-SITE_NAME}' => null,
			'{X-ROOM}' => null,
			'{X-PAGE}' => null,
			'{X-CONTENT_NAME}' => null,
			'{X-SUBJECT}' => null,
			'{X-BODY}' => null,
			'{X-USER}' => null,
			'{X-USER_NAME}' => null,
			'{X-TO_DATE}' => null,
			'{X-URL}' => null,
		);
		$this->unEscapeTags = array(
			'{X-BODY}',
		);
	}

/**
 * subject,body変換文字列をReplace
 *
 * @param  string $str
 * @param  string $emailFormat html or text
 * @param  boolean $assignTags
 * @return string
 */
	public function replaceTags($str, $emailFormat = 'html') {
		$str = h($str);
		foreach($this->assignedTags as $key => $assignedTag) {
			if(!preg_match('/'.preg_quote($key).'/', $str)) {
				continue;
			}
			if(!isset($assignedTag)) {
				$name = $this->_getAssignMethod($key);
				if(isset($name)) {
					$assignedTag = $this->assignedTags[$key] = $this->{$name}();
				}
			}

			if($emailFormat == 'html' && !in_array($key, $this->unEscapeTags)) {
				$assignedTag = h($assignedTag);
			}

			if($emailFormat == 'text' && in_array($key, $this->unEscapeTags)) {
				$assignedTag = $this->convertHtmlToText($assignedTag);
			}
			$str = str_replace($key, $assignedTag, $str);
		}

		if($emailFormat == 'html') {
			$str = str_replace("\r\n", "<br />", $str);
			$str = str_replace("\r", "<br />", $str);
			$str = str_replace("\n", "<br />", $str);
		}

		return $str;
	}

/**
 * HtmlからText変換処理
 * @param string Html文字列
 * @return string	Plain Text文字列
 **/
	public function convertHtmlToText($str) {
		$patterns = array();
		$replacements = array();
		//\nを削除
		$patterns[] = "/\\n/su";
		$replacements[] = "";

		//brを\n
		$patterns[] = "/<br(.|\s)*?>/u";
		$replacements[] = "\n";

		//divを\n
		$patterns[] = "/<\/div>/u";
		$replacements[] = "</div>\n";

		//pを\n
		$patterns[] = "/<\/p>/u";
		$replacements[] = "</p>\n";

		//blockquoteを\n
		$patterns[] = "/<\/blockquote>/u";
		$replacements[] = "</blockquote>\n";

		//liを\n
		$patterns[] = "/[ ]*<li>/u";
		$replacements[] = "    <li>";

		$patterns[] = "/<\/li>/u";
		$replacements[] = "</li>\n";

		//&npspを空白
		$patterns[] = "/\&nbsp;/u";
		$replacements[] = " ";

		//&quot;を"
		$patterns[] = "/\&quot;/u";
		$replacements[] = "\"";

		//&acute;を´
		$patterns[] = "/\&acute;/u";
		$replacements[] = "´";

		//&cedil;を¸
		$patterns[] = "/\&cedil;/u";
		$replacements[] = "¸";

		//&circ;を?
		$patterns[] = "/\&circ;/u";
		$replacements[] = "?";

		//&lsquo;を‘
		$patterns[] = "/\&lsquo;/u";
		$replacements[] = "‘";

		//&rsquo;を’
		$patterns[] = "/\&rsquo;/u";
		$replacements[] = "’";

		//&ldquo;を“
		$patterns[] = "/\&ldquo;/u";
		$replacements[] = "“";

		//&rdquo;を”
		$patterns[] = "/\&rdquo;/u";
		$replacements[] = "”";

		//&apos;を'
		$patterns[] = "/\&apos;/u";
		$replacements[] = "'";

		//&#039;を'
		$patterns[] = "/\&#039;/u";
		$replacements[] = "'";

		//&amp;を&
		$patterns[] = "/\&amp;/u";
		$replacements[] = "&";

		$str = preg_replace($patterns, $replacements, $str);
		$quote_arr = explode("<blockquote class=\"quote\">", $str);
		$quote_cnt = count($quote_arr);
		if($quote_cnt > 1) {
			$result_str = "";
			$indent_cnt = 0;
			$count = 0;
			foreach($quote_arr as $quote_str) {
				if($count == 0 || $quote_cnt == $count) {
					$result_str .= $quote_str;
					$count++;
					continue;
				}
				$indent_cnt++;
				$quote_close_arr = explode("</blockquote>", $quote_str);
				$quote_close_cnt = count($quote_close_arr);
				if($quote_close_cnt > 1) {
					$close_count = 0;
					foreach($quote_close_arr as $quote_close_str) {
						//if($close_count == 0 || $quote_close_cnt == $close_count) {
						//						if($quote_close_cnt == $close_count+1) {
						//							$result_str .= $quote_close_str;
						//							$close_count++;
						//							continue;
						//						}
						$indent_str = $this->_getIndentStr($indent_cnt);
						if($indent_str != "") {
							$quote_pattern = "/\n/u";
							$quote_replacement = "\n".$indent_str;
							$result_str = preg_replace("/(> )+$/u", "", $result_str);
							if($quote_close_cnt != $close_count+1) {
								if(!preg_match("/\n$/u", $result_str)) {
									$result_str .= "\n";
								}
								$result_str .= preg_replace("/^(> )+\n/u", "", $indent_str.preg_replace($quote_pattern, $quote_replacement, $quote_close_str));
								$indent_cnt--;
							} else {
								$result_str .= preg_replace($quote_pattern, $quote_replacement, $quote_close_str);
							}
						} else {
							$result_str .= $quote_close_str;
						}
						$close_count++;
					}

				} else {
					$indent_str = $this->_getIndentStr($indent_cnt);
					$quote_pattern = "/\n/u";
					$quote_replacement = "\n".$indent_str;
					$result_str .= $indent_str.preg_replace($quote_pattern, $quote_replacement, $quote_str);
				}
				$count++;
			}
			$str = $result_str;
		}
		$str = strip_tags($str);

		// strip_tagsで「<」、「>」があるとそれ以降の文字が消えるため、strip_tags後に変換
		$patterns = array();
		$replacements = array();

		//&lt;を<
		$patterns[] = "/\&lt;/u";
		$replacements[] = "<";

		//&gt;を>
		$patterns[] = "/\&gt;/u";
		$replacements[] = ">";
		return preg_replace($patterns, $replacements, $str);
	}

/**
 * Assignするメソッド名を取得
 *
 * @param string assignedTag {X-XXXXX}
 * @return string method名
 */
	protected function _getAssignMethod($key) {
		$name = null;
		switch($key) {
			case '{X-SITE_NAME}':
				$name = 'Site';
				break;
			case '{X-ROOM}':
				$name = 'Room';
				break;
			case '{X-PAGE}':
				$name = 'Page';
				break;
			case '{X-CONTENT_NAME}':
				$name = 'ContentName';
				break;
			case '{X-SUBJECT}':
				$name = 'Subject';
				break;
			case '{X-BODY}':
				$name = 'Body';
				break;
			case '{X-USER}':
				$name = 'User';
				break;
			case '{X-USER_NAME}':
				$name = 'UserName';
				break;
			case '{X-TO_DATE}':
				$name = 'ToDate';
				break;
			case '{X-URL}':
				$name = 'Url';
				break;
		}
		if(isset($name)) {
			$name = '_assign'.$name.'Tag';
		}
		return $name;
	}

/**
 * サイト名
 * subject,body変換文字列取得
 *
 * @param void
 * @return string
 */
	protected function _assignSiteTag() {
		return Configure::read(NC_CONFIG_KEY.'.'.'sitename');
	}

/**
 * ルーム名
 * subject,body変換文字列取得
 *
 * @param void
 * @return string
 */
	protected function _assignRoomTag() {
		if(isset($this->_controller->nc_page)) {
			$page = $this->_controller->Page->setPageName($this->_controller->nc_page);
		}

		// 権限を付与したショートカットの場合、権限元のルームではなく投稿があったルームをセット
		$roomName = '';
		if(isset($page)) {
			$roomName = isset($this->_controller->nc_page['CommunityLang']['community_name']) ? $this->_controller->nc_page['CommunityLang']['community_name'] :
				(($page['Page']['id'] == $page['Page']['room_id']) ? $page['Page']['page_name'] : null);
			if(!isset($roomName)) {
				$activePage = $this->_controller->Page->findIncludeComunityLang($this->_controller->nc_page['Page']['room_id']);
				$activePage = $this->_controller->Page->setPageName($activePage);
				if(!$activePage) {
					// errorでも空文字を返している
					return '';
				}
				$roomName = isset($activePage['CommunityLang']['community_name']) ? $activePage['CommunityLang']['community_name'] :
					$activePage['Page']['page_name'];
			}
		}
		return $roomName;
	}

/**
 * ページ名
 * subject,body変換文字列取得
 *
 * @param void
 * @return string
 */
	protected function _assignPageTag() {
		$pageName = '';
		if(isset($this->_controller->nc_page)) {
			$pageName = $this->_controller->nc_page['Page']['page_name'];
		}
		return $pageName;
	}

/**
 * コンテンツ名
 * subject,body変換文字列取得
 *
 * @param void
 * @return string
 */
	protected function _assignContentNameTag() {
		$contentName = '';
		if(isset($this->_controller->nc_block)) {
			$contentName = $this->_controller->nc_block['Content']['title'];
		}
		return $contentName;
	}

/**
 * 件名 各Controllerでセットさせるため、対処しない
 * subject,body変換文字列取得
 *
 * @param void
 * @return string
 */
	protected function _assignSubjectTag() {
		return '';
	}

/**
 * 内容 各Controllerでセットさせるため、対処しない
 * subject,body変換文字列取得
 *
 * @param void
 * @return string
 */
	protected function _assignBodyTag() {
		return '';
	}

/**
 * ハンドル名
 * subject,body変換文字列取得
 *
 * @param void
 * @return string
 */
	protected function _assignUserTag() {
		$userId = 0;
		$handle = '';
		if(isset($this->userId)) {
			$userId = $this->userId;
			$user = $this->_controller->User->findById($userId);
			if(!$user) {
				// errorでも空文字を返している
				return '';
			}
			$handle = $user['User']['handle'];
		} else {
			$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
			if(isset($loginUser['handle'])) {
				$userId = $loginUser['id'];
				$handle = $loginUser['handle'];
			}
		}
		return $handle;
	}

/**
 * 会員名
 * subject,body変換文字列取得
 *
 * @param void
 * @return string
 */
	protected function _assignUserNameTag() {
		$userId = 0;
		if(isset($this->userId)) {
			$userId = $this->userId;
			$user = $this->_controller->User->findById($userId);
			if(!$user) {
				// errorでも空文字を返している
				return '';
			}
		} else {
			$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
			if(isset($loginUser['handle'])) {
				$userId = $loginUser['id'];
			}
		}

		$username = '';
		if($userId > 0) {
			// TODO:会員名称が、各自で公開可否を選択でき、非公開にしている場合、取得させないほうがよい。
			$UserItemLink = ClassRegistry::init('UserItemLink');
			$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
			$userItemLink = $UserItemLink->find('first', array(
				'fields' => array('content'),
				'conditions' => array(
					'user_id' => $userId,
					'user_item_id' => NC_ITEM_ID_USERNAME,
					'lang' => array('', $lang),
				),
				'order' => array('lang' => 'DESC'),
			));
			if($userItemLink) {
				$username = $userItemLink['UserItemLink']['content'];
			}
		}
		if($username == '') {
			// 会員名称が取得できなければHandleをセットする。
			$username = $this->assignedTags['{X-USER}'];
		}
		return $username;
	}

/**
 * 投稿日付
 * subject,body変換文字列取得
 *
 * @param void
 * @return string
 */
	protected function _assignToDateTag() {
		// 現在時刻をセット　DBの値と完全に一致しないが、おおよその値で問題ないためセット
		return $this->_controller->Page->date($this->_controller->Page->nowDate(), __('Y-m-d H:i'));
	}

/**
 * URL
 * subject,body変換文字列取得
 *
 * @param void
 * @return string
 */
	protected function _assignUrlTag() {
		return Router::url($this->assignedTags['{X-URL}'], true);
	}
}