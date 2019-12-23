<?php

/**
 * @group classloader
 */
class LoadClassFileChangeCustomDirTest extends Common_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $plugin_id = $this->objQuery->nextVal('dtb_plugin_plugin_id');
        $pluginValues = [
            'plugin_id' => $plugin_id,
            'plugin_name' => 'FixturePlugin',
            'plugin_code' => 'FixturePlugin',
            'class_name' => 'FixturePlugin',
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

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLOading()
    {
        //  __DIR__.'/../fixtures/plugin/ に配置したプラグインをオートロード対象にする
        spl_autoload_register(function ($class){
            SC_ClassAutoloader_Ex::autoload($class, __DIR__.'/../fixtures/plugin/');
        }, true, true);

        $objCustomer = new SC_Customer_Ex();
        $this->assertInstanceOf('Fixture_SC_Customer', $objCustomer);
        $this->assertEquals('loading', $objCustomer->getValue('loading'), __DIR__.'/../fixtures/plugin/Fixture_SC_Customer がロードされる');
    }
}
