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
 * APIの基本クラス
 *
 * @author EC-CUBE CO.,LTD.
 *
 * @version $Id$
 */
class API_CartCreate extends SC_Api_Abstract_Ex
{
    protected $operation_name = 'CartCreate';
    protected $operation_description = 'カートの新規作成';
    protected $default_auth_types = self::API_AUTH_TYPE_SESSION_TOKEN;
    protected $default_enable = '0';
    protected $default_is_log = '0';
    protected $default_sub_data = '';

    public function doAction($arrParam)
    {
        $this->arrResponse = [
            'Version' => ECCUBE_VERSION,
        ];

        return true;
    }

    public function getRequestValidate()
    {
        return;
    }

    protected function lfInitParam(&$objFormParam)
    {
    }

    public function getResponseGroupName()
    {
        return 'VersionResponse';
    }
}
