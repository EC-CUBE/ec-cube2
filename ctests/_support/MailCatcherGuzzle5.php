<?php
namespace Codeception\Module;

use Codeception\Module;

class MailCatcherGuzzle5 extends MailCatcher
{
    public function _initialize()
    {
        $base_uri = trim($this->config['url'], '/') . ':' . $this->config['port'];
        // XXX FIX base_uri to base_url
        // see https://github.com/captbaritone/codeception-mailcatcher-module/issues/28

        $this->mailcatcher = new \GuzzleHttp\Client(['base_url' => $base_uri]);

        if (isset($this->config['guzzleRequestOptions'])) {
            foreach ($this->config['guzzleRequestOptions'] as $option => $value) {
                $this->mailcatcher->setDefaultOption($option, $value);
            }
        }
    }

    protected function emailFromId($id)
    {
        $response = $this->mailcatcher->get("/messages/{$id}.json");
        $message = json_decode($response->getBody(), true);

        $message['source'] = quoted_printable_decode($message['source']);
        $message['source'] = mb_convert_encoding($message['source'], 'UTF-8', 'JIS');
        return $message;
    }
}
