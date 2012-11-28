<?php
/**
 * SQL Dump element.  Dumps out SQL log information
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.View.Elements
 * @since         CakePHP(tm) v 1.3
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
//echo('<span class="nc-log-second"> ('.(sprintf('%01.04f', round(microtime(true) - TIME_START, 4))).'s)</span>');
if (!class_exists('ConnectionManager') || Configure::read('debug') < 2) {
	return false;
}
$noLogs = !isset($logs);
if ($noLogs):
	$sources = ConnectionManager::sourceList();

	$logs = array();
	foreach ($sources as $source):
		$db = ConnectionManager::getDataSource($source);
		if (!method_exists($db, 'getLog')):
			continue;
		endif;
		$logs[$source] = $db->getLog();
	endforeach;
	//$php_logs = Debugger::getLog();
	$php_logs = Configure::read(NC_SYSTEM_KEY.'.php_logs');
	$current_urls = Configure::read(NC_SYSTEM_KEY.'.current_urls');
	$form_get_values = Configure::read(NC_SYSTEM_KEY.'.form_get_values');
	$form_post_values = Configure::read(NC_SYSTEM_KEY.'.form_post_values');
	$nc_execute_start_times = Configure::read(NC_SYSTEM_KEY.'.nc_execute_start_times');
	$nc_execute_end_times = Configure::read(NC_SYSTEM_KEY.'.nc_execute_end_times');
endif;
$header_log = '<table class="nc-log"><tr><td class="nc-php-log">';
if (count($php_logs) > 0):
	for($i = 0; $i < count($php_logs); $i++) {
		$header_log .= $php_logs[$i]."\n";
	}
endif;
if (Configure::read('debug') != 1 && isset($current_urls) && count($current_urls) > 0):
	$pre_post_str = null;
	foreach($current_urls as $key => $current_url) {
		if($key == 0) {
			$header_log .= '<div><a href="#" onclick="$(this).parents(\'.nc-log:first\').next().toggle();return false;">'.__('Show', true).'</a>';
			$header_log .= '&nbsp;|&nbsp;<a href="#" onclick="$(this).parents(\'.nc-log:first\').next().remove();$(this).parents(\'.nc-log:first\').remove();return false;">'.__('Delete', true).'</a>';
		} else {
			$header_log .= '<div style="padding:0 0 0 20px;">';
		}
		$header_log .= '&nbsp;<span class="bold">URL:</span>'.h($current_url);
		if($key != 0) {
			$start = $nc_execute_start_times[$key - 1];
			$end = $nc_execute_end_times[$key - 1];
		} else {
			$start = TIME_START;
			$end = microtime(true);
		}
		$header_log .= '<span class="nc-log-second"> ('.(sprintf('%01.04f', round($end - $start, 4))).'s)</span>';
		if (isset($form_get_values[$key])):
			$get_str = (string)var_export(h($form_get_values[$key]), true);
			if($get_str != $pre_get_str) {
				$header_log .= '<p class="nc-log-get"><span class="bold">GET :</span>'.$get_str.'</p>';
			}
			$pre_get_str = $get_str;
		endif;
		if (isset($form_post_values[$key])):
			$post_str = (string)var_export(h($form_post_values[$key]), true);
			if($post_str != $pre_post_str) {
				$header_log .= '<p class="nc-log-post"><span class="bold">POST:</span>'.$post_str.'</p>';
			}
			$pre_post_str = $post_str;
		endif;
		$header_log .= '</div>';
	}
endif;
if(Configure::read('debug') != 1 || count($php_logs) > 0) {
	$header_log .= '</td></tr></table>';
	echo($header_log);
}
if(Configure::read('debug') == 1) {
		return;
}
if ($noLogs || isset($_forced_from_dbo_)):
	foreach ($logs as $source => $logInfo):
		$text = $logInfo['count'] > 1 ? 'queries' : 'query';
		printf(
			'<table class="nc-log" style="display:none;" summary="Cake SQL Log">',
			preg_replace('/[^A-Za-z0-9_]/', '_', uniqid(time(), true))
		);
		printf('<caption>(%s) %s %s took %s ms</caption>', $source, $logInfo['count'], $text, $logInfo['time']);
	?>
	<thead>
		<tr><th>Nr</th><th>Query</th><th>Error</th><th>Affected</th><th>Num. rows</th><th>Took (ms)</th></tr>
	</thead>
	<tbody>
	<?php
		foreach ($logInfo['log'] as $k => $i) :
			$i += array('error' => '');
			if (!empty($i['params']) && is_array($i['params'])) {
				$bindParam = $bindType = null;
				if (preg_match('/.+ :.+/', $i['query'])) {
					$bindType = true;
				}
				foreach ($i['params'] as $bindKey => $bindVal) {
					if ($bindType === true) {
						$bindParam .= h($bindKey) ." => " . h($bindVal) . ", ";
					} else {
						$bindParam .= h($bindVal) . ", ";
					}
				}
				$i['query'] .= " , params[ " . rtrim($bindParam, ', ') . " ]";
			}
			if(isset($i['global_count']) && isset($current_urls[$i['global_count']])) {
				echo ("<tr><td class=\"nc-log-child-url\" colspan=\"6\">".$current_urls[$i['global_count']]."</td></tr>");
				unset($current_urls[$i['global_count']]);
			}
			$pattern = "/^(INSERT INTO|CREATE TABLE IF NOT EXISTS|CREATE TABLE|ALTER TABLE|UPDATE|DELETE)(\s)+/siU";
			if (preg_match($pattern, $i['query'], $matches)) {
				$class_name = ' class="nc-log-save"';
			} else {
				$class_name = '';
			}
			echo "<tr><td".$class_name.">" . ($k + 1) . "</td><td>" . h($i['query']) . "</td><td>{$i['error']}</td><td style = \"text-align: right\">{$i['affected']}</td><td style = \"text-align: right\">{$i['numRows']}</td><td style = \"text-align: right\">{$i['took']}</td></tr>\n";
		endforeach;
	?>
	</tbody></table>
	<?php
	endforeach;
else:
	echo '<p>Encountered unexpected $logs cannot generate SQL log</p>';
endif;