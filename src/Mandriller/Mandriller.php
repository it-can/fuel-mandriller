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
        'async'               => false,
        'preserve_recipients' => false,
        'user_agent'          => 'Fuel-Mandriller/0.1',
        'custom_headers'      => array(),
    );

    /**
     * @var  string  $api_url
     */
    protected $api_url = 'https://mandrillapp.com/api/1.0/';

    /**
     * @var  resource  $ch
     */
    protected $ch;

    /**
     * @var  array  $to
     */
    protected $to = array();

    /**
     * @var  array  $from
     */
    protected $from = array(
        'name' => '',
        'email' => '',
    );

    /**
     * @var  string  $reply_to
     */
    protected $reply_to;

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
     * @var  bool  $important
     */
    protected $important = false;

    /**
     * @var  array  $allowed_methods
     */
    protected $allowed_methods = array('messages/send-template', 'messages/send');

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
     * @throws Exception
     * @return object    The response object
     */
    public function request($method)
    {
        if (empty($method) or ! in_array($method, $this->allowed_methods))
        {
            // Throw exception
            throw new Mandriller_Exception('Not allowed to call ' . $method);
        }

        // Create array with info
        $arguments = $this->createMessage();

        // setup curl request
        $ch = $this->ch;

        curl_setopt($ch, CURLOPT_URL, $this->api_url . $method . '.json');
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

        // Reset
        $this->reset();

        return $result;
    }

    /**
     * Reset fields
     *
     * @return void
     */
    public function reset()
    {
        $this->to = array();
        $this->from = array(
            'name' => '',
            'email' => '',
        );
        $this->reply_to = '';
        $this->subject = '';
        $this->templateName = '';
        $this->mergeVars = array();
        $this->important = false;
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
        $this->to[] = array(
            'email' => $to,
            'name' => $name,
            'type' => $type
        );
    }

    /**
     * Set reply-to field
     *
     * @param  string    $reply_to The email address to reply to
     *
     * @return void
     */
    public function reply_to($reply_to)
    {
        $this->reply_to = $reply_to;
    }

    /**
     * Set from field
     *
     * @param  string     $email The email address that send the email
     * @param  string     $name The name that send the email
     *
     * @return void
     */
    public function from($email, $name = '')
    {
        $this->from = array(
            'email' => $email,
            'name' => $name,
        );
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
     * Set important
     *
     * @param  bool     $important Is this email important?
     *
     * @return void
     */
    public function important($important = false)
    {
        $this->important = (bool) $important;
    }

    /**
     * Create the array to send to Mandrill
     *
     *
     * @return array
     */
    public function createMessage()
    {
        $message = array(
            'key' => $this->defaults['api_key'],
            'async' => $this->defaults['async'],
            'template_name' => $this->templateName,
            'template_content' => array(),
            'message' => array(
                'preserve_recipients' => $this->defaults['preserve_recipients'],
                'headers'             => $this->defaults['custom_headers'],
                'subject'             => $this->subject,
                'from_name'           => $this->from['name'],
                'from_email'          => $this->from['email'],
                'to'                  => $this->to,
                'global_merge_vars'   => $this->mergeVars,
                'important'           => $this->important,
            ),
        );

        // Set reply_to headers
        if ( ! empty($this->reply_to))
        {
            $message['message']['headers'] = array_merge(
                array('Reply-To' => $this->reply_to),
                $message['message']['headers']);
        }

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
        return $this->request('messages/send-template');
    }
}
