<?php
class LC_Page_Index_Ex extends LC_Page_Index
{
    public function init()
    {
        parent::init();
        $this->tpl_subtitle = '(カスタマイズ)';
    }

    public function process()
    {
        parent::process();
    }
}
