<?php

class SC_FromParamTest extends Common_TestCase
{
    /**
     * @var SC_FormParam
     */
    protected $objFormParam;

    protected function setUp()
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
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->setParam(['name01' => '']);
        $this->actual = $this->objFormParam->checkError();

        $this->expected = [
            'name01' => '※ お名前(姓)が入力されていません。<br />'
        ];

        $this->verify();
    }

    public function testConvParam()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->setParam(['name01' => 'ｱｲｳｴｵ']);
        $this->objFormParam->convParam();
        $this->actual = $this->objFormParam->getFormParamList();

        $this->expected = [
            'name01' => [
                'keyname' => 'name01',
                'disp_name' => 'お名前(姓)',
                'length' => STEXT_LEN,
                'value' => 'アイウエオ'
            ]
        ];

        $this->verify();
    }

    public function testGetHashArray()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->setParam(['name01' => 'あ']);
        $this->actual = $this->objFormParam->getHashArray();
        $this->expected = ['name01' => 'あ'];

        $this->verify();
    }

    public function testGetDbArray()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'), '', false);
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'), '', true);

        $this->objFormParam->setParam(
            [
                'name01' => 'あ',
                'name02' => 'い'
            ]
        );

        $this->actual = $this->objFormParam->getDbArray();
        $this->expected = ['name02' => 'い'];

        $this->verify();
    }

    public function testGetSwapArray()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'), '', true);
        $this->objFormParam->setParam(
            [
                'name01' => ['test01' => 'ああ'],
                'name02' => ['test02' => 'い']
            ]
        );
        $this->actual = $this->objFormParam->getSwapArray();

        $this->expected = [
            'test01' => ['name01' => 'ああ'],
            'test02' => ['name02' => 'い']
        ];
        $this->verify();
    }

    public function testSetParamWithSeq()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->setParam(['あ', 'い'], true);
        $this->actual = $this->objFormParam->getHashArray();

        $this->expected = [
            'name01' => 'あ',
            'name02' => 'い'
        ];
        $this->verify();
    }

    public function testToLower()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->setParam(
            [
                'name01' => 'A',
                'name02' => 'B'
            ]
        );
        $this->objFormParam->toLower('name01');
        $this->actual = $this->objFormParam->getHashArray();

        $this->expected = [
            'name01' => 'a',
            'name02' => 'B'
        ];
        $this->verify();
    }

    public function testRecursionCheck()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'), '', true);
        $this->objFormParam->setParam(
            [
                'name01' => [
                    ['ああ', ''],
                    ['うう', 'ええ']
                ],
                'name02' => [['ｱｱｱ'], ['ｱｱｱ']]
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
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'), '', true);

        $this->actual = $this->objFormParam->getKeyList();
        $this->expected = ['name01', 'name02'];
        $this->verify();
    }

    public function testGetCount()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'), '', true);

        $this->actual = $this->objFormParam->getCount();
        $this->expected = 2;
        $this->verify();
    }

    public function testGetValueWithDefault()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));

        $this->objFormParam->setParam(
            [
                'name01' => '',
                'name02' => ['', '']
            ]
        );
        $this->assertEquals('あ', $this->objFormParam->getValue('name01', 'あ'));
        $this->assertEquals(['あ', 'あ'], $this->objFormParam->getValue('name02', 'あ'));
    }

    public function testTrimParam()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'), '', true);
        $this->objFormParam->setParam(
            [
                'name01' => [
                    [' ああ ', ''],
                    ["\tうう", "\n\nええ"]
                ],
                'name02' => [['あああ'], ['あああ　']]
            ]
        );
        $this->objFormParam->trimParam(true);

        $this->assertEquals([['ああ', ''], ['うう', 'ええ']], $this->objFormParam->getValue('name01'));
        $this->assertEquals([['あああ'], ['あああ']], $this->objFormParam->getValue('name02'));
    }

    public function testTrimParamWithWideSpace()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'), '', true);
        $this->objFormParam->setParam(
            [
                'name01' => [
                    [' ああ ', ''],
                    ["\tうう", "\n\nええ"]
                ],
                'name02' => [['あああ'], ['あああ　']]
            ]
        );
        $this->objFormParam->trimParam(false);

        $this->assertEquals([['ああ', ''], ['うう', 'ええ']], $this->objFormParam->getValue('name01'));
        $this->assertEquals([['あああ'], ['あああ　']], $this->objFormParam->getValue('name02'));
    }

    public function testSetValueWithKeyIsNotFound()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->setValue('aaaa', 'あ');

        $this->assertEquals('', $this->objFormParam->getValue('name01'));
    }

    public function testGetParamSetting()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));

        $this->expected = [
            'name01' =>
            [
                'disp_name' => 'お名前(姓)',
                'keyname' => 'name01',
                'length' => 50,
                'convert' => 'aKV',
                'arrCheck' =>
                [
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
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));

        $this->expected = [
            'disp_name' => 'お名前(姓)',
            'keyname' => 'name01',
            'length' => 50,
            'convert' => 'aKV',
            'arrCheck' =>
            [
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
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'), 'あ');

        $this->expected = 'あ';
        $this->actual = $this->objFormParam->getParamSetting('name01', 'default');

        $this->verify();
    }

    public function testGetParamSettingWithKeyAndTarget()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'), 'あ');

        $this->expected = 'aKV';
        $this->actual = $this->objFormParam->getParamSetting('name01', 'convert');

        $this->verify();
    }

    public function testGetTitleArray()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'), '', true);

        $this->expected = ['お名前(姓)', 'お名前(名)'];
        $this->actual = $this->objFormParam->getTitleArray();
        $this->verify();
    }

    public function testHtmlDispArray()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', array('NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'), 'あ', false);

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
        $this->objFormParam->addParam('お名前(姓)', 'name1', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->addParam('お名前(姓)', 'name2', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->setParamList([['name' =>'あ'], ['name' => 'い']], 'name');

        $this->assertEquals('あ', $this->objFormParam->getValue('name1'));
        $this->assertEquals('い', $this->objFormParam->getValue('name2'));
    }

    public function testSetDbDate()
    {
        $this->objFormParam->addParam('年', 'year', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->addParam('月', 'month', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->addParam('日', 'day', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));

        $this->objFormParam->setDbDate('2019-04-01');

        $this->assertEquals('2019', $this->objFormParam->getValue('year'));
        $this->assertEquals('04', $this->objFormParam->getValue('month'));
        $this->assertEquals('01', $this->objFormParam->getValue('day'));
    }

    public function testSetDbDateWithEmpty()
    {
        $this->objFormParam->addParam('年', 'year', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->addParam('月', 'month', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->addParam('日', 'day', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));

        $this->objFormParam->setDbDate('');

        $this->assertEquals('', $this->objFormParam->getValue('year'));
        $this->assertEquals('', $this->objFormParam->getValue('month'));
        $this->assertEquals('', $this->objFormParam->getValue('day'));
    }

    public function testGetSearchArray()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', array('NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'), 'あ', false);

        $this->objFormParam->setParam(
            [
                'name01' => 'あ',
                'name02' => 'い'
            ]
        );

        $this->expected = [
            'name01' => 'あ',
            'name02' => 'い'
        ];

        $this->actual = $this->objFormParam->getSearchArray('name');
        $this->verify();
    }

    public function testRemoveParam()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $this->objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', array('NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'), 'あ', false);

        $this->objFormParam->removeParam('name01');

        $this->objFormParam->setParam(
            [
                'name01' => 'あ',
                'name02' => 'い'
            ]
        );

        $this->expected = [
            'name02' => 'い'
        ];

        $this->actual = $this->objFormParam->getSearchArray('name');
        $this->verify();
    }

    public function testOverwiteParam()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));

        $this->objFormParam->overwriteParam('name01', 'disp_name', 'Name');
        $this->expected = 'Name';

        $this->actual = $this->objFormParam->getParamSetting('name01', 'disp_name');
        $this->verify();
    }

    public function testOverwiteParamWithDefault()
    {
        $this->objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'), 'あ');

        $this->objFormParam->overwriteParam('name01', 'default', 'Name');
        $this->expected = 'Name';

        $this->actual = $this->objFormParam->getParamSetting('name01', 'default');
        $this->verify();
    }

    public function testCheckErrorWithArrCHeck()
    {
        $arrCheck = [
            'EXIST_CHECK',
            'NUM_CHECK',
            'EMAIL_CHECK',
            'EMAIL_CHAR_CHECK',
            'ALNUM_CHECK',
            'GRAPH_CHECK',
            'KANA_CHECK',
            'URL_CHECK',
            'IP_CHECK',
            'SPTAB_CHECK',
            'ZERO_CHECK',
            'ALPHA_CHECK',
            'ZERO_START',
            'FIND_FILE',
            'NO_SPTAB',
            'DIR_CHECK',
            'DOMAIN_CHECK',
            'FILE_NAME_CHECK',
            'MOBILE_EMAIL_CHECK',
            'MAX_LENGTH_CHECK',
            'MIN_LENGTH_CHECK',
            'NUM_COUNT_CHECK',
            'KANABLANK_CHECK',
            'SELECT_CHECK',
            'FILE_NAME_CHECK_BY_NOUPLOAD',
            'NUM_POINT_CHECK',
            'PREF_CHECK',
            'CHANGE_LOWER',
            'FILE_EXISTS',
            'DOWN_FILE_EXISTS',
            'XXXXXXXXXX'
        ];

        foreach ($arrCheck as $key => $check) {
            $this->objFormParam->addParam($check, $check.'_key', STEXT_LEN, 'aKV', [$check]);

            $this->objFormParam->setValue($check.'_key', $key);
        }

        $this->expected = [
            'EMAIL_CHECK_key' => '※ EMAIL_CHECKの形式が不正です。',
            'ALNUM_CHECK_key' => '※ ALNUM_CHECKは英数字で入力してください。',
            'KANA_CHECK_key' => '※ KANA_CHECKはカタカナで入力してください。',
            'URL_CHECK_key' => '※ URL_CHECKを正しく入力してください。',
            'IP_CHECK_key' => '※ IP_CHECKに正しい形式のIPアドレスを入力してください。',
            'ALPHA_CHECK_key' => '※ ALPHA_CHECKは半角英字で入力してください。',
            'FIND_FILE_key' => '※ 50/13が見つかりません。',
            'DIR_CHECK_key' => '※ 指定したDIR_CHECKは存在しません。',
            'DOMAIN_CHECK_key' => '※ DOMAIN_CHECKの形式が不正です。',
            'MOBILE_EMAIL_CHECK_key' => '※ MOBILE_EMAIL_CHECKは携帯電話のものではありません。',
            'MIN_LENGTH_CHECK_key' => '※ MIN_LENGTH_CHECKは50字以上で入力してください。',
            'KANABLANK_CHECK_key' => '※ KANABLANK_CHECKはカタカナで入力してください。',
            'FILE_EXISTS_key' => '※ FILE_EXISTSのファイルが存在しません。',
            'DOWN_FILE_EXISTS_key' => '※ DOWN_FILE_EXISTSのファイルが存在しません。',
            'XXXXXXXXXX_key' => '※※　エラーチェック形式(XXXXXXXXXX)には対応していません　※※ ',
            'NUM_COUNT_CHECK_key' => '※ NUM_COUNT_CHECKは50桁で入力して下さい。'
        ];
        $this->actual = @$this->objFormParam->checkError(false);
        $this->verify();
    }
        
}
