<?php
/**
 * リビジョン比較-復元画面表示
 * string           $title               : タイトル設定 リビジョン - $title - モジュール名 - サイト名 となる
 * string|array     $cancel_url          : キャンセル時のURL
 * Model Revisions  $revisions           : Revision->findRevisions実行結果（isAll = true）
 * array $url                            :  View->linkのurl(default: 'revision_id' => $revision['Revision']['id'], '#' => $id ) セットするとマージする。
 *                                          Form->actionのurl(default: '#' => $id ) セットするとマージする。
 *                                          投稿記事のidはセットすること。
 * integer          $current_revision_id : 現在のリビジョンID
 * integer          $revision_id         : 比較先リビジョンID
 * @copyright     Copyright 2012, NetCommons Project
 * @package       View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<?php
$this->extend('/Frame/block');
$page_title = isset($title) ? __('Revisions') . NC_TITLE_SEPARATOR . $title : __('Revisions');
$this->element('Pages/title_assign', array('title' => $page_title));
$defaultUrl = array('controller' => $this->request->params['controller'], '#' => '');
$setUrl = isset($url) ? array_merge($defaultUrl, $url) : $defaultUrl;
echo $this->Form->create(null, array('url' => $setUrl, 'data-ajax' => '#'.$id));
foreach ($revisions as $revision) {
	if(!isset($top_revision)) {
		$top_revision = $revision;
	}
	if($current_revision_id == $revision['Revision']['id']) {
		$current_revision = $revision;
	}
	if($revision_id == $revision['Revision']['id']) {
		$show_revision = $revision;
	}
}
?>
<h2 class="nc-revisions-title">
	<?php
		echo h($page_title);
		echo NC_TITLE_SEPARATOR . $this->TimeZone->date($show_revision['Revision']['created'], __('Y-m-d H:i:s'));
		if($show_revision['Revision']['revision_name'] == 'auto-draft') {
			echo '&nbsp;'. __('[Autosave]');
		}
	?>
</h2>
<?php if (!isset($diffText)): ?>
<ul class="nc-lists clearfix">
	<li>
		<dl>
			<dt>
				<?php
					echo __('Content');
				?>
			</dt>
			<dd>
				<?php
					echo $show_revision['Revision']['content'];
				?>
			</dd>
		</dl>
	</li>
</ul>
<?php elseif($diffText == ''): ?>
<div class="nc-diff-outer-identical">
	<?php echo(__('This revision is identical.')); ?>
</div>
<?php else: ?>
<div class="nc-diff-outer">
	<table class="nc-diff-compare">
		<thead>
			<th scope="col" colspan="2">
				<?php
					$revisionTitle = $this->TimeZone->date($show_revision['Revision']['created'], __('Y-m-d H:i:s'));
					$defaultUrl = array('revision_id' => $show_revision['Revision']['id'], '#' => $id);
					$setUrl = isset($url) ? array_merge($defaultUrl, $url) : $defaultUrl;
					$a = $this->Html->link(
						$revisionTitle,
						$setUrl,
						array('title' => $revisionTitle, 'data-pjax' => '#'.$id)
					);
					echo __('Older posting: %s', $a);
				?>
			</th>
			<th scope="col" colspan="2">
				<?php
					$revisionTitle = $this->TimeZone->date($current_revision['Revision']['created'], __('Y-m-d H:i:s'));
					$defaultUrl = array('revision_id' => $current_revision['Revision']['id'], '#' => $id);
					$setUrl = isset($url) ? array_merge($defaultUrl, $url) : $defaultUrl;
					$a = $this->Html->link(
						$revisionTitle,
						$setUrl,
						array('title' => $revisionTitle, 'data-pjax' => '#'.$id)
					);

					if($top_revision['Revision']['id'] == $current_revision['Revision']['id']) {
						$a .= '&nbsp;'. __('[Current Revision]');
					}
					echo __('Newer posting: %s', $a);
				?>
			</th>
		</thead>
		<?php echo($diffText); ?>
	</table>
</div>
<?php endif; ?>
<?php if (count($revisions) > 0): ?>
<table id="nc-revisions<?php echo($id); ?>" class="nc-revisions">
	<thead>
		<tr class="nc-title-color">
			<th scope="col"><?php echo(__('Old')); ?></th>
			<th scope="col"><?php echo(__('New')); ?></th>
			<th class="nc-revisions_date" scope="col"><?php echo(__('Date Created')); ?></th>
			<th class="nc-revisions_author" scope="col"><?php echo(__('Author')); ?></th>
			<th class="nc-revisions_manage" scope="col"><?php echo(__('Manage')); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($revisions as $key => $revision): ?>
		<tr<?php if ($key%2): ?> class="nc-revisions-erow"<?php endif; ?>>
			<th class="row">
				<?php
					$settings = array(
						'type' => 'radio',
						'options' => array($revision['Revision']['id'] => ''),
						'value' => $revision_id,
						'div' => false,
						'label' => false,
						'legend' => false,
						'name' => 'revision_id',
					);
					if($key == 0 && $revision_id != $revision['Revision']['id']
							&& $current_revision_id != $revision['Revision']['id']) {
						$settings['class'] = 'display-none';
					}
					echo $this->Form->input('revision_id', $settings);
				?>
			</th>
			<th class="row">
				<?php
					$settings = array(
						'type' => 'radio',
						'options' => array($revision['Revision']['id'] => ''),
						'value' => $current_revision_id,
						'div' => false,
						'label' => false,
						'legend' => false,
						'name' => 'current_revision_id',
					);
					if($key != 0
						&& ($revision_id > $revision['Revision']['id']
							|| $current_revision_id < $revision['Revision']['id'])) {
						$settings['class'] = 'display-none';
					}
					echo $this->Form->input('current_revision_id', $settings);
				?>
			</th>
			<td>
				<?php
					$revisionTitle = $this->TimeZone->date($revision['Revision']['created'], __('Y-m-d H:i:s'));
					$defaultUrl = array('revision_id' => $revision['Revision']['id'], '#' => $id);
					$setUrl = isset($url) ? array_merge($defaultUrl, $url) : $defaultUrl;
					echo $this->Html->link(
						$revisionTitle,
						$setUrl,
						array('title' => $revisionTitle, 'data-pjax' => '#'.$id)
					);
					if($current_revision_id == $revision['Revision']['id']) {
						echo '&nbsp;'. __('[Current Revision]');
					} else if($revision['Revision']['revision_name'] == 'auto-draft') {
						echo '&nbsp;'. __('[Autosave]');
					}
				?>
			</td>
			<td>
				<?php
					/* TODO:後にリンクにする */
					echo (h($revision['Revision']['created_user_name']));
				?>
			</td>
			<td>
				<?php
					/* 復元 */
					if($top_revision['Revision']['id'] != $revision['Revision']['id']) {
						$defaultUrl = array('revision_id' => $revision['Revision']['id'], '#' => $id);
						$setUrl = isset($url) ? array_merge($defaultUrl, $url) : $defaultUrl;
						echo $this->Html->link(
							__('Restore'),
							$setUrl,
							array(
								'title' => __('Restore'),
								'data-pjax' => '#'.$id,
								'data-ajax-type' => 'post',
								'data-ajax-serialize' => true,
								'data-ajax-confirm' => __('Restoring post from %s. <br />Are you sure to proceed?', $revisionTitle),
							)
						);
					}
				?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>
<?php
	echo $this->Html->div('nc-btn-bottom',
		$this->Form->button(__('Compare Revisions'), array(
			'name' => 'compare', 'class' => 'nc-common-btn',
			'onclick' => "$.Revision.compare(event, '".$id."');",
		)).
		$this->Form->button(__('Cancel'),
			array(
				'name' => 'cancel', 'class' => 'nc-common-btn', 'type' => 'button',
				'data-pjax' => '#'.$id, 'data-ajax-url' =>  $this->Html->url($cancel_url)
			)
		)
	);
	echo $this->Form->end();
	echo $this->Html->css(array('revisions/revisions'));
	echo $this->Html->script(array('revisions/revisions'));
?>
<script>
$(function(){
	$('#nc-revisions<?php echo($id); ?>').Revision('<?php echo($id);?>');
});
</script>

