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
 * テキスト/HTML　メール送信
 */
class SC_SendMail
{
    /** 送信先のメールアドレス */
    public string $to = '';
    public string $to_name = '';
    /** 題名 */
    public string $subject = '';
    /** 本文 */
    public string $body = '';
    public string $cc = '';
    public string $cc_name = '';
    public string $bcc = '';
    public string $bcc_name = '';
    public string $replay_to = '';
    public string $return_path = '';
    /** @var \Mail|\PEAR_Error */
    public $objMail;
    public array $arrRecip = [];
    public string $backend = MAIL_BACKEND;
    public string $host = SMTP_HOST;
    /**
     * ポート番号
     *
     * 本質的には数値だが、処理を簡素にするため文字列も可能とする。
     *
     * @var int|string
     */
    public $port = SMTP_PORT;
    public string $from = '';
    public string $from_name = '';
    public string $reply_to = '';
    protected array $customHeaders = [];
    protected string $charset = 'ISO-2022-JP';

    /**
     * 本文に Base64 エンコードを使用するか。
     *
     * null は自動判定。
     */
    protected ?bool $useBase64ForBody = null;

    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct()
    {
        // PEAR::Mailを使ってメール送信オブジェクト作成
        $this->objMail = \Mail::factory(
            $this->backend,
            $this->getBackendParams($this->backend)
        );
        if (\PEAR::isError($this->objMail)) {
            // XXX 環境によっては文字エンコードに差異がないか些か心配
            trigger_error($this->objMail->getMessage(), E_USER_ERROR);
        }
    }

    /**
     * 送信先の設定
     *
     * @param string $key
     */
    public function setRecip($key, $recipient)
    {
        $this->arrRecip[$key] = $recipient;
    }

    /**
     * 宛先の設定
     */
    public function setTo($to, $to_name = '')
    {
        if ($to != '') {
            $this->to = $to;
            $this->to_name = $to_name;
            $this->setRecip('To', $to);
        }
    }

    public function getToWithEncodedName()
    {
        return $this->getNameAddress($this->to_name, $this->to);
    }

    /**
     * 送信元の設定
     */
    public function setFrom($from, $from_name = '')
    {
        $this->from = $from;
        $this->from_name = $from_name;
    }

    public function getFromWithEncodedName()
    {
        return $this->getNameAddress($this->from_name, $this->from);
    }

    /**
     * CCの設定
     *
     * @param string $cc
     */
    public function setCc($cc, $cc_name = '')
    {
        if ($cc != '') {
            $this->cc = $cc;
            $this->cc_name = $cc_name;
            $this->setRecip('Cc', $cc);
        }
    }

    public function getCcWithEncodedName()
    {
        return $this->getNameAddress($this->cc_name, $this->cc);
    }

    /**
     * BCCの設定
     *
     * @param string $bcc
     */
    public function setBcc($bcc, $bcc_name = '')
    {
        if ($bcc != '') {
            $this->bcc = $bcc;
            $this->bcc_name = $bcc_name;
            $this->setRecip('Bcc', $bcc);
        }
    }

    public function getBccWithEncodedName()
    {
        return $this->getNameAddress($this->bcc_name, $this->bcc);
    }

    /**
     * Reply-Toの設定
     *
     * @param string $reply_to
     */
    public function setReplyTo($reply_to)
    {
        if ($reply_to != '') {
            $this->reply_to = $reply_to;
        }
    }

    /**
     * Return-Pathの設定
     */
    public function setReturnPath($return_path)
    {
        $this->return_path = $return_path;
    }

    /**
     * カスタムヘッダーを追加
     *
     * @param string $name  ヘッダー名
     * @param string $value ヘッダー値
     */
    public function addCustomHeader($name, $value)
    {
        // ヘッダー名の形式チェック（RFC 7230 token）
        if (!is_string($name) || $name === '' || !preg_match('/^[!#$%&\'*+\-.^_`|~0-9A-Za-z]+$/', $name)) {
            trigger_error('ヘッダー名の形式が不正です。', E_USER_WARNING);

            return;
        }

        // ヘッダーインジェクション対策
        if (preg_match('/[\r\n]/', $name) || preg_match('/[\r\n]/', $value)) {
            trigger_error('ヘッダーに改行文字は使用できません。', E_USER_WARNING);

            return;
        }

        // 重要なヘッダーの上書きを防止
        $protectedHeaders = ['from', 'to', 'subject', 'cc', 'bcc', 'reply-to', 'return-path', 'date', 'mime-version', 'content-type', 'content-transfer-encoding'];
        if (in_array(strtolower($name), $protectedHeaders, true)) {
            trigger_error('保護されたヘッダーは上書きできません: '.$name, E_USER_WARNING);

            return;
        }

        $this->customHeaders[$name] = $value;
    }

    /**
     * カスタムヘッダーをクリア
     */
    public function clearCustomHeaders()
    {
        $this->customHeaders = [];
    }

    /**
     * 件名の設定
     */
    public function setSubject($subject)
    {
        $subject = str_replace(["\r\n", "\r", "\n"], ' ', $subject);
        $this->subject = $subject;
    }

    public function getEncodedSubject()
    {
        $subject = $this->subject;

        $subject = mb_encode_mimeheader($subject, $this->getCharsetForEncodeProcess(), 'B', "\n");

        return $subject;
    }

    /**
     * 本文の設定
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getEncodedBody()
    {
        $body = $this->body;

        if ($this->useBase64ForBody) {
            $body = mb_convert_encoding($this->body, $this->getCharsetForEncodeProcess());

            // Base64 エンコード（RFC2045 に準拠し chunk_split で76文字ごとに改行）
            $body = chunk_split(base64_encode($body));
        }

        $body = mb_convert_encoding($body, $this->getCharsetForEncodeProcess(), CHAR_CODE);
        $body = str_replace(["\r\n", "\r"], "\n", $body);

        return $body;
    }

    /**
     * 前方互換用
     *
     * @deprecated 2.12.2 (#1912)
     */
    public function setHost($host)
    {
        trigger_error('前方互換用メソッドが使用されました。', E_USER_WARNING);
        $this->host = $host;
        $arrHost = [
            'host' => $this->host,
            'port' => $this->port,
        ];
        // PEAR::Mailを使ってメール送信オブジェクト作成
        $this->objMail = \Mail::factory('smtp', $arrHost);
    }

    /**
     * 前方互換用
     *
     * @deprecated 2.12.2 (#1912)
     */
    public function setPort($port)
    {
        trigger_error('前方互換用メソッドが使用されました。', E_USER_WARNING);
        $this->port = $port;
        $arrHost = [
            'host' => $this->host,
            'port' => $this->port,
        ];
        // PEAR::Mailを使ってメール送信オブジェクト作成
        $this->objMail = \Mail::factory('smtp', $arrHost);
    }

    /**
     * `名前 <メールアドレス>` の形式を生成
     */
    public function getNameAddress(?string $name, ?string $mail_address)
    {
        $name = (string) $name;
        $mail_address = (string) $mail_address;

        $name_address = (function () use ($name) {
            switch (true) {
                // 空文字の場合
                case $name === '':
                    return '';
                    // ダブルクォーテーションまたはバックスラッシュを含む場合
                    // - バックスラッシュを正しく解釈しない MUA があるため、ASCII として処理せずエンコードに回す。
                    // - `"` も原則通りエスケープすると `\"` となるため同様。
                case strcspn($name, '"\\') < strlen($name):
                    // nop
                    break;
                    // 印字可能 ASCII（制御文字除く）のみの場合
                case preg_match('/^[\x20-\x7E]*$/', $name):
                    return '"'.$name.'" ';
            }

            return mb_encode_mimeheader($name, $this->getCharsetForEncodeProcess(), 'B', "\n").' ';
        })();
        $name_address .= "<{$mail_address}>";

        return $name_address;
    }

    public function setItem($to, $subject, $body, $fromaddress, $from_name, $reply_to = '', $return_path = '', $errors_to = '', $bcc = '', $cc = '')
    {
        $this->setBase($to, $subject, $body, $fromaddress, $from_name, $reply_to, $return_path, $errors_to, $bcc, $cc);
    }

    public function setItemHtml($to, $subject, $body, $fromaddress, $from_name, $reply_to = '', $return_path = '', $errors_to = '', $bcc = '', $cc = '')
    {
        $this->setBase($to, $subject, $body, $fromaddress, $from_name, $reply_to, $return_path, $errors_to, $bcc, $cc);
    }

    /*  ヘッダ等を格納
         $to            -> 送信先メールアドレス
         $subject       -> メールのタイトル
         $body          -> メール本文
         $fromaddress   -> 送信元のメールアドレス
         $header        -> ヘッダー
         $from_name     -> 送信元の名前（全角OK）
         $reply_to      -> reply_to設定
         $return_path   -> return-pathアドレス設定（エラーメール返送用）
         $cc            -> カーボンコピー
         $bcc           -> ブラインドカーボンコピー
    */
    public function setBase($to, $subject, $body, $fromaddress, $from_name, $reply_to = '', $return_path = '', $errors_to = '', $bcc = '', $cc = '')
    {
        // 宛先設定
        $this->setTo($to);
        // 件名設定
        $this->setSubject($subject);
        // 本文設定
        $this->setBody($body);
        // 送信元設定
        $this->setFrom($fromaddress, $from_name);
        // 返信先設定
        $this->setReplyTo($reply_to);
        // CC設定
        $this->setCc($cc);
        // BCC設定
        $this->setBcc($bcc);

        // Errors-Toは、ほとんどのSMTPで無視され、Return-Pathが優先されるためReturn_Pathに設定する。
        if ($errors_to != '') {
            $this->return_path = $errors_to;
        } elseif ($return_path != '') {
            $this->return_path = $return_path;
        } else {
            $this->return_path = $fromaddress;
        }
    }

    /**
     * ヘッダーを返す (後方互換)
     *
     * @deprecated getHeader() を使用すること。
     */
    public function getBaseHeader()
    {
        $arrHeader = $this->getHeader();
        unset($arrHeader['Content-Type']);

        return $arrHeader;
    }

    /**
     * ヘッダーを返す
     *
     * @param bool $use_html HTMLメールか
     */
    public function getHeader(bool $use_html = false): array
    {
        // 送信するメールの内容と送信先
        $arrHeader = [];
        $arrHeader['MIME-Version'] = '1.0';
        $arrHeader['To'] = $this->getToWithEncodedName();
        $arrHeader['Subject'] = $this->getEncodedSubject();
        $arrHeader['From'] = $this->getFromWithEncodedName();
        $arrHeader['Return-Path'] = $this->return_path;
        if ($this->reply_to != '') {
            $arrHeader['Reply-To'] = $this->reply_to;
        }
        if ($this->cc != '') {
            $arrHeader['Cc'] = $this->getCcWithEncodedName();
        }
        if ($this->bcc != '') {
            $arrHeader['Bcc'] = $this->getBccWithEncodedName();
        }
        $arrHeader['Date'] = date('D, j M Y H:i:s O');
        $arrHeader['Content-Transfer-Encoding'] = $this->getContentTransferEncoding();

        $arrHeader['Content-Type'] = $use_html
            ? 'text/html; charset="'.$this->getCharsetForHeader().'"'
            : 'text/plain; charset="'.$this->getCharsetForHeader().'"'
        ;

        // カスタムヘッダーをマージ
        foreach ($this->customHeaders as $name => $value) {
            $arrHeader[$name] = $value;
        }

        return $arrHeader;
    }

    /**
     * ヘッダーを返す (後方互換)
     *
     * @deprecated getHeader() を使用すること。
     */
    public function getTEXTHeader()
    {
        return $this->getHeader();
    }

    /**
     * ヘッダーを返す (後方互換)
     *
     * @deprecated getHeader() を使用すること。
     */
    public function getHTMLHeader()
    {
        return $this->getHeader(true);
    }

    /**
     * メーラーバックエンドに応じた送信先を返す
     *
     * @return array|string メーラーバックエンドに応じた送信先
     */
    public function getRecip()
    {
        switch ($this->backend) {
            // PEAR::Mail_mail#send は、(他のメーラーバックエンドと異なり) 第1引数を To: として扱う。Cc: や Bcc: は、ヘッダー情報から処理する。
            case 'mail':
                return $this->to;

            case 'sendmail':
            case 'smtp':
            default:
                return $this->arrRecip;
        }
    }

    /**
     * TXTメール送信を実行する.
     *
     * 設定された情報を利用して, メールを送信する.
     *
     * @return bool
     */
    public function sendMail($isHtml = false)
    {
        $header = $this->getHeader($isHtml);
        $recip = $this->getRecip();

        // メール送信
        $result = $this->objMail->send($recip, $header, $this->getEncodedBody());
        if (\PEAR::isError($result)) {
            // XXX Windows 環境では SJIS でメッセージを受け取るようなので変換する。
            $msg = mb_convert_encoding($result->getMessage(), CHAR_CODE, 'auto');
            $msg = 'メール送信に失敗しました。['.$msg.']';
            trigger_error($msg, E_USER_WARNING);
            GC_Utils_Ex::gfPrintLog($result->getMessage());
            GC_Utils_Ex::gfDebugLog($header);

            return false;
        }

        return true;
    }

    /**
     * HTMLメール送信を実行する.
     *
     * @return bool
     */
    public function sendHtmlMail()
    {
        return $this->sendMail(true);
    }

    /**
     * メーラーバックエンドに応じたパラメーターを返す.
     *
     * @param  string $backend Pear::Mail のバックエンド
     *
     * @return array  メーラーバックエンドに応じたパラメーターの配列
     */
    public function getBackendParams($backend)
    {
        switch ($backend) {
            case 'mail':
                $arrParams = [];
                $objDb = new SC_Helper_DB_Ex();
                $objSite = $objDb->sfGetBasisData();
                if (!empty($objSite['email04']) && strpos($objSite['email04'], '@') > 0) {
                    $arrParams[] = '-f '.$objSite['email04'];
                }
                break;

            case 'sendmail':
                $arrParams = [
                    'sendmail_path' => '/usr/bin/sendmail',
                    'sendmail_args' => '-i',
                ];
                break;

            case 'smtp':
                $arrParams = [
                    'host' => $this->host,
                    'port' => $this->port,
                ];
                if (defined('SMTP_USER')
                    && defined('SMTP_PASSWORD')
                    && !SC_Utils_Ex::isBlank(SMTP_USER)
                    && !SC_Utils_Ex::isBlank(SMTP_PASSWORD)) {
                    $arrParams['auth'] = true;
                    $arrParams['username'] = SMTP_USER;
                    $arrParams['password'] = SMTP_PASSWORD;
                }
                break;

            default:
                trigger_error('不明なバックエンド。[$backend = '.var_export($backend, true).']', E_USER_ERROR);
                exit;
        }

        return $arrParams;
    }

    /**
     * エンコード処理で用いる文字コードを設定する。
     *
     * @param string $charset 文字コード。
     */
    public function setCharset(string $charset)
    {
        $this->charset = $charset;
    }

    /**
     * エンコード処理で用いる文字コードを返す。
     */
    public function getCharsetForEncodeProcess(): string
    {
        if ($this->charset === 'ISO-2022-JP') {
            return 'ISO-2022-JP-MS';
        }

        return $this->charset;
    }

    /**
     * ヘッダーに記すための文字コードを取得する。
     */
    public function getCharsetForHeader(): string
    {
        return $this->charset;
    }

    /**
     * ヘッダーに記すための Content-Transfer-Encoding を取得する。
     */
    public function getContentTransferEncoding(): string
    {
        $charset = $this->getCharsetForHeader();

        if ($this->useBase64ForBody === true) {
            return 'base64';
        }

        if ($charset === 'ISO-2022-JP') {
            return '7bit';
        }

        return '8bit';
    }

    public function setUseBase64ForBody(?bool $useBase64ForBody): void
    {
        $this->useBase64ForBody = $useBase64ForBody;
    }
}
