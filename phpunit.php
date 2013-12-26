<?php

class FuelException extends \Exception {}

class Config {
    public static function load()
    {
        return array(
            'api_key' => '123',
            'async' => false,
            'preserve_recipients' => false,
            'user_agent' => 'Fuel-Mandriller/0.1',
            'custom_headers' => array(),
        );
    }
}

include './vendor/autoload.php';
