<?php

include("Answers.php");
include("MySQL.php");
include("TelegramApi.php");

$Answers = new Answers;
$Telegram = new TelegramApi;
$MySQL = new MySQL;

$valid_command = array("A", "B", "C", "D");

while (true) {
    $data = $MySQL->get_new_data();
    for($i = 0; $i < count($data); $i++) {
        if(in_array($data[$i]["message"], $valid_command)) { // Если команды разрешены

            $current_branch = $data[$i]["branch"];
            $current_game = $MySQL->get_game_by_branch($current_branch);

            $next_branch = $current_game[$data[$i]["message"]];
            $next_game = $MySQL->get_game_by_branch($next_branch);
            $Answers->input_all($next_game["A"], $next_game["B"], $next_game["C"], $next_game["D"]);

            $wait_by_game = $next_game["time"];
            $chat_id = $data[$i]["chat_id"];

            if(strtotime(date('Y-m-d H:i:s')) > $data[$i]["date"] + $wait_by_game) {
                if($next_game["photo"] != "") {
                    $Telegram->sendPhoto($data[$i]["chat_id"], $next_game["photo"]);
                }
                $Telegram->sendMessage($data[$i]["chat_id"], $next_game["message"], $Answers);


                if($Answers->get_count() == 0) { // Если это последнее сообщение в ветви
                    echo 123;
                }
                elseif ($Answers->get_count() == 1) {
                    $MySQL->full_update_users("Waiting for the timeout", $Answers->out_all()[0], $next_branch, $chat_id);
                }
                else {
                    $MySQL->full_update_users("waiting user input", "", $next_branch, $chat_id);
                }
            }

        }
    }
}