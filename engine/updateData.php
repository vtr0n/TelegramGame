<?php

include("TelegramApi.php");
include("MySQL.php");

$Telegram = new TelegramApi;
$MySQL = new MySQL;

$offset = 0;
while (true) {
    $resp = $Telegram->getUpdates($offset);

    $date = strtotime(date('Y-m-d H:i:s'));
    if (isset($resp->result) and $resp->result[count($resp->result) - 1]->update_id > $offset) {
        for($i=0;$i<count($resp->result);$i++) {

        }

    }
}