<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * メルマガ登録解除トークンクリーンアップスクリプト
 *
 * 期限切れ・使用済みのメルマガ登録解除トークンをクリーンアップします。
 *
 * 使用方法:
 *   php cleanup_mailmaga_token.php
 *
 * cron設定例（毎日深夜3時に実行）:
 *   0 3 * * * cd /path/to/ec-cube/html && php cleanup_mailmaga_token.php >> /path/to/logs/cleanup.log 2>&1
 */

require_once __DIR__.'/define.php';
require_once HTML_REALDIR.'../data/require_base.php';

$objBatch = new SC_Batch_CleanupMailmagaToken_Ex();
$objBatch->execute();
