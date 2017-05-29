<?php

class MySQL
{
    var
        $link;

    function __construct()
    {
        include_once(__DIR__ . "/config.php");
        $this->link = mysqli_connect(
            config\MYSQL_HOST,
            config\MYSQL_USERNAME,
            config\MYSQL_PASSWORD,
            config\MYSQL_DB
        );
        mysqli_set_charset($this->link, "utf8mb4");
    }

    public function query()
    {
        //var_dump($this->prepareQuery(func_get_args()));
        return mysqli_query($this->link, $this->prepareQuery(func_get_args()));
    }

    public function get_user_info($chat_id)
    {
        $resp = $this->query("SELECT * FROM users WHERE chat_id = ?s", $chat_id);
        if(!$resp) {
            return false;
        } else {
            return mysqli_fetch_assoc($resp);
        }
    }

    public function create_new_user($chat_id)
    {
        $date = strtotime(date('Y-m-d H:i:s'));

        $this->query("INSERT INTO users(chat_id, message, status, branch, date) VALUES(?s, ?s, ?s, ?s, ?s)",
            $chat_id, "A", "Waiting for the timeout", 0, $date);

//        $this->query("INSERT INTO save(chat_id, message, status, branch, date) VALUES(?s, ?s, ?s, ?s, ?s)",
//            $chat_id, "A", 0, 0, $date);
    }

    public function update_user_info($chat_id, $message, $date)
    {
        $this->query("UPDATE users SET message = ?s, date = ?s, status = ?s WHERE chat_id = ?s AND status <> 'Waiting for the timeout'",
            $message, $date, "Waiting for the timeout", $chat_id);
    }

    public function full_update_users($status, $message, $branch, $chat_id)
    {
        $date = date('Y-m-d H:i:s');
        $date = strtotime($date);

        $this->query("UPDATE users SET status = ?s, message = ?s, branch = ?s, date = ?s WHERE chat_id = ?s",
            $status, $message, $branch, $date, $chat_id);
    }

    public function get_new_data()
    {
        $resp = $this->query("SELECT * FROM users WHERE status = 'Waiting for the timeout'");

        $arr = array();
        $i = 0;
        while ($row = mysqli_fetch_assoc($resp)) {
            $arr[$i] = $row;
            $i++;
        }
        return $arr;
    }

    public function get_game_by_branch($branch) {
        $resp = $this->query("SELECT * FROM game WHERE mid = ?s", $branch);
        if(!$resp) {
            return false;
        } else {
            return mysqli_fetch_assoc($resp);
        }
    }

    public function restart_game($chat_id)
    {
        $date = strtotime(date('Y-m-d H:i:s'));
        $this->query("UPDATE users SET message = ?s, branch = ?s, status = ?s, date = ?s WHERE chat_id = ?s",
            'A', 0, 'Waiting for the timeout', $date, $chat_id);
    }

    /** Методы защиты ***/
    private function prepareQuery($args)
    {
        $query = '';
        $raw = array_shift($args);
        $array = preg_split('~(\?[nsiuap])~u', $raw, null, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($array as $i => $part) {
            if (($i % 2) == 0) {
                $query .= $part;
                continue;
            }
            $value = array_shift($args);
            $part = $this->escapeVar($value);
            $query .= $part;
        }

        return $query;
    }

    private function escapeVar($value)
    {
        if ($value === NULL) {
            return 'NULL';
        }

        return "'" . mysqli_real_escape_string($this->link, $value) . "'";
    }
}