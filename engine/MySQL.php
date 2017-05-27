<?php

class MySQL
{
    var
        $link;

    function __construct($config = NULL)
    {
        include(__DIR__ . "/config.php");
        $this->link = mysqli_connect(
            "localhost",
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