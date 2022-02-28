<?php
return $routes = function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', function(array $vars) {
        $objPage = new LC_Page_Index_Ex();
        $objPage->skip_load_page_layout = true;
        $objPage->init();
        $layout = new SC_Helper_PageLayout_Ex();
        $layout->sfGetPageLayout($objPage, false, 'index.php');
        $objPage->process($vars);
    });
    $r->addRoute(['GET', 'POST'], '/products/list', function (array $vars) {
        $objPage = new LC_Page_Products_List_Ex();
        $objPage->skip_load_page_layout = true;
        $objPage->init();
        $layout = new SC_Helper_PageLayout_Ex();
        $layout->sfGetPageLayout($objPage, false, '/products/list.php');
        $objPage->process($vars);
    });
    $r->addRoute(['GET', 'POST'], '/products/list.php', function (array $vars) {
        $objPage = new LC_Page_Products_List_Ex();
        $objPage->skip_load_page_layout = true;
        $objPage->init();
        $layout = new SC_Helper_PageLayout_Ex();
        $layout->sfGetPageLayout($objPage, false, '/products/list.php');
        $objPage->process($vars);
    });
    $r->addRoute(['GET', 'POST'], '/products/detail/{product_id:\d+}', function (array $vars) {
        $objPage = new LC_Page_Products_Detail_Ex();
        $objPage->skip_load_page_layout = true;
        $objPage->init();
        $layout = new SC_Helper_PageLayout_Ex();
        $layout->sfGetPageLayout($objPage, false, '/products/detail.php');
        $_REQUEST['product_id'] = $vars['product_id'];
        $objPage->process();
    });

};
