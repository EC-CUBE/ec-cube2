<?php

class SC_FormParamTest extends Common_TestCase
{
    /**
     * @var SC_FormParam
     */
    protected $objFormParam;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objFormParam = new SC_FormParam_Ex();
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf('SC_FormParam', $this->objFormParam);
    }

    public function testCheckError()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->setParam(['name01' => '']);
        $this->actual = $this->objFormParam->checkError();

        $this->expected = [
            'name01' => '※ お名前(姓)が入力されていません。<br />',
        ];

        $this->verify();
    }

    public function testConvParam()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->setParam(['name01' => 'ｱｲｳｴｵ']);
        $this->objFormParam->convParam();
        $this->actual = $this->objFormParam->getFormParamList();

        $this->expected = [
            'name01' => [
                'keyname' => 'name01',
                'disp_name' => 'お名前(姓)',
                'length' => STEXT_LEN,
                'value' => 'アイウエオ',
            ],
        ];

        $this->verify();
    }

    public function testGetHashArray()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->setParam(['name01' => 'あ']);
        $this->actual = $this->objFormParam->getHashArray();
        $this->expected = ['name01' => 'あ'];

        $this->verify();
    }

    public function testGetDbArray()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'], '', false);
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'], '', true);

        $this->objFormParam->setParam(
            [
                'name01' => 'あ',
                'name02' => 'い',
            ]
        );

        $this->actual = $this->objFormParam->getDbArray();
        $this->expected = ['name02' => 'い'];

        $this->verify();
    }

    public function testGetSwapArray()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'], '', true);
        $this->objFormParam->setParam(
            [
                'name01' => ['test01' => 'ああ'],
                'name02' => ['test02' => 'い'],
            ]
        );
        $this->actual = $this->objFormParam->getSwapArray();

        $this->expected = [
            'test01' => ['name01' => 'ああ'],
            'test02' => ['name02' => 'い'],
        ];
        $this->verify();
    }

    public function testSetParamWithSeq()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->setParam(['あ', 'い'], true);
        $this->actual = $this->objFormParam->getHashArray();

        $this->expected = [
            'name01' => 'あ',
            'name02' => 'い',
        ];
        $this->verify();
    }

    public function testToLower()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->setParam(
            [
                'name01' => 'A',
                'name02' => 'B',
            ]
        );
        $this->objFormParam->toLower('name01');
        $this->actual = $this->objFormParam->getHashArray();

        $this->expected = [
            'name01' => 'a',
            'name02' => 'B',
        ];
        $this->verify();
    }

    public function testRecursionCheck()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'], '', true);
        $this->objFormParam->setParam(
            [
                'name01' => [
                    ['ああ', ''],
                    ['うう', 'ええ'],
                ],
                'name02' => [['ｱｱｱ'], ['ｱｱｱ']],
            ]
        );
        $this->objFormParam->convParam();
        $this->actual = $this->objFormParam->checkError();

        $this->expected = [
            'name01' => [0 => [1 => '※ お名前(姓)が入力されていません。<br />']],
        ];
        $this->verify();

        $this->assertEquals([['アアア'], ['アアア']], $this->objFormParam->getValue('name02'));
    }

    public function testGetKeyList()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'], '', true);

        $this->actual = $this->objFormParam->getKeyList();
        $this->expected = ['name01', 'name02'];
        $this->verify();
    }

    public function testGetCount()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'], '', true);

        $this->actual = $this->objFormParam->getCount();
        $this->expected = 2;
        $this->verify();
    }

    public function testGetValueWithDefault()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);

        $this->objFormParam->setParam(
            [
                'name01' => '',
                'name02' => ['', ''],
            ]
        );
        $this->assertEquals('あ', $this->objFormParam->getValue('name01', 'あ'));
        $this->assertEquals(['あ', 'あ'], $this->objFormParam->getValue('name02', 'あ'));
    }

    public function testTrimParam()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'], '', true);
        $this->objFormParam->setParam(
            [
                'name01' => [
                    [' ああ ', ''],
                    ["\tうう", "\n\nええ"],
                ],
                'name02' => [['あああ'], ['あああ　']],
            ]
        );
        $this->objFormParam->trimParam(true);

        $this->assertEquals([['ああ', ''], ['うう', 'ええ']], $this->objFormParam->getValue('name01'));
        $this->assertEquals([['あああ'], ['あああ']], $this->objFormParam->getValue('name02'));
    }

    public function testTrimParamWithWideSpace()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'], '', true);
        $this->objFormParam->setParam(
            [
                'name01' => [
                    [' ああ ', ''],
                    ["\tうう", "\n\nええ"],
                ],
                'name02' => [['あああ'], ['あああ　']],
            ]
        );
        $this->objFormParam->trimParam(false);

        $this->assertEquals([['ああ', ''], ['うう', 'ええ']], $this->objFormParam->getValue('name01'));
        $this->assertEquals([['あああ'], ['あああ　']], $this->objFormParam->getValue('name02'));
    }

    public function testSetValueWithKeyIsNotFound()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->setValue('aaaa', 'あ');

        $this->assertEquals('', $this->objFormParam->getValue('name01'));
    }

    public function testGetParamSetting()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);

        $this->expected = [
            'name01' => [
                'disp_name' => 'お名前(姓)',
                'keyname' => 'name01',
                'length' => 50,
                'convert' => 'aKV',
                'arrCheck' => [
                    0 => 'EXIST_CHECK',
                    1 => 'NO_SPTAB',
                    2 => 'SPTAB_CHECK',
                    3 => 'MAX_LENGTH_CHECK',
                ],
                'default' => '',
                'input_db' => true,
            ],
        ];
        $this->actual = $this->objFormParam->getParamSetting();
        $this->verify();
    }

    public function testGetParamSettingWithKey()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);

        $this->expected = [
            'disp_name' => 'お名前(姓)',
            'keyname' => 'name01',
            'length' => 50,
            'convert' => 'aKV',
            'arrCheck' => [
                0 => 'EXIST_CHECK',
                1 => 'NO_SPTAB',
                2 => 'SPTAB_CHECK',
                3 => 'MAX_LENGTH_CHECK',
            ],
            'default' => '',
            'input_db' => true,
        ];
        $this->actual = $this->objFormParam->getParamSetting('name01');
        $this->verify();
    }

    public function testGetParamSettingWithKeyAndTargetDefault()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'], 'あ');

        $this->expected = 'あ';
        $this->actual = $this->objFormParam->getParamSetting('name01', 'default');

        $this->verify();
    }

    public function testGetParamSettingWithKeyAndTarget()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'], 'あ');

        $this->expected = 'aKV';
        $this->actual = $this->objFormParam->getParamSetting('name01', 'convert');

        $this->verify();
    }

    public function testGetTitleArray()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'], '', true);

        $this->expected = ['お名前(姓)', 'お名前(名)'];
        $this->actual = $this->objFormParam->getTitleArray();
        $this->verify();
    }

    public function testHtmlDispArray()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', ['NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'], 'あ', false);

        $this->objFormParam->setHtmlDispNameArray();
        $this->expected = [
            'お名前(姓)<span class="red">(※ 必須)</span>',
            'お名前(名) [省略時初期値: あ] [登録・更新不可] ',
        ];
        $this->actual = $this->objFormParam->getHtmlDispNameArray();
        $this->verify();
    }

    public function testSetParamList()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name1', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->addParam('お名前(姓)', 'name2', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->setParamList([['name' => 'あ'], ['name' => 'い']], 'name');

        $this->assertEquals('あ', $this->objFormParam->getValue('name1'));
        $this->assertEquals('い', $this->objFormParam->getValue('name2'));
    }

    public function testSetDbDate()
    {
        $this->objFormParam->addParam('年', 'year', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->addParam('月', 'month', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->addParam('日', 'day', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);

        $this->objFormParam->setDbDate('2019-04-01 00:00:00');

        $this->assertSame('2019', $this->objFormParam->getValue('year'));
        $this->assertSame('4', $this->objFormParam->getValue('month'));
        $this->assertSame('1', $this->objFormParam->getValue('day'));
    }

    public function testSetDbDateWithEmpty()
    {
        $this->objFormParam->addParam('年', 'year', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->addParam('月', 'month', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->addParam('日', 'day', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);

        $this->objFormParam->setDbDate('');

        $this->assertEquals('', $this->objFormParam->getValue('year'));
        $this->assertEquals('', $this->objFormParam->getValue('month'));
        $this->assertEquals('', $this->objFormParam->getValue('day'));
    }

    public function testGetSearchArray()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', ['NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'], 'あ', false);

        $this->objFormParam->setParam(
            [
                'name01' => 'あ',
                'name02' => 'い',
            ]
        );

        $this->expected = [
            'name01' => 'あ',
            'name02' => 'い',
        ];

        $this->actual = $this->objFormParam->getSearchArray('name');
        $this->verify();
    }

    public function testRemoveParam()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', ['NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'], 'あ', false);

        $this->objFormParam->removeParam('name01');

        $this->objFormParam->setParam(
            [
                'name01' => 'あ',
                'name02' => 'い',
            ]
        );

        $this->expected = [
            'name02' => 'い',
        ];

        $this->actual = $this->objFormParam->getSearchArray('name');
        $this->verify();
    }

    public function testOverwiteParam()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK']);

        $this->objFormParam->overwriteParam('name01', 'disp_name', 'Name');
        $this->expected = 'Name';

        $this->actual = $this->objFormParam->getParamSetting('name01', 'disp_name');
        $this->verify();
    }

    public function testOverwiteParamWithDefault()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', ['EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'], 'あ');

        $this->objFormParam->overwriteParam('name01', 'default', 'Name');
        $this->expected = 'Name';

        $this->actual = $this->objFormParam->getParamSetting('name01', 'default');
        $this->verify();
    }

    public function testCheckErrorWithArrCheck()
    {
        $arrPatterns = [
            ['ALNUM_CHECK'],
            ['ALPHA_CHECK'],
            ['CHANGE_LOWER'], // XXX エラーを返さない。
            ['DIR_CHECK'],
            ['DOMAIN_CHECK'],
            ['DOWN_FILE_EXISTS'],
            ['EMAIL_CHAR_CHECK'],
            ['EMAIL_CHECK'],
            ['EXIST_CHECK', ''],
            ['FILE_EXISTS'],
            ['FILE_NAME_CHECK_BY_NOUPLOAD'],
            ['FILE_NAME_CHECK'], // XXX エラーを起こすには、$_FILES を書き換える必要がある。
            ['FIND_FILE'],
            ['GRAPH_CHECK', "\x13"], // XXX GRAPH_CHECK は \a(\x0a) を通してしまう。多分、望ましくない。
            ['IP_CHECK'],
            ['KANA_CHECK'],
            ['KANABLANK_CHECK'],
            ['MAX_LENGTH_CHECK', str_repeat('a', 51)],
            ['MIN_LENGTH_CHECK'],
            ['MOBILE_EMAIL_CHECK'],
            ['NO_SPTAB', 'a a'],
            ['NUM_CHECK'],
            ['NUM_COUNT_CHECK'],
            ['NUM_POINT_CHECK'],
            ['PREF_CHECK'],
            ['SELECT_CHECK', ''],
            ['SPTAB_CHECK'], // XXX SC_FormParam::getValue() の仕様上、エラーを起こせない。default もセットすればできなくもないか。
            ['URL_CHECK'],
            ['XXXXXXXXXX'],
            ['ZERO_CHECK', '0'],
            ['ZERO_START', '01'],
        ];

        foreach ($arrPatterns as $arrPattern) {
            $check = $arrPattern[0];
            $key = $arrPattern[0].'_key';
            $value = $arrPattern[1] ?? "\a";
            $this->objFormParam->addParam($check, $key, STEXT_LEN, 'aKV', [$check]);
            $this->objFormParam->setValue($key, $value);
        }

        $this->expected = [
            'ALNUM_CHECK_key' => '※ ALNUM_CHECKは英数字で入力してください。',
            'ALPHA_CHECK_key' => '※ ALPHA_CHECKは半角英字で入力してください。',
            'DIR_CHECK_key' => '※ 指定したDIR_CHECKは存在しません。',
            'DOMAIN_CHECK_key' => '※ DOMAIN_CHECKの形式が不正です。',
            'DOWN_FILE_EXISTS_key' => '※ DOWN_FILE_EXISTSのファイルが存在しません。',
            'EMAIL_CHAR_CHECK_key' => '※ EMAIL_CHAR_CHECKに使用する文字を正しく入力してください。',
            'EMAIL_CHECK_key' => '※ EMAIL_CHECKの形式が不正です。',
            'EXIST_CHECK_key' => '※ EXIST_CHECKが入力されていません。',
            'FILE_EXISTS_key' => '※ FILE_EXISTSのファイルが存在しません。',
            'FILE_NAME_CHECK_BY_NOUPLOAD_key' => '※ FILE_NAME_CHECK_BY_NOUPLOADのファイル名には、英数字、記号（_ - .）のみを入力して下さい。',
            'FIND_FILE_key' => '※ FIND_FILEが見つかりません。',
            'GRAPH_CHECK_key' => '※ GRAPH_CHECKは英数記号で入力してください。',
            'IP_CHECK_key' => '※ IP_CHECKに正しい形式のIPアドレスを入力してください。',
            'KANA_CHECK_key' => '※ KANA_CHECKはカタカナで入力してください。',
            'KANABLANK_CHECK_key' => '※ KANABLANK_CHECKはカタカナで入力してください。',
            'MAX_LENGTH_CHECK_key' => '※ MAX_LENGTH_CHECKは50字以下で入力してください。',
            'MIN_LENGTH_CHECK_key' => '※ MIN_LENGTH_CHECKは50字以上で入力してください。',
            'MOBILE_EMAIL_CHECK_key' => '※ MOBILE_EMAIL_CHECKは携帯電話のものではありません。',
            'NO_SPTAB_key' => '※ NO_SPTABにスペース、タブ、改行は含めないで下さい。',
            'NUM_CHECK_key' => '※ NUM_CHECKは数字で入力してください。',
            'NUM_COUNT_CHECK_key' => '※ NUM_COUNT_CHECKは50桁で入力して下さい。',
            'NUM_POINT_CHECK_key' => '※ NUM_POINT_CHECKは数字で入力してください。',
            'PREF_CHECK_key' => '※ PREF_CHECKが不正な値です。',
            'SELECT_CHECK_key' => '※ SELECT_CHECKが選択されていません。',
            'URL_CHECK_key' => '※ URL_CHECKを正しく入力してください。',
            'XXXXXXXXXX_key' => '※※　エラーチェック形式(XXXXXXXXXX)には対応していません　※※ ',
            'ZERO_CHECK_key' => '※ ZERO_CHECKは1以上を入力してください。',
            'ZERO_START_key' => '※ ZERO_STARTに0で始まる数値が入力されています。',
        ];
        $this->actual = @$this->objFormParam->checkError(false);
        $this->verify();
    }

    public function testConstructorHookPoint()
    {
        $phpunit = $this;
        $actualInstance = null;

        $objPlugin = SC_Helper_Plugin_Ex::getSingletonInstance();
        $objPlugin->arrRegistedPluginActions['SC_FormParam_construct'][] = [['function' => function ($class, $objFormParam) use ($phpunit, &$actualInstance) {
            $phpunit->assertEquals('SC_FormParamTest', $class, 'backtrace から取得した呼び出し元のクラスが渡ってくるはず');
            $phpunit->assertInstanceOf('SC_FormParam_Ex', $objFormParam);
            $actualInstance = $objFormParam;
        }]];

        $objFormParam = new SC_FormParam_Ex();
        $this->assertSame($objFormParam, $actualInstance, 'フックポイントのコールバック関数で同一インスタンスが取得できるはず');
    }
}
