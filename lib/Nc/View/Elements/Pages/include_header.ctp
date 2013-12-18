<?php
/**
 * ヘッダー出力
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       View.Elements
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
	$nc_user = $this->Session->read(NC_AUTH_KEY.'.'.'User');
	$ncMode = intval($this->Session->read(NC_SYSTEM_KEY.'.'.'mode'));
	$locale = Configure::read(NC_SYSTEM_KEY.'.locale');
	$version = Security::hash(NC_VERSION, null, true);

// TODO:test
if (!empty($page_style['file'])) {
	echo '<link href="theme/page_styles/'.$page_style['file'].'" rel="stylesheet "type="text/css">';
}

	echo "\n".$this->fetch('meta');
	echo $this->Html->script('master.min');
	echo $this->fetch('script');
	echo '<!--[if IE]>'."\n".$this->Html->script('html5/html5', array('inline' => true, 'data-title' => 'IE')).'<![endif]-->';
?>
<script>(function($) {
<?php
if($this->params['plugin'] == '' && $this->params['controller'] == 'pages') {
	echo '	$.ajaxSetup({headers: {"X-NC-PAGE":"true"}});'."\n";
	//TODO:必要ないかも？
	echo '	$._block_type = \'blocks\';'."\n";
} else {
	//TODO:必要ないかも？
	echo '	$._block_type = \'active-blocks\';'."\n";
}
echo '	$._nc = Array();'."\n";
echo '	$._mode = '.$ncMode.";\n";
echo '	$._debug = '.intval(Configure::read('debug')).";\n";
echo '	$._display_header_menu = '.intval(Configure::read(NC_CONFIG_KEY.'.'.'display_header_menu')).";\n";
echo '	$._base_url = \''.$this->Html->url('/').'\';'."\n";
echo '	$._full_base_url = \''.$this->Html->url('/', true).'\';'."\n";
echo '	$._current_url = \''.rtrim($this->Html->url(), '/'). '/'.'\';'."\n";
$pemalink = str_replace('%2F', '/', urlencode(rtrim(Configure::read(NC_SYSTEM_KEY.'.permalink'), '/')));
if($pemalink != '')
	$pemalink .= '/';
echo '	$._page_url = \''.$this->Html->url('/').$pemalink. '\';'."\n";
echo '	$._nc.nc_wysiwyg = new Object();'."\n";
echo '	$._nc.nc_wysiwyg[\'allow_attachment\'] = '.(isset($nc_user['allow_attachment']) ? $nc_user['allow_attachment'] : _OFF).';'."\n";
echo '	$._nc.nc_wysiwyg[\'allow_video\'] = '.(isset($nc_user['allow_video']) ? intval($nc_user['allow_video']) : _OFF).';'."\n";
echo '	$._nc.nc_wysiwyg[\'allow_js\'] = '.($nc_user['allow_htmltag_flag'] ? _ON : _OFF).';'."\n";
echo '	$._nc.show_count = new Object();'."\n";
if(isset($pages) && isset($page_id_arr)) {
	echo '	$._nc.show_count['.$page_id_arr[0].'] = '.$pages[$page_id_arr[0]]['Page']['show_count'].';'."\n";
	if(!empty($page_id_arr[1]))
		echo '	$._nc.show_count['.$page_id_arr[1].'] = '.$pages[$page_id_arr[1]]['Page']['show_count'].';'."\n";
	if(!empty($page_id_arr[2]))
		echo '	$._nc.show_count['.$page_id_arr[2].'] = '.$pages[$page_id_arr[2]]['Page']['show_count'].';'."\n";
	if(!empty($page_id_arr[3]))
		echo '	$._nc.show_count['.$page_id_arr[3].'] = '.$pages[$page_id_arr[3]]['Page']['show_count'].';'."\n";
	if(!empty($page_id_arr[4]))
		echo '	$._nc.show_count['.$page_id_arr[4].'] = '.$pages[$page_id_arr[4]]['Page']['show_count'].';'."\n";
} else if(isset($page)) {
	echo '	$._nc.show_count['.$page['Page']['id'].'] = '.$page['Page']['show_count'].';'."\n";
}
echo '	$._nc.v = \''.$version.'\';'."\n";
?>
$.Common.pjaxPrevUrl = location.href;
})(jQuery);</script>
<?php
	if(!isset($locale) || !file_exists(WWW_ROOT.'locale/'.$locale.'/lang.js')) {
		$locale = 'jpn';
	}
	if($locale) {
		echo $this->Html->script('master.lang.min');
	}

	echo $this->Html->css('master.min');
	echo $this->fetch('css');

	// PageStyle
	if(!empty($nc_page_styles)) {
		$base = $this->Html->url('/', true);
		foreach($nc_page_styles as $nc_page_style) {
			echo $this->Html->css($base.$nc_page_style['PageStyle']['file'], null, array('inline' => true));
		}
	}

?>
