<?php

class TelegramApi
{
    private
        $token;

    function __construct()
    {
        include_once(__DIR__ . "/config.php");
        $this->token = config\TOKEN;
    }

    public function getUpdates($offset)
    {
        return $this->request("getUpdates", array('offset' => $offset));
    }

    protected function request($method, $params = array())
    {
        $url = 'https://api.telegram.org/bot' . $this->token . '/' . $method;
        return json_decode($this->curl($url . '?' . http_build_query($params)));
    }

    private function curl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $resp = curl_exec($ch);
        curl_close($ch);
        return $resp;
    }

    public function sendMessage($chat_id, $text, $answers = NULL)
    {
        if ($answers == NULL or $answers->get_count() <= 1) { // Один ответ
            $content = array(
                'chat_id' => $chat_id,
                'text' => $text
            );

            return $this->request("sendMessage", $content);
        }
        else {
            $keyboard = $answers->out_all();
        }


        $replyMarkup = array(
            'keyboard' => array($keyboard),
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        );
        $encodedMarkup = json_encode($replyMarkup);
        $content = array(
            'chat_id' => $chat_id,
            'reply_markup' => $encodedMarkup,
            'text' => $text
        );
        return $this->request("sendMessage", $content);
    }

    public function sendPhoto($chat_id, $image)
    {
        $url = 'https://api.telegram.org/bot' . $this->token . '/sendPhoto?chat_id=' . $chat_id;
        $post_fields = array('chat_id' => $chat_id,
            'photo' => new CURLFile(realpath($image))
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        return $output = curl_exec($ch);
    }
}
