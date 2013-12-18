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
     * @var  array  Default configuration values
     */
    protected $defaults = array(
        'api_key'             => '',
        'api_url'             => 'https://mandrillapp.com/api/1.0/',
        'async'               => false,
        'preserve_recipients' => false,
        'user_agent'          => 'Fuel-Mandriller/0.1',
        'custom_headers'      => array(),
    );

    /**
     * Constructor
     *
     * @param  array|null  $config  Optional array of configuration items
     *
     * @return void
     */
    public function __construct()
    {
        $config = \Config::load('mandriller', true);

        // override defaults if needed
        if (is_array($config))
        {
            foreach ($config as $key => $value)
            {
                array_key_exists($key, $this->defaults) and $this->defaults[$key] = $value;
            }
        }
    }

    /**
     * Make the actual API call via curl
     *
     * @param  string    $method    API method that has been called
     * @param  array     $arguments Arguments that have been passed to it
     * @throws Exception
     * @return object    The response object
     */
    public function request($method, $arguments = array())
    {
        // build arguments to send
        $arguments['key'] = $this->defaults['api_key'];
        $arguments['async'] = $this->defaults['async'];
        $arguments['message']['preserve_recipients'] = $this->defaults['preserve_recipients'];
        $arguments['message']['headers'] = $this->defaults['custom_headers'];

        // setup curl request
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_USERAGENT      => $this->defaults['user_agent'],
            CURLOPT_URL            => $this->defaults['api_url'] . $method . '.json',
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
            throw new Mandriller_Exception('Mandrill API call to url failed: ' . $error);
        }

        // Close connection
        curl_close($ch);

        // return array
        $result = json_decode($response_body, true);

        // Check for failed calls to mandrill
        if (floor($info['http_code'] / 100) >= 4)
        {
            throw new Mandriller_Exception('Mandrill error #' . $result['code'] . ': ' . $result['message']);
        }

        // Check for errors inside the response
        if ( ! empty($result[0]))
        {
            // Is response status is not sent, than error
            if (in_array($result[0]['status'], array('rejected', 'invalid')))
            {
                throw new Mandriller_Exception('Mandrill response error: ' . $result[0]['status']);
            }
        }
        else if ( ! empty($result['code']))
        {
            throw new Mandriller_Exception('Mandrill response error: ' . $result['message']);
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
    public function sendTemplate($message = array())
    {
        // Send email
        return $this->request('messages/send-template', $message);
    }
}
