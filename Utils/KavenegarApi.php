<?php

namespace Utils;


class KavenegarApi
{
    const APIPATH = "%s://api.kavenegar.com/v1/%s/%s/%s.json/";
    const VERSION = "1.2.2";
    public function __construct($apiKey, $insecure=false)
    {
        if (!extension_loaded('curl')) {
            die('cURL library is not loaded');
            exit;
        }
        if (is_null($apiKey)) {
            die('apiKey is empty');
            exit;
        }
        $this->apiKey = trim($apiKey);
        $this->insecure = $insecure;
    }

	protected function get_path($method, $base = 'sms')
    {
        return sprintf(self::APIPATH, $this->insecure == true ? "http" : "https", $this->apiKey, $base, $method);
    }

    public function Send($sender, $receptor, $message, $date = null, $type = null, $localid = null)
    {
        if (is_array($receptor)) {
            $receptor = implode(",", $receptor);
        }
        if (is_array($localid)) {
            $localid = implode(",", $localid);
        }
        $path   = $this->get_path("send");
        $params = array(
            "receptor" => $receptor,
            "sender" => $sender,
            "message" => $message,
            "date" => $date,
            "type" => $type,
            "localid" => $localid
        );
        return $this->execute($path, $params);
    }

    public function SendArray($sender, $receptor, $message, $date = null, $type = null, $localmessageid = null)
    {
        if (!is_array($receptor)) {
            $receptor = (array) $receptor;
        }
        if (!is_array($sender)) {
            $sender = (array) $sender;
        }
        if (!is_array($message)) {
            $message = (array) $message;
        }
        $repeat = count($receptor);
        if (!is_null($type) && !is_array($type)) {
            $type = array_fill(0, $repeat, $type);
        }
        if (!is_null($localmessageid) && !is_array($localmessageid)) {
            $localmessageid = array_fill(0, $repeat, $localmessageid);
        }
        $path   = $this->get_path("sendarray");
        $params = array(
            "receptor" => json_encode($receptor),
            "sender" => json_encode($sender),
            "message" => json_encode($message),
            "date" => $date,
            "type" => json_encode($type),
            "localmessageid" => json_encode($localmessageid)
        );
        return $this->execute($path, $params);
    }
}
