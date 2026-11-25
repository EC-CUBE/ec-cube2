<?php

declare(strict_types=1);

use Eccube2\Migration\Migration;

/**
 * #1065: dtb_templates の主キーを (template_code, device_type_id) へ変更
 *
 * 2.25.0 以前にインストールした MySQL / PostgreSQL 環境が対象。
 * SQLite3 は当初から複合主キーのため変更不要。
 */
class Version20260928000001_AlterTemplatesPrimaryKey extends Migration
{
    public function up(): void
    {
        if ($this->platform->getName() === 'sqlite3') {
            return;
        }

        $this->sql(
            'ALTER TABLE dtb_templates DROP PRIMARY KEY, ADD PRIMARY KEY (template_code(255), device_type_id)',
            'ALTER TABLE dtb_templates DROP CONSTRAINT dtb_templates_pkey, ADD PRIMARY KEY (template_code, device_type_id)'
        );
    }

    public function down(): void
    {
        if ($this->platform->getName() === 'sqlite3') {
            return;
        }

        $this->sql(
            'ALTER TABLE dtb_templates DROP PRIMARY KEY, ADD PRIMARY KEY (template_code(255))',
            'ALTER TABLE dtb_templates DROP CONSTRAINT dtb_templates_pkey, ADD PRIMARY KEY (template_code)'
        );
    }
}
