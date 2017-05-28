<?php

include("TelegramApi.php");
include("MySQL.php");

$Telegram = new TelegramApi;
$MySQL = new MySQL;

$offset = 0;
while (true) {
    $resp = $Telegram->getUpdates($offset); // Получаем данные
    // Обрабатываем новые запросы
    if (isset($resp->result) and $resp->result[count($resp->result) - 1]->update_id > $offset) {
        for ($i = 0; $i < count($resp->result); $i++) {
            if ($resp->result[$i]->update_id > $offset) {
                $chat_id = $resp->result[$i]->message->chat->id;
                $user_date = $resp->result[$i]->message->date;

                if (!$user_info = $MySQL->get_user_info($chat_id)) { // Если новый пользователь
                    $MySQL->create_new_user($chat_id);
                } else {
                    // Если это новое сообщение
                    if (!empty($resp->result[$i]->message->text) and $user_info["date"] < $user_date) {
                        echo $message = $resp->result[$i]->message->text;
                    }
                }
            }

        }
        $offset = $resp->result[count($resp->result) - 1]->update_id; // Сдвиг для следующих запросов
    }
}