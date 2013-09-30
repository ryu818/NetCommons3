<?php
/**
 * 追加CSSテンプレート
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Upload.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<script id="pages-style-css-template" type="text/html">
	<style id="${id}" type="text/css">
	<!--
	{{each(title, csses) data}}
	{{if data[title]}}
		{{if title == 'a' }}
		a,a:link {
		{{else}}
		${title} {
		{{/if}}
			{{each(name, value) csses}}
				{{if !value }}
				{{else name == 'font-family'}}
				${name}:'${value}', 'Lucida Grande','Hiragino Kaku Gothic ProN', sans-serif;*font-size:small;
				{{else}}
				${name}:${value};
				{{/if}}
			{{/each}}
		}
	{{/if}}
	{{/each}}
	-->
	</style>
</script>