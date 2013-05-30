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
 * Mailリスト
 *
 * @var array
 */
	public $mails = null;

/**
 * subject,body変換文字列
 *
 * @var array
 */
	public $assignedTags = array();

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
 * @return void
 * @throws SocketException
 */
	public function send($emailFormat = 'auto') {
		App::uses('CakeEmail', 'Network/Email');
		$Email = new CakeEmail('default');
		$htmlmail = Configure::read(NC_CONFIG_KEY.'.'.'htmlmail');
		$mobileHtmlmail = Configure::read(NC_CONFIG_KEY.'.'.'mobile_htmlmail');
		if($emailFormat == 'auto') {
			$emailFormat = ($htmlmail == _ON) ? 'html' : 'text';
		}

		$this->_assignTags();

		$subject = $this->replaceTags($this->subject);

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
				$Email->to($emailRes['mobileEmail'])
					->emailFormat($mobileEmailFormat)
					->subject($subject)
					->send($this->replaceTags($this->body, $mobileEmailFormat));
			}
			if(count($emailRes['email']) > 0) {
				$mails = $emailRes['email'];
			} else {
				return;
			}
		}
		try{
			$Email->to($mails)
				->emailFormat($emailFormat)
				->subject($subject)
				->send($this->replaceTags($this->body, $emailFormat));
		}catch(Exception $e){
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
			// 未承認記事　主坦通知
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
		$this->assignedTags = array(
			'{X-SITE_NAME}' => "",
			'{X-ROOM}' => '',
			'{X-PAGE}' => '',
			'{X-CONTENT_NAME}' => '',
			'{X-SUBJECT}' => '',
			'{X-BODY}' => '',
			'{X-USER}' => '',
			'{X-TO_DATE}' => '',
			'{X-URL}' => '',
		);
	}

/**
 * subject,body変換文字列をReplace
 *
 * @param  string $str
 * @param string $emailFormat html or text
 * @return string
 */
	public function replaceTags($str, $emailFormat = 'html') {
		foreach($this->assignedTags as $key => $assignedTag) {
			if($emailFormat == 'text' && $key == '{X-BODY}') {
				$assignedTag = $this->convertHtmlToText($assignedTag);
			}
			if($emailFormat == 'html') {
				$assignedTag = h($assignedTag);
			}
			$str = str_replace($key, $assignedTag, $str);
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
 * subject,body変換文字列セット
 *
 * @param void
 * @return void
 */
	protected function _assignTags() {
		if($this->assignedTags['{X-SITE_NAME}'] == '') {
			$this->assignedTags['{X-SITE_NAME}'] = Configure::read(NC_CONFIG_KEY.'.'.'sitename');
		}
		if(isset($this->_controller->nc_page)) {
			$page['Page'] = $this->_controller->Page->setPageName($this->_controller->nc_page['Page']);
		}

		if($this->assignedTags['{X-ROOM}'] == '') {
			// 権限を付与したショートカットの場合、権限元のルームではなく投稿があったルームをセット
			$roomName = '';
			if(isset($this->_controller->nc_page)) {
				$roomName = isset($this->_controller->nc_page['CommunityLang']['community_name']) ? $this->_controller->nc_page['CommunityLang']['community_name'] :
					(($page['Page']['id'] == $page['Page']['room_id']) ?
					$page['Page']['page_name'] : null);
				if(!isset($roomName)) {
					$active_page = $this->_controller->Page->findIncludeComunityLang($this->_controller->nc_page['Page']['room_id']);
					$active_page['Page'] = $this->_controller->Page->setPageName($active_page['Page']);
					$roomName = isset($active_page['CommunityLang']['community_name']) ? $active_page['CommunityLang']['community_name'] :
						$active_page['Page']['page_name'];
				}
			}
			$this->assignedTags['{X-ROOM}'] = $roomName;
		}

		if($this->assignedTags['{X-PAGE}'] == '') {
			// 権限を付与したショートカットの場合、権限元のルームではなく投稿があったページをセット
			$pageName = '';
			if(isset($this->_controller->nc_page)) {
				$pageName = $this->_controller->nc_page['Page']['page_name'];
			}
			$this->assignedTags['{X-PAGE}'] = $pageName;
		}

		if($this->assignedTags['{X-CONTENT_NAME}'] == '') {
			$contentName = '';
			if(isset($this->_controller->nc_block)) {
				$contentName = $this->_controller->nc_block['Content']['title'];
			}
			$this->assignedTags['{X-CONTENT_NAME}'] = $contentName;
		}

		if($this->assignedTags['{X-USER}'] == '') {
			$handle = '';
			if(isset($this->userId)) {
				$user = $this->_controller->User->findById($this->userId);
				$handle = $user['User']['handle'];
			} else {
				$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
				if(isset($loginUser['handle'])) {
					$handle = $loginUser['handle'];
				}
			}
			$this->assignedTags['{X-USER}'] = $handle;
		}

 		if($this->assignedTags['{X-TO_DATE}'] == '') {
 			// 現在時刻をセット　DBの値と完全に一致しないが、おおよその値で問題ないためセット
 			$this->assignedTags['{X-TO_DATE}'] = $this->_controller->Page->date($this->_controller->Page->nowDate(), __('Y-m-d H:i'));
 		}

 		if(is_array($this->assignedTags['{X-URL}'])) {
 			$this->assignedTags['{X-URL}'] = Router::url($this->assignedTags['{X-URL}'], true);
 		}
	}
}