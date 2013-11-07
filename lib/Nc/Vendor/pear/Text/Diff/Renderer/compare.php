<?php
/**
 * NetCommons用Text_Diff Rendererクラス
 *
 * <pre>
 * WYSIWYG同士の文字列比較を描画する。
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controllers.Components
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */

/** Text_Diff_Renderer */
require_once 'Text/Diff/Renderer.php';

/**
 * WYSIWYG同士の文字列比較を描画する。
 * (inline Rendererから派生して作成)
 *
 * @author  Ryuji.M
 * @package Text_Diff
 */
class Text_Diff_Renderer_compare extends Text_Diff_Renderer {

    /**
     * Number of leading context "lines" to preserve.
     */
    var $_leading_context_lines = 10000;

    /**
     * Number of trailing context "lines" to preserve.
     */
    var $_trailing_context_lines = 10000;

    /**
     * Prefix for inserted text.
     */
    var $_ins_prefix = '<ins>';

    /**
     * Suffix for inserted text.
     */
    var $_ins_suffix = '</ins>';

    /**
     * Prefix for deleted text.
     */
    var $_del_prefix = '<del>';

    /**
     * Suffix for deleted text.
     */
    var $_del_suffix = '</del>';

    /**
     * Header for each change block.
     */
    var $_block_header = '';

    /**
     * What are we currently splitting on? Used to recurse to show word-level
     * changes.
     */
    var $_split_level = 'lines';



    function _blockHeader($xbeg, $xlen, $ybeg, $ylen)
    {
        return $this->_block_header;
    }

    function _startBlock($header)
    {	/* 未使用 */
        return $header;
    }

    function _lines($lines, $prefix = ' ', $encode = true)
    {/* 未使用
        if ($encode) {
            array_walk($lines, array(&$this, '_encode'));
        }

        if ($this->_split_level == 'words') {
            return implode('', $lines);
        } else {
            return implode("\n", $lines) . "\n";
        }*/
    	return '';
    }

    function _added($lines, $encode = true)
    {
		$tr = '';
		foreach ($lines as $line) {
			if($encode)
				$this->_encode($line);
			$tr .= "<tr><td class=\"nc-diff-mark\"></td><td>&nbsp;</td><td class=\"nc-diff-mark-added\">+</td><td class=\"nc-diff-added\">".$line."</td></tr>\n";
		}
		return $tr;
    }

    function _deleted($lines, $encode = true)
    {
		$tr = '';
		foreach ($lines as $line) {
			if($encode)
				$this->_encode($line);
			$tr .= "<tr><td class=\"nc-diff-mark-deleted\">-</td><td class=\"nc-diff-deleted\">".$line."</td><td class=\"nc-diff-mark\"></td><td>&nbsp;</td></tr>\n";
		}
		return $tr;
    }

    function _context( $lines, $encode = true ) {
    	$tr = '';
		foreach ($lines as $line) {
			if($encode)
				$this->_encode($line);
			$tr .= "<tr><td class=\"nc-diff-mark\"></td><td class=\"nc-diff-context\">".$line."</td><td class=\"nc-diff-mark\"></td><td class=\"nc-diff-context\">".$line."</td></tr>\n";
		}
		return $tr;
    }

    function _changed($orig, $final)
    {
    	$tr = '';

		$max = max(count($orig), count($final));
		for($i = 0; $i < $max; $i++) {
			$origValue = isset($orig[$i]) ? $orig[$i] : '';
			$finalValue = isset($final[$i]) ? $final[$i] : '';

			$type = 'copy';
			$changed = false;
			$add = false;
			$delete = false;
			$origStr = '';
			$finalStr = '';

			if(!isset($preOrigValue) || $preOrigValue != $origValue) {
				$preOrigValue = $bufOrigValue = $origValue;
				$origArr = array();
				while ($iLen = mb_strlen($bufOrigValue))
				{
					array_push($origArr, mb_substr($bufOrigValue, 0, 1));
					$bufOrigValue = mb_substr($bufOrigValue, 1, $iLen);
				}
			}
			if(!isset($preFinalValue) || $preFinalValue != $finalValue) {
				$preFinalValue = $bufFinalValue = $finalValue;
				$finalArr = array();
				while ($iLen = mb_strlen($bufFinalValue))
				{
					array_push($finalArr, mb_substr($bufFinalValue, 0, 1));
					$bufFinalValue = mb_substr($bufFinalValue, 1, $iLen);
				}
			}

			$text_diff = new Text_Diff( 'auto', array( $origArr, $finalArr ) );

			foreach ($text_diff as $buf_text_diff) {
				foreach($buf_text_diff as $diff) {
					$bufOrigStr = '';
					$bufFinalStr = '';
					foreach($diff as $key => $diffArr) {
						if($diffArr === false) {
							continue;
						}
						foreach($diffArr as $diffStr) {
							if($key == 'orig') {
								$this->_encode($diffStr);
								$bufOrigStr .= $diffStr;
							}
							if($key == 'final') {
								$this->_encode($diffStr);
								$bufFinalStr .= $diffStr;
							}
						}
					}

					if(get_class($diff) == 'Text_Diff_Op_copy') {
						$changed = true;
						$origStr .= $bufOrigStr;
						$finalStr .= $bufFinalStr;
					} else if(get_class($diff) == 'Text_Diff_Op_change') {
						$changed = true;
						if($bufOrigStr != '')
							$origStr .= '<del>'. $bufOrigStr. '</del>';
						if($bufFinalStr != '')
							$finalStr .= '<ins>'. $bufFinalStr. '</ins>';
					} else if(get_class($diff) == 'Text_Diff_Op_add') {
						if($bufOrigStr != '') {
							$delete = true;
							$origStr .= '<del>'. $bufOrigStr. '</del>';
						} else if($bufFinalStr != '') {
							$add = true;
							$finalStr .= '<ins>'. $bufFinalStr. '</ins>';
						}
					} else if(get_class($diff) == 'Text_Diff_Op_delete') {
						if($bufOrigStr != '') {
							$delete = true;
							$origStr .= '<del>'. $bufOrigStr. '</del>';
						} else if($bufFinalStr != '') {
							$add = true;
							$finalStr .= '<ins>'. $bufFinalStr. '</ins>';
						}
					}
				}
			}

			if($add && !$delete && !$changed) {
				$type = 'add';
			} else if(!$add && $delete && !$changed) {
				$type = 'delete';
			} else if(!$add && !$delete && !$changed) {
				$type = 'copy';
			} else {
				$type = 'changed';
			}

			if($type == 'copy') {
				$tr .= "<tr><td></td><td>".$origValue."</td><td></td><td>".$finalValue."</td></tr>\n";
			} else if($type == 'changed') {
				$tr .= "<tr><td class=\"nc-diff-mark-deleted\">-</td><td class=\"nc-diff-deleted\">".$origStr."</td><td class=\"nc-diff-mark-added\">+</td><td class=\"nc-diff-added\">".$finalStr."</td></tr>\n";
			} else if($type == 'add') {
				$tr .= $this->_added(array($finalStr), false);
			} else if($type == 'delete') {
				$tr .= $this->_deleted(array($origStr), false);
			}
		}

    	return $tr;
    }


    function _splitOnWords($string, $newlineEscape = "\n")
    {
        // Ignore \0; otherwise the while loop will never finish.
        $string = str_replace("\0", '', $string);

        $words = array();
        $length = strlen($string);
        $pos = 0;

        while ($pos < $length) {
            // Eat a word with any preceding whitespace.
            $spaces = strspn(substr($string, $pos), " \n");
            $nextpos = strcspn(substr($string, $pos + $spaces), " \n");
            $words[] = str_replace("\n", $newlineEscape, substr($string, $pos, $spaces + $nextpos));
            $pos += $spaces + $nextpos;
        }

        return $words;
    }

    function _encode(&$string)
    {
        $string = htmlspecialchars($string);
    }
}
