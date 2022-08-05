<?php
class LC_Page_Admin_Home_Ex extends LC_Page_Admin_Home
{
    public function init()
    {
        parent::init();
    }

    public function process()
    {
        parent::process();
    }

    /**
     * @override
     */
    public function lfGetPHPVersion()
    {
        return 'PHP_VERSION_ID: '.PHP_VERSION_ID;
    }
}
