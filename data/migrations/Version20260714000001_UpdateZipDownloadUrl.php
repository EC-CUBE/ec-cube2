<?php

declare(strict_types=1);

use Eccube2\Migration\Migration;
use Eccube2\Util\ParameterUtil;

/**
 * 郵便番号CSV(ken_all.zip)のダウンロードURL変更に伴う ZIP_DOWNLOAD_URL の更新.
 *
 * 日本郵便の配布URLが変更され、旧URLが404を返すようになったため、
 * mtb_constants に登録済みの ZIP_DOWNLOAD_URL を新URLへ更新する。
 *
 * 旧URL: https://www.post.japanpost.jp/zipcode/dl/kogaki/zip/ken_all.zip
 * 新URL: https://www.post.japanpost.jp/service/search/zipcode/download/kogaki/zip/ken_all.zip
 *
 * NOTE: 管理者が管理画面「システム設定 > パラメータ設定」で独自のURLへ
 *       変更済みの環境を上書きしないよう、値が旧URLの場合のみ更新する。
 */

class Version20260714000001_UpdateZipDownloadUrl extends Migration
{
    private const KEY = 'ZIP_DOWNLOAD_URL';
    private const OLD_URL = '"https://www.post.japanpost.jp/zipcode/dl/kogaki/zip/ken_all.zip"';
    private const NEW_URL = '"https://www.post.japanpost.jp/service/search/zipcode/download/kogaki/zip/ken_all.zip"';

    public function up(): void
    {
        $this->replace(self::OLD_URL, self::NEW_URL);
    }

    public function down(): void
    {
        $this->replace(self::NEW_URL, self::OLD_URL);
    }

    private function replace(string $from, string $to): void
    {
        $parameter = new ParameterUtil();

        // 管理画面で独自URLに変更済みの場合は上書きしない。
        if ($parameter->get(self::KEY) !== $from) {
            return;
        }

        $parameter->set(self::KEY, $to);
    }
}
