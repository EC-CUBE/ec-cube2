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
 * パスワードリセットトークンクリーンアップスクリプト
 *
 * 期限切れのパスワードリセットトークンをクリーンアップします。
 *
 * 使用方法:
 *   php cleanup_password_reset.php
 *
 * cron設定例（毎日深夜2時に実行）:
 *   0 2 * * * cd /path/to/ec-cube/html && php cleanup_password_reset.php >> /path/to/logs/cleanup.log 2>&1
 */

require_once __DIR__.'/define.php';
require_once HTML_REALDIR.'../data/require_base.php';

$objBatch = new SC_Batch_CleanupPasswordReset_Ex();
$objBatch->execute();
