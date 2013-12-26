<?php

class MandrillerTests extends PHPUnit_Framework_TestCase
{
    public function testConfig()
    {
        $expected = array(
            'api_key' => '123',
            'async' => false,
            'preserve_recipients' => false,
            'user_agent' => 'Fuel-Mandriller/0.1',
            'custom_headers' => array(),
            'api_url' => 'https://mandrillapp.com/api/1.0/',
        );

        $mandrill = new \Mandriller\Mandriller();

        $this->assertEquals($expected, $mandrill->defaults);

        // test loading config via constructor
        $config = array(
            'api_key' => '123',
            'async' => false,
            'preserve_recipients' => false,
            'user_agent' => 'Fuel-Mandriller/0.1',
            'custom_headers' => array(),
        );

        $expected = array(
            'api_key' => '123',
            'async' => false,
            'preserve_recipients' => false,
            'user_agent' => 'Fuel-Mandriller/0.1',
            'custom_headers' => array(),
            'api_url' => 'https://mandrillapp.com/api/1.0/',
        );

        $mandrill = new \Mandriller\Mandriller($config);

        $this->assertEquals($expected, $mandrill->defaults);
    }
}
