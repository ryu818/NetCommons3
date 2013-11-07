<?php
/**
 * WYSIWYG内画面リビジョンリストの表示
 *
 * Model Revisions $revisions      :  Revision->findRevisions実行結果
 * array $options                  :  View->linkのoptions(default: 'title' => $date, 'data-pjax' => '#'.$id ) セットするとマージする。
 * array $url                      :  View->linkのurl(default: 'action' => 'revision', 'revision_id' => $revision['Revision']['id'], '#' => $id ) セットするとマージする。
 *                                        投稿記事のidはセットすること デフォルトaction=revisionへリンクする。
 * @copyright     Copyright 2012, NetCommons Project
 * @package       View.Elements
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<ul class="nc-revisions">
	<?php foreach ($revisions as $revision): ?>
		<li>
			<?php
				$revisionTitle = $this->TimeZone->date($revision['Revision']['created'], __('Y-m-d H:i'));

				$defaultUrl = array('action' => 'revision', 'revision_id' => $revision['Revision']['id'], '#' => $id,);
				$setUrl = isset($url) ? array_merge($defaultUrl, $url) : $defaultUrl;

				$defaultOptions = array('title' => $revisionTitle, 'data-pjax' => '#'.$id);
				$setOptions = isset($options) ? array_merge($defaultOptions, $options) : $defaultOptions;

				echo $this->Html->link($revisionTitle, $setUrl, $setOptions);
				if($revision['Revision']['revision_name'] == 'auto-draft') {
					echo '&nbsp;'.__('[Autosave]');
				}
				echo ('@'.h($revision['Revision']['created_user_name']));
			?>
		</li>
	<?php endforeach; ?>
</ul>