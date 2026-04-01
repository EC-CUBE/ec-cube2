<?php

declare(strict_types=1);

use Eccube2\Migration\Migration;
use Eccube2\Migration\Schema\Table;

/**
 * Issue #819: メルマガワンクリック登録解除トークン管理テーブル
 */
class Version20260401000001_CreateMailmagaUnsubscribeTokenTable extends Migration
{
    public function up(): void
    {
        $this->create('dtb_mailmaga_unsubscribe_token', function (Table $table) {
            $table->serial();
            $table->integer('customer_id')->notNull();
            $table->integer('send_id')->notNull();
            $table->varchar('token', 64)->notNull();
            $table->varchar('email', 255)->notNull();
            $table->smallint('used_flg')->notNull()->default(0);
            $table->timestamp('used_date')->nullable();
            $table->timestamp('expire_date')->notNull();
            $table->timestamp('create_date')->notNull()->default('CURRENT_TIMESTAMP');

            $table->unique(['token']);
            $table->index(['customer_id']);
            $table->index(['send_id']);
            $table->index(['expire_date']);
        });
    }

    public function down(): void
    {
        $this->drop('dtb_mailmaga_unsubscribe_token');
    }
}
