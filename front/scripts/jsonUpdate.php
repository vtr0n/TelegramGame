<?php
$json = $_REQUEST["json"];
file_put_contents("../json/game.json", $json);