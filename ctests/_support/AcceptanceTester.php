<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

   /**
    * Define custom actions here
    */

    /**
     * @param string|$fileNameRegex ファイル名のパターン(CI環境で同時実行したときに区別するため)
     * @param int $retryCount リトライカウント数
     * @param string $downloadDir ダウンロードディレクトリ
     *
     * @return string|bool ファイルパス. ファイルが見つからない場合 false
     */
    public function getLastDownloadFile($fileNameRegex, $retryCount = 3, $downloadDir = '/tmp')
    {
        $files = scandir($downloadDir);
        $files = array_map(function ($fileName) use ($downloadDir) {
            return $downloadDir.'/'.$fileName;
        }, $files);
        $files = array_filter($files, function ($f) use ($fileNameRegex) {
            return is_file($f) && preg_match($fileNameRegex, basename($f));
        });

        usort($files, function ($l, $r) {
            return filemtime($l) - filemtime($r);
        });

        if (empty($files)) {
            if ($retryCount > 0) {
                $this->wait(3);

                return $this->getLastDownloadFile($fileNameRegex, $retryCount - 1, $downloadDir);
            }
            return false;
        }

        return end($files);
    }
}
