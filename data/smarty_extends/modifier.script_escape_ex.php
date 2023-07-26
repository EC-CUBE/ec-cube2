<?php
/**
 * Scriptタグをエスケープする
 *
 * @param  string $value 入力
 * @return string $value マッチした場合は変換後の文字列、しない場合は入力された文字列をそのまま返す。
 */
function smarty_modifier_script_escape_ex($value)
{
    if (is_array($value)) return $value;

    $pattern = "<script.*?>|<\/script>|javascript:|<svg.*(onload|onerror).*?>|<img.*(onload|onerror).*?>|<body.*onload.*?>|<iframe.*?>|<object.*?>|<embed.*?>|";

    // 追加でサニタイズするイベント一覧
    $escapeEvents = array(
        'onmouse',
        'onclick',
        'onblur',
        'onfocus',
        'onresize',
        'onscroll',
        'ondblclick',
        'onchange',
        'onselect',
        'onsubmit',
        'onkey',
    );

    // イベント毎の正規表現を生成
    $generateHtmlTagPatterns = array_map(function($str) {
        return "<(\w+)([^>]*\s)?\/?".$str."[^>]*>";
    }, $escapeEvents);
    $pattern .= implode("|", $generateHtmlTagPatterns)."|";
    $pattern .= "(\"|').*(onerror|onload|".implode("|", $escapeEvents).").*=.*(\"|').*";

    // 正規表現をまとめる
    $attributesPattern = "/${pattern}/i";

    // 置き換える文字列
    $convert = '#script tag escaped#';

    // マッチしたら文字列を置き換える
    return preg_replace($attributesPattern, $convert, $value);
}
