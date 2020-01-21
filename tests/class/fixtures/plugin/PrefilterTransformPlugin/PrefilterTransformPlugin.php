<?php

class PrefilterTransformPlugin extends SC_Plugin_Base
 {
     public function prefilterTransform(&$source, LC_Page_Ex $objPage, $filename) {
         $objTransform = new SC_Helper_Transform($source);
         switch($objPage->arrPageLayout['device_type_id']) {
         case DEVICE_TYPE_SMARTPHONE:
         case DEVICE_TYPE_PC:
             // 商品一覧画面
             if (strpos($filename, 'products/list.tpl') !== false) {
                 // see http://downloads.ec-cube.net/manual/12.0_plugin/plugin.pdf
                 $objTransform->select('h2.title')->insertBefore('<p>プラグイン仕様書の記述方法</p>');
             }
             if ('products/list.tpl' === $filename) {
                 $objTransform->select('h2.title')->insertBefore('<p>一部のプラグインは完全一致が使用されている</p>');
             }
             break;
         case DEVICE_TYPE_ADMIN:
         default:
         }
         $source = $objTransform->getHTML();
     }
 }
