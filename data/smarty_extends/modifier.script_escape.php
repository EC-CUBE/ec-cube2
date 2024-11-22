<?php
require_once __DIR__ . '/../vendor/ezyang/htmlpurifier/library/HTMLPurifier.auto.php';
/**
 * Scriptタグをエスケープする
 *
 * @param  string $value 入力
 *
 * @return string $value マッチした場合は変換後の文字列、しない場合は入力された文字列をそのまま返す。
 */
function smarty_modifier_script_escape($value)
{
    // パフォーマンス低下を軽減するため文字列以外は処理しない。
    // TODO: Stringable なオブジェクトも対象とするのが安全かもしれない。しかし、この modifier は、default_modifiers に設定しているため、影響が見通せない (文字列として返して良いのか不確か)。
    if (!is_string($value)) {
        return $value;
    }

    static $pattern;
    if (is_null($pattern)) {
        $pattern = "<script.*?>|<\/script>|javascript:|<svg.*(onload|onerror).*?>|<img.*(onload|onerror).*?>|<body.*onload.*?>|<iframe.*?>|<object.*?>|<embed.*?>|";

        // 追加でサニタイズするイベント一覧
        $escapeEvents = [
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
        ];

        // イベント毎の正規表現を生成
        $generateHtmlTagPatterns = array_map(function ($str) {
            return "<(\w+)([^>]*\s)?\/?".$str.'[^>]*>';
        }, $escapeEvents);
        $pattern .= implode('|', $generateHtmlTagPatterns).'|';
        $pattern .= "(\"|').*(onerror|onload|".implode('|', $escapeEvents).").*=.*(\"|').*";
        $pattern = "/{$pattern}/i";
    }

    // マッチしたら文字列を置き換える
    // - preg_replace は、(たとえ置換が発生しなくても) preg_match より重い。置換が発生する頻度は極めて低いはずなので、preg_match で事前判定する。
    if (preg_match($pattern, $value)) {
        // 置き換える文字列
        $convert = '#script escaped#';
        $value = preg_replace($pattern, $convert, $value);
    }

    // 念のために HTMLPurifier でサニタイズ
    $config = HTMLPurifier_Config::createDefault();
    $config->set('Cache.SerializerPath', __DIR__ . '/../cache');
    $purify = new HTMLPurifier($config);

    return $purify->purify($value ?? '');
}
