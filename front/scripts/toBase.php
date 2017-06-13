<?php

include("MySQL.php");
$SQL = new MySQL;

$json = file_get_contents(realpath(dirname(__FILE__) . '/../json/') . "/game.json");
$json = json_decode($json);

$nodeDataArray = $json->nodeDataArray;
$linkDataArray = $json->linkDataArray;

$SQL->query("TRUNCATE game");

$base = array();
for ($i = 0, $j = 1; $i < count($nodeDataArray); $i++, $j++) {

    $base[$i]["id"] = abs((int)$nodeDataArray[$i]->key);
    isset($nodeDataArray[$i]->message) ? $base[$i]["message"] = $nodeDataArray[$i]->message :
        $base[$i]["message"] = "";
    isset($nodeDataArray[$i]->sleep) ? $base[$i]["sleep"] = $nodeDataArray[$i]->sleep :
        $base[$i]["sleep"] = 0;
    isset($nodeDataArray[$i]->photo) ? $base[$i]["photo"] = $nodeDataArray[$i]->photo :
        $base[$i]["photo"] = "";

    $base[$i]["A"] = 0;
    $base[$i]["B"] = 0;
    $base[$i]["C"] = 0;
    $base[$i]["D"] = 0;
    $base[$i]["count"] = 0;
}

for ($i = 0; $i < count($linkDataArray); $i++) {
    if ($base[abs($linkDataArray[$i]->from) - 1]["count"] == 0) {
        $base[abs($linkDataArray[$i]->from) - 1]["A"] = abs((int)$linkDataArray[$i]->to) - 1;
    }
    if ($base[abs($linkDataArray[$i]->from) - 1]["count"] == 1) {
        $base[abs($linkDataArray[$i]->from) - 1]["B"] = abs((int)$linkDataArray[$i]->to) -1;
    }
    if ($base[abs($linkDataArray[$i]->from) - 1]["count"] == 2) {
        $base[abs($linkDataArray[$i]->from) - 1]["C"] = abs((int)$linkDataArray[$i]->to) -1;
    }
    if ($base[abs($linkDataArray[$i]->from) - 1]["count"] == 3) {
        $base[abs($linkDataArray[$i]->from) - 1]["D"] = abs((int)$linkDataArray[$i]->to) -1;
    }

    $base[abs($linkDataArray[$i]->from) - 1]["count"]++;
}

for ($i = 0; $i < count($nodeDataArray); $i++)
    $SQL->query("INSERT INTO game(mid, message, time, A, B, C, D, photo) VALUES(?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s);",
        $i,
        $base[$i]["message"],
        $base[$i]["sleep"],
        $base[$i]["A"],
        $base[$i]["B"],
        $base[$i]["C"],
        $base[$i]["D"],
        $base[$i]["photo"]
    );

