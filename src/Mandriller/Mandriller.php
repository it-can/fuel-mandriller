<?php

/**
 * A package to use Mandrill api http://mandrill.com/.
 *
 * @package    Fuel-Mandriller
 * @version    0.1
 * @author     Michiel Vugteveen
 * @license    MIT License
 * @copyright  2013 Michiel Vugteveen
 * @link       https://github.com/it-can/fuel-mandriller
 */

namespace Mandriller;

/**
 * Exception for Mandriller
 */
class Mandriller_Exception extends \FuelException {}

class Mandriller {

    /**
     * @var string The secret API key
     */
    protected static $api_key = '';

    /**
     * @var string The API url
     */
    protected static $api_url = 'https://mandrillapp.com/api/1.0/';

    /**
     * @var bool Async
     */
    protected static $async = false;

    /**
     * @var bool Preserve recipients
     */
    protected static $preserve_recipients = false;

    /**
     * @var string User agent
     */
    protected static $user_agent = 'Fuel-Mandriller/0.1';

    /**
     * @var array Custom headers
     */
    protected static $custom_headers = array();

    /**
     * Static constructor called by autoloader
     */
    public static function _init()
    {
        $config = \Config::load('mandriller', true);

        if (empty($config['api_key']))
        {
            throw new \Mandriller_Exception('API key not specified.');
        }

        static::$api_key = $config['api_key'];
        static::$async = $config['async'];
        static::$preserve_recipients = $config['preserve_recipients'];
        static::$user_agent = $config['user_agent'];
        static::$custom_headers = $config['custom_headers'];
    }

    /**
     * Make the actual API call via curl
     *
     * @param  string    $method    API method that has been called
     * @param  array     $arguments Arguments that have been passed to it
     * @throws Exception
     * @return object    The response object
     */
    public static function request($method, $arguments = array())
    {
        // build arguments to send
        $arguments['key'] = static::$api_key;
        $arguments['async'] = static::$async;
        $arguments['message']['preserve_recipients'] = static::$preserve_recipients;
        $arguments['message']['headers'] = static::$custom_headers;

        // setup curl request
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_USERAGENT      => static::$user_agent,
            CURLOPT_URL            => static::$api_url . $method . '.json',
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER         => false,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_POST           => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_PORT           => 443,
            CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
            CURLOPT_POSTFIELDS     => json_encode($arguments),
        ));

        // Execute request
        $response_body = curl_exec($ch);
        $info = curl_getinfo($ch);

        // catch errors
        if ($error = curl_error($ch))
        {
            curl_close($ch);

            // Throw exception
            throw new \Mandriller_Exception('Mandrill API call to url failed: ' . $error);
        }

        // Close connection
        curl_close($ch);

        // return array
        $result = json_decode($response_body, true);

        // Check for failed calls to mandrill
        if (floor($info['http_code'] / 100) >= 4)
        {
            throw new \Mandriller_Exception('Mandrill error #' . $result['code'] . ': ' . $result['message']);
        }

        // Check for errors inside the response
        if ( ! empty($result[0]))
        {
            // Is response status is not sent, than error
            if (in_array($result[0]['status'], array('rejected', 'invalid')))
            {
                throw new \Mandriller_Exception('Mandrill response error: ' . $result[0]['status']);
            }
        }
        else if ( ! empty($result['code']))
        {
            throw new \Mandriller_Exception('Mandrill response error: ' . $result['message']);
        }

        return $result;
    }

    /**
     * Do a request to "messages/send-template"
     *
     * @param  array     $message Message info
     * @throws Exception
     * @return object    The response object
     */
    public static function sendTemplate($message = array())
    {
        // Send email
        return static::request('messages/send-template', $message);
    }
}
