<?php

/**
 * @group classloader
 */
class LoadClassFileChangeTest extends Common_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->createPlugin();
    }

    protected function tearDown()
    {
        $plugins = ['AutoloadingPlugin'];
        foreach ($plugins as $plugin) {
            $dir = PLUGIN_UPLOAD_REALDIR.$plugin;
            if (!file_exists($dir)) break;
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $file) {
                /** @var SplFileInfo $file */
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getRealPath());
            }
            rmdir($dir);
        }
        parent::tearDown();
    }

    /**
     * loadClassFileChange で拡張したクラスのテストケース.
     */
    public function testLoadExtendedClass()
    {
        $objProduct = new SC_Product_Ex();
        $this->assertTrue(constant('SC_Product_Ex::AUTOLOAD'), 'loadClassFileChange で拡張したクラス定数にアクセスできる');

        $refclass = new ReflectionClass($objProduct);
        $this->assertTrue($refclass->hasProperty('autoloaded'), 'loadClassFileChange で拡張したプロパティが存在する');

        $refProp = $refclass->getProperty('autoloaded');
        $this->assertTrue($refProp->getValue($objProduct), 'loadClassFileChange で拡張したプロパティにアクセスできる');

        $this->assertTrue($refclass->hasMethod('isExtended'), 'loadClassFileChange で拡張したメソッドが存在する');
        $refMethod = $refclass->getMethod('isExtended');
        $this->assertTrue($refMethod->invoke($objProduct), 'loadClassFileChange で拡張したメソッドにアクセスできる');
    }

    /**
     * ダミーのプラグインをインストールする.
     */
    private function createPlugin()
    {
        $realdir = PLUGIN_UPLOAD_REALDIR;
        $plugin_info = <<< __EOS__
<?php
class plugin_info {
    static \$PLUGIN_CODE        = 'AutoloadingPlugin';
    static \$PLUGIN_NAME        = 'AutoloadingPlugin';
    static \$CLASS_NAME         = 'AutoloadingPlugin';
    static \$PLUGIN_VERSION     = '0.0.0';
    static \$COMPLIANT_VERSION  = '2.17';
    static \$AUTHOR             = 'dummy';
    static \$DESCRIPTION        = 'dummy';
    static \$HOOK_POINTS        = 'loadClassFileChange';
}
__EOS__;
       $autoloadingPlugin = <<< __EOS__
<?php
class AutoloadingPlugin extends SC_Plugin_Base
{
    public function loadClassFileChange(&\$classname, &\$classpath) {
        if (\$classname === "SC_Product_Ex") {
            \$classpath = "${realdir}/AutoloadingPlugin/Autoloading_SC_Product.php";
            \$classname = "Autoloading_SC_Product";
        }
    }
}
__EOS__;
       $Autoloading_SC_Product = <<< __EOS__
<?php
class Autoloading_SC_Product extends SC_Product
{
    const AUTOLOAD = true;
    public \$autoloaded = true;
    public function isExtended() {
        return true;
    }
}
__EOS__;

        $files = [
            'plugin_info' => $plugin_info,
            'AutoloadingPlugin' => $autoloadingPlugin,
            'Autoloading_SC_Product' => $Autoloading_SC_Product
        ];

        $dir = PLUGIN_UPLOAD_REALDIR.'AutoloadingPlugin';
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        foreach ($files as $name => $content) {
            file_put_contents($dir.'/'.$name.'.php', $content);
        }

        $plugin_id = $this->objQuery->nextVal('dtb_plugin_plugin_id');
        $pluginValues = [
            'plugin_id' => $plugin_id,
            'plugin_name' => 'AutoloadingPlugin',
            'plugin_code' => 'AutoloadingPlugin',
            'class_name' => 'AutoloadingPlugin',
            'plugin_version' => '0.0.0',
            'compliant_version' => '2.17',
            'enable' => 1,
            'create_date' => 'CURRENT_TIMESTAMP',
            'update_date' => 'CURRENT_TIMESTAMP'
        ];
        $this->objQuery->insert('dtb_plugin', $pluginValues);

        $plugin_hookpoint_id = $this->objQuery->nextVal('dtb_plugin_hookpoint_plugin_hookpoint_id');
        $hookpointValues = [
            'plugin_hookpoint_id' => $plugin_hookpoint_id,
            'plugin_id' => $plugin_id,
            'hook_point' => 'loadClassFileChange',
            'callback' => 'loadClassFileChange',
            'update_date' => 'CURRENT_TIMESTAMP'
        ];
        $this->objQuery->insert('dtb_plugin_hookpoint', $hookpointValues);
    }
}
