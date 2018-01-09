<?php

class Request
{
    protected $req;
    protected $serv;
    public function __construct()
    {
        $this->req = $_REQUEST;
        $json = file_get_contents('php://input');
        if ($json && is_json($json)) {
            $this->req = array_merge($this->req, json_decode($json, true));
        }
        $this->serv = $_SERVER;
    }
    public function input($field = '', $default = null)
    {
        if ($field){
            if (isset($this->req[$field])) {
                return $this->req[$field];
            } else {
                if (isset($default)) {
                    $this->req[$field] = $default;
                    return $default;
                } else {
                    return '';
                }
            }
        } else {
            return $this->req;
        }
    }

    public function offsetSet($key, $value)
    {
        $this->req[$key] = $value;
        return $this->req;
    }

    public function server($field)
    {
        return $this->serv[$field];
    }
}



