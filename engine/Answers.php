<?php

class Answers
{
    public
        $answ;

    public function input_all($a, $b, $c, $d)
    {
        if ($a == 0) {
            $a = false;
        }

        if ($b == 0) {
            $b = false;
        }

        if ($c == 0) {
            $c = false;
        }

        if ($d == 0) {
            $d = false;
        }

        $this->answ[0] = $a;
        $this->answ[1] = $b;
        $this->answ[2] = $c;
        $this->answ[3] = $d;
    }

    public function input_one($key, $val)
    {
        if ($key == "A") {
            $this->answ[0] = $val;
        }

        if ($key == "B") {
            $this->answ[1] = $val;
        }

        if ($key == "C") {
            $this->answ[2] = $val;
        }

        if ($key == "D") {
            $this->answ[3] = $val;
        }

    }

    public function get_count()
    {
        $count = 0;
        for ($i = 0; $i < 4; $i++) {
            if ($this->answ[$i] != 0 and $this->answ[$i] != false) {
                $count++;
            }
        }

        return $count;
    }

    public function out_all()
    {
        $arr_symbol = array('A', 'B', 'C', 'D');
        $arr = array();
        for ($i = 0; $i < 4; $i++) {
            if ($this->answ[$i] != false) {
                array_push($arr, $arr_symbol[$i]);
            }

        }
        return $arr;

    }

    public function get_one($key)
    {
        if ($key == "A") {
            return $this->answ[0];
        }

        if ($key == "B") {
            return $this->answ[1];
        }

        if ($key == "C") {
            return $this->answ[2];
        }

        if ($key == "D") {
            return $this->answ[3];
        }

    }

    public function dump()
    {
        var_dump($this->answ);
    }
}