<?php

require_once __DIR__.'/SC_Helper_Bloc_TestBase.php';

/**
 * SC_Helper_Bloc::getBloc()のテストクラス.
 */
class SC_Helper_Bloc_getBlocTest extends SC_Helper_Bloc_TestBase
{
    public function testGetBlocブロック情報を取得()
    {
        $this->createBlocData([
            'bloc_id' => 1,
            'bloc_name' => 'ヘッダー',
            'filename' => 'header_bloc',
            'tpl_path' => 'header_bloc.tpl',
        ]);

        $this->createBlocFile('header_bloc.tpl', '<div>ヘッダーコンテンツ</div>');

        $result = $this->objHelper->getBloc(1);

        $this->assertIsArray($result);
        $this->assertEquals('ヘッダー', $result['bloc_name']);
        $this->assertEquals('<div>ヘッダーコンテンツ</div>', $result['bloc_html']);
    }

    public function testGetBloc存在しないブロックIDは空配列()
    {
        $result = $this->objHelper->getBloc(9999);

        $this->assertIsArray($result);
        // getRow()が空配列を返すが、bloc_htmlキーが追加されるため厳密には空ではない
        // データベースから取得されていないことを確認
        $this->assertArrayNotHasKey('bloc_id', $result, 'データベースから取得されていない');
    }

    public function testGetBlocファイルが存在しない場合blocHtmlは含まれない()
    {
        $this->createBlocData([
            'bloc_id' => 1,
            'filename' => 'not_exists',
            'tpl_path' => 'not_exists.tpl',
        ]);

        $result = $this->objHelper->getBloc(1);

        $this->assertArrayNotHasKey('bloc_html', $result, 'ファイルが存在しない場合はbloc_htmlキーなし');
    }

    public function testGetBloc異なるデバイスタイプは取得されない()
    {
        $this->createBlocData([
            'bloc_id' => 1,
            'device_type_id' => DEVICE_TYPE_MOBILE,
        ]);

        $result = $this->objHelper->getBloc(1);

        $this->assertIsArray($result);
        $this->assertArrayNotHasKey('bloc_id', $result, 'デバイスタイプが異なるので取得されない');
    }

    public function testGetBlocテンプレートパスが返される()
    {
        $this->createBlocData([
            'bloc_id' => 1,
            'filename' => 'test_bloc',
            'tpl_path' => 'test_bloc.tpl',
        ]);

        $result = $this->objHelper->getBloc(1);

        $this->assertEquals('test_bloc.tpl', $result['tpl_path']);
    }
}
