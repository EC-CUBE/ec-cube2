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
 * 携帯端末の情報を扱うクラス
 *
 * 対象とする携帯端末は $_SERVER から決定する。
 * 全てのメソッドはクラスメソッド。
 * @deprecated
 */
class SC_MobileUserAgent
{
    /**
     * 携帯端末のキャリアを表す文字列を取得する。
     *
     * 文字列は docomo, ezweb, softbank のいずれか。
     *
     * @return string|false 携帯端末のキャリアを表す文字列を返す。
     *                      携帯端末ではない場合は false を返す。
     * @deprecated
     */
    public function getCarrier()
    {
        trigger_error('Net_UserAgent_Mobile is deprecated', E_USER_DEPRECATED);
        return false;
    }

    /**
     * 勝手サイトで利用可能な携帯端末/利用者のIDを取得する。
     *
     * 各キャリアで使用するIDの種類:
     * + docomo   ... UTN
     * + ezweb    ... EZ番号
     * + softbank ... 端末シリアル番号
     *
     * @deprecated
     * @return string|false 取得したIDを返す。取得できなかった場合は false を返す。
     */
    public function getId()
    {
        trigger_error('Net_UserAgent_Mobile is deprecated', E_USER_DEPRECATED);
        return false;
    }

    /**
     * 携帯端末の機種を表す文字列を取得する。
     * 携帯端末ではない場合はユーザーエージェントの名前を取得する。(例: 'Mozilla')
     *
     * @return string 携帯端末のモデルを表す文字列を返す。
     */
    public function getModel()
    {
        trigger_error('Net_UserAgent_Mobile is deprecated', E_USER_DEPRECATED);
        return false;
    }

    /**
     * EC-CUBE がサポートする携帯端末かどうかを判別する。
     *
     * 以下の条件に該当する場合は, false を返す.
     *
     * - 携帯端末だと判別されたが, ユーザーエージェントが解析不能な場合
     * - J-PHONE C4型(パケット非対応)
     * - EzWeb で WAP2 以外の端末
     * - DoCoMo 501i, 502i, 209i, 210i, SH821i, N821i, P821i, P651ps, R691i, F671i, SH251i, SH251iS
     *
     * @return boolean サポートしている場合は true、それ以外の場合は false を返す。
     */
    public function isSupported()
    {
        trigger_error('Net_UserAgent_Mobile is deprecated', E_USER_DEPRECATED);
        return false;
    }

    /**
     * EC-CUBE がサポートする携帯キャリアかどうかを判別する。
     *
     * ※一部モジュールで使用。ただし、本メソッドは将来的に削除しますので新規ご利用は控えてください。
     *
     * @return boolean サポートしている場合は true、それ以外の場合は false を返す。
     */
    public function isMobile()
    {
        trigger_error('Net_UserAgent_Mobile is deprecated', E_USER_DEPRECATED);
        return false;
    }
}
