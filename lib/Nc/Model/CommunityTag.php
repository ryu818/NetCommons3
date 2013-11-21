<?php
/**
 * CommunityTagモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class CommunityTag extends AppModel
{
	public $validate = array();

/**
 * バリデート処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
*/
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);

		//エラーメッセージ取得
		$this->validate = array(
			'room_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
			),
			'community_sum_tag_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
			),
			'tag_value' => array(
				'notEmpty'  => array(
					'rule' => array('notEmpty'),
					'last' => true,
					'required' => true,
					'message' => __('Please be sure to input.')
				),
				'maxLength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_TAG_NAME_LEN),
					'last' => true ,
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TAG_NAME_LEN)
				)
			),
			'display_sequence' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
			),
		);
	}

/**
 * CommunityTagカンマ区切り取得
 * @param   integer $roomId
 * @param   string  $lang
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function findCommaDelimitedTags($roomId, $lang) {
		$communityTags = $this->find('list', array(
			'fields' => array('tag_value'),
			'conditions' => array('room_id' => $roomId, 'lang' => $lang),
			'order' => array('display_sequence' => 'ASC')
		));
		return implode(',', $communityTags);
	}

/**
 * CommunityTagバリデート処理
 * @param   string  $tagValuestag_valueのカンマ区切り文字列
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function validateTags($tagValues) {
		// validate
		$tagValueArrs = explode(',', $tagValues);
		foreach($tagValueArrs as $key => $tagValue) {
			$tagValueArrs[$key] = $tagValue = trim(mb_convert_kana( $tagValue, "s"));
			if($tagValue == '') {
				continue;
			}
			$this->set(array('tag_value' => $tagValue));
			if (!$this->validates(array('fieldList' => array('tag_value')))) {
				return false;
			}
		}
		return true;
	}

/**
 * CommunityTag,CommunitySumTag登録処理
 * @param   integer $roomId
 * @param   string  $lang
 * @param   string  $tagValuestag_valueのカンマ区切り文字列
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function saveTags($roomId, $lang, $tagValues = '') {
		$CommunitySumTag = ClassRegistry::init('CommunitySumTag');
		$tagValueArrs = explode(',', $tagValues);
		$communityKeyTags = array();
		$communitySumKeyTags = array();
		$communitySumKeysTags = $this->find('all', array(
			'fields' => array('id', 'tag_value', 'community_sum_tag_id'),
			'conditions' => array('room_id' => $roomId, 'lang' => $lang),
			'order' => array('display_sequence' => 'ASC')
		));
		foreach($communitySumKeysTags as $bufCommunitySumKeyTags) {
			$communityKeyTags[$bufCommunitySumKeyTags[$this->alias]['tag_value']] = $bufCommunitySumKeyTags[$this->alias]['id'];
			$communitySumKeyTags[$bufCommunitySumKeyTags[$this->alias]['tag_value']] = $bufCommunitySumKeyTags[$this->alias]['community_sum_tag_id'];
		}

		$count = 1;
		$savedCommunityTags = array();

		foreach($tagValueArrs as $tagValue) {
			$tagValue = trim(mb_convert_kana( $tagValue, "s"));
			if($tagValue == '' || isset($savedCommunityTags[$tagValue])) {
				continue;
			}
			$communitySumTag = $CommunitySumTag->find('first', array(
				'fields' => array($CommunitySumTag->primaryKey),
				'conditions' => array('tag_value' => $tagValue, 'lang' => $lang),
			));
			if(isset($communitySumTag[$CommunitySumTag->alias])) {
				// CommunitySumTag.used_numberインクリメント
				if(!isset($communitySumKeyTags[$tagValue]) || $communitySumKeyTags[$tagValue] != $communitySumTag[$CommunitySumTag->alias][$CommunitySumTag->primaryKey]) {
					if(!$CommunitySumTag->incrementSeq(array($CommunitySumTag->alias.'.'. $CommunitySumTag->primaryKey => $communitySumTag[$CommunitySumTag->alias][$CommunitySumTag->primaryKey]), 'used_number')){
						return false;
					}
				}
				$sumId = $communitySumTag[$CommunitySumTag->alias][$CommunitySumTag->primaryKey];
			} else if(!isset($communityKeyTags[$tagValue])){
				// CommunitySumTag insert
				$saveCommunitySumTag = array(
					$CommunitySumTag->alias => array(
						'tag_value' => $tagValue,
						'lang' => $lang,
						'used_number' => 1,
					)
				);
				$CommunitySumTag->create();
				$CommunitySumTag->set($saveCommunitySumTag);
				if(!$CommunitySumTag->save($saveCommunitySumTag)) {
					return false;
				}
				$sumId = $CommunitySumTag->id;
			} else {
				$sumId = $communitySumKeyTags[$tagValue];
			}
			$savedCommunityTags[$tagValue][$this->alias] = array(
				'id' => isset($communityKeyTags[$tagValue]) ? $communityKeyTags[$tagValue] : 0,
				'room_id' => $roomId,
				'community_sum_tag_id' => $sumId,
				'tag_value' => $tagValue,
				'lang' => $lang,
				'display_sequence' => $count
			);
			$count++;
		}

		foreach($communityKeyTags as $tagValue => $id) {
			if(!isset($savedCommunityTags[$tagValue])) {
				// CommunitySumTag.used_numberデクリメントOR削除
				$communitySumTag = $CommunitySumTag->find('first', array(
					'fields' => array($CommunitySumTag->primaryKey, 'used_number'),
					'conditions' => array('tag_value' => $tagValue, 'lang' => $lang),
				));
				if(isset($communitySumTag[$CommunitySumTag->alias])) {
					if($communitySumTag[$CommunitySumTag->alias]['used_number'] > 1) {
						if(!$CommunitySumTag->decrementSeq(array('tag_value' => $tagValue, 'lang' => $lang), 'used_number')){
							return false;
						}
					} else {
						if(!$CommunitySumTag->delete($communitySumTag[$CommunitySumTag->alias]['id'])) {
							return false;
						}
					}
					if(!$this->delete($id)) {
						return false;
					}
				}
			}
		}

		// 登録 ・ 更新
		foreach($savedCommunityTags as $savedCommunityTag) {
			$this->create();
			$this->set($savedCommunityTag);
			if(!$this->save($savedCommunityTag)) {
				return false;
			}
		}
		return true;
	}


/**
 * CommunityTag,CommunitySumTag削除処理
 * @param   integer $roomId
 * @param   string  $lang
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function deleteTags($roomId, $lang) {
		return $this->saveTags($roomId, $lang);
	}
}