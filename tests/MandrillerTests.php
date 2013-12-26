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
        );

        $mandrill = new \Mandriller\Mandriller($config);

        $this->assertEquals($expected, $mandrill->defaults);

        // Test exception
        unset($config['api_key']);

        $this->setExpectedException('Mandriller\Mandriller_Exception', 'You must provide a Mandrill API key!');
        $mandrill = new \Mandriller\Mandriller($config);
    }
}
