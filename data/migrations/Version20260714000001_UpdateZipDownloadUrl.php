<?php

declare(strict_types=1);

use Eccube2\Migration\Migration;

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
 * NOTE: 本マイグレーションは mtb_constants(DB) を更新するのみ。定数キャッシュ
 *       (data/cache/n.php) には即時反映されないため、反映には管理画面
 *       「システム設定 > パラメータ設定」での保存操作（キャッシュ再生成）が必要。
 */
class Version20260714000001_UpdateZipDownloadUrl extends Migration
{
    private const OLD_URL = '"https://www.post.japanpost.jp/zipcode/dl/kogaki/zip/ken_all.zip"';
    private const NEW_URL = '"https://www.post.japanpost.jp/service/search/zipcode/download/kogaki/zip/ken_all.zip"';

    public function up(): void
    {
        $this->sql(
            'UPDATE mtb_constants SET name = ? WHERE id = ? AND name = ?',
            [self::NEW_URL, 'ZIP_DOWNLOAD_URL', self::OLD_URL]
        );
    }

    public function down(): void
    {
        $this->sql(
            'UPDATE mtb_constants SET name = ? WHERE id = ? AND name = ?',
            [self::OLD_URL, 'ZIP_DOWNLOAD_URL', self::NEW_URL]
        );
    }
}
