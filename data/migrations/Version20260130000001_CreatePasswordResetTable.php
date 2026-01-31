<?php

declare(strict_types=1);

use Eccube2\Migration\Migration;
use Eccube2\Migration\Schema\Table;

/**
 * Issue #368: パスワード再発行トークン管理テーブル
 */
class Version20260130000001_CreatePasswordResetTable extends Migration
{
    public function up(): void
    {
        $this->create('dtb_password_reset', function (Table $table) {
            $table->serial();
            $table->text('email')->notNull();
            $table->text('token_hash')->notNull();
            $table->integer('customer_id')->nullable();
            $table->smallint('status')->notNull()->default(0);
            $table->timestamp('expire_date')->notNull();
            $table->text('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('used_date')->nullable();
            $table->timestamp('create_date')->notNull()->default('CURRENT_TIMESTAMP');
            $table->timestamp('update_date')->notNull()->default('CURRENT_TIMESTAMP');

            $table->index(['token_hash']);
            $table->index(['email', 'create_date']);
            $table->index(['expire_date', 'status']);
        });
    }

    public function down(): void
    {
        $this->drop('dtb_password_reset');
    }
}
