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
    public $defaults = array(
        'api_key'             => '',
        'api_url'             => 'https://mandrillapp.com/api/1.0/',
        'async'               => false,
        'preserve_recipients' => false,
        'user_agent'          => 'Fuel-Mandriller/0.1',
        'custom_headers'      => array(),
    );

    /**
     * @var  resource  $ch
     */
    protected $ch;

    /**
     * @var  array  $to
     */
    protected $to = array();

    /**
     * @var  string  $from
     */
    protected $from;

    /**
     * @var  string  $subject
     */
    protected $subject;

    /**
     * @var  string  $templateName
     */
    protected $templateName;

    /**
     * @var  array  $mergeVars
     */
    protected $mergeVars = array();


    /**
     * Constructor
     *
     * @param  array|null  $config  Optional array of configuration items
     *
     * @return void
     */
    public function __construct($config = array())
    {
        // check if we have libcurl available
        if ( ! function_exists('curl_init'))
        {
            // Throw exception
            throw new Mandriller_Exception('Your PHP installation doesn\'t have cURL enabled. Rebuild PHP with --with-curl');
        }

        // Load config
        $config = ($config) ?: \Config::load('mandriller', true);

        // Override defaults if needed
        if (is_array($config))
        {
            foreach ($config as $key => $value)
            {
                array_key_exists($key, $this->defaults) and $this->defaults[$key] = $value;
            }
        }

        // Check if there is a api key
        if (empty($this->defaults['api_key']))
        {
            // Throw exception
            throw new Mandriller_Exception('You must provide a Mandrill API key!');
        }

        // Init cURL
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->defaults['user_agent']);
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($this->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
    }

    /**
     * Destructor
     *
     *
     * @return void
     */
    public function __destruct()
    {
        curl_close($this->ch);
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
        $ch = $this->ch;

        curl_setopt($ch, CURLOPT_URL, $this->defaults['api_url'] . $method . '.json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arguments));

        // Execute request
        $response_body = curl_exec($ch);
        $info = curl_getinfo($ch);

        // catch errors
        if ($error = curl_error($ch))
        {
            // Throw exception
            throw new Mandriller_Exception('Mandrill API call to url failed: ' . $error);
        }

        // Check for response
        $result = json_decode($response_body, true);
        if ($result === null)
        {
            // Throw exception
            throw new Mandriller_Exception('We were unable to decode the JSON response from the Mandrill API: ' . $response_body);
        }

        // Check for failed calls to mandrill
        if (floor($info['http_code'] / 100) >= 4)
        {
            // Throw exception
            throw new Mandriller_Exception('Mandrill error #' . $result['code'] . ': ' . $result['message']);
        }

        // Check for errors inside the response
        if ( ! empty($result[0]))
        {
            // Is response status is not sent, than error
            if (in_array($result[0]['status'], array('rejected', 'invalid')))
            {
                // Throw exception
                throw new Mandriller_Exception('Mandrill response error: ' . $result[0]['status']);
            }
        }
        else if ( ! empty($result['code']))
        {
            // Throw exception
            throw new Mandriller_Exception('Mandrill response error: ' . $result['message']);
        }

        return $result;
    }

    /**
     * Set to field
     *
     * @param  mixed     $to The email address to send to
     * @param  string    $name The optional display name to use for the recipient
     * @param  string    $type The header type to use for the recipient, defaults to "to" if not provided oneof(to, cc, bcc)
     *
     * @return void
     */
    public function to($to, $name = '', $type = 'to')
    {
        $this->to[] = array('email' => $to, 'name' => $name, 'type' => $type);
    }

    /**
     * Set from field
     *
     * @param  string     $from The email address that send the email
     *
     * @return void
     */
    public function from($from)
    {
        $this->from = $from;
    }

    /**
     * Set subject field
     *
     * @param  string     $subject The subject
     *
     * @return void
     */
    public function subject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Set template name
     *
     * @param  string     $name The Mandrill template name
     *
     * @return void
     */
    public function template($name)
    {
        $this->templateName = $name;
    }

    /**
     * Set merge vars for template
     *
     * @param  array     $vars The merge vars for the template
     *
     * @return void
     */
    public function mergeVars($vars)
    {
        $this->mergeVars[] = $vars;
    }

    /**
     * Create the array to send to Mandrill (send-template)
     *
     *
     * @return array
     */
    public function createTemplateMessage()
    {
        $message = array(
            'template_name' => $this->templateName,
            'template_content' => array(),
            'message' => array(
                'subject'    => $this->subject,
                'from_email' => $this->from,
                'to'         => $this->to,
                'global_merge_vars' => $this->mergeVars,
            ),
        );

        return $message;
    }

    /**
     * Do a request to "messages/send-template"
     *
     * @throws Exception
     * @return object    The response object
     */
    public function sendTemplate()
    {
        // Send email
        return $this->request('messages/send-template', $this->createTemplateMessage());
    }
}
