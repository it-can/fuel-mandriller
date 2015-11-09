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
            'ip' => '',
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
            'ip' => '',
        );

        $expected = array(
            'api_key' => '123',
            'async' => false,
            'preserve_recipients' => false,
            'user_agent' => 'Fuel-Mandriller/0.1',
            'custom_headers' => array(),
            'ip' => '',
        );

        $mandrill = new \Mandriller\Mandriller($config);

        $this->assertEquals($expected, $mandrill->defaults);

        // Test exception
        unset($config['api_key']);

        $this->setExpectedException('Mandriller\Mandriller_Exception', 'You must provide a Mandrill API key!');
        $mandrill = new \Mandriller\Mandriller($config);
    }

    public function testMessageArray()
    {
        $expected = array(
            'key' => '123',
            'async' => false,
            'template_name' => 'test-template',
            'template_content' => array(),
            'message' => array(
                'html' => '',
                'text' => '',
                'preserve_recipients' => false,
                'headers' => array('Reply-To' => 'test_to4@example.com'),
                'subject'    => 'Subject test',
                'from_name' => 'Test',
                'from_email' => 'test@example.com',
                'to'         => array(
                    array(
                        'email' => 'test_to@example.com',
                        'name' => 'TEST MAN',
                        'type' => 'to',
                    ),
                    array(
                        'email' => 'test_to2@example.com',
                        'name' => 'TEST MAN2',
                        'type' => 'bcc',
                    ),
                    array(
                        'email' => 'test_to3@example.com',
                        'name' => 'TEST MAN3',
                        'type' => 'cc',
                    ),
                ),
                'global_merge_vars' => array(
                    array(
                        'name' => 'TEST',
                        'content' => 'OKido',
                    ),
                ),
                'important' => true,
            ),
        );

        $mandrill = new \Mandriller\Mandriller();
        $mandrill->from('test@example.com', 'Test');
        $mandrill->to('test_to@example.com', 'TEST MAN');
        $mandrill->to('test_to2@example.com', 'TEST MAN2', 'bcc');
        $mandrill->to('test_to3@example.com', 'TEST MAN3', 'cc');
        $mandrill->reply_to('test_to4@example.com');
        $mandrill->subject('Subject test');
        $mandrill->template('test-template');
        $mandrill->mergeVars(array(
            'name' => 'TEST',
            'content' => 'OKido',
        ));
        $mandrill->important(true);

        $this->assertEquals($expected, $mandrill->createMessage());
    }
}
