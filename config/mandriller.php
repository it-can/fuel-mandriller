<?php

/**
 * A package to use Mandrill api http://mandrill.com/.
 *
 * @package    Fuel-Mandriller
 * @version    0.1
 * @author     Michiel Vugteveen
 * @license    MIT License
 * @copyright  2013 Michiel Vugteveen
 * @link       https://github.com/IT-Can/fuel-mandriller
 */
/**
 * NOTICE:
 *
 * If you need to make modifications to the default configuration, copy
 * this file to your app/config folder, and make them in there.
 *
 * This will allow you to upgrade fuel without losing your custom config.
 */

return array(
    // your secret Mandrill API key
    'api_key' => '',

    // enable a background sending mode that is optimized for bulk sending. In async mode, messages/send will immediately return a status of "queued" for every recipient. To handle rejections when sending in async mode, set up a webhook for the 'reject' event. Defaults to false for messages with no more than 10 recipients; messages with more than 10 recipients are always sent asynchronously, regardless of the value of async.
    'async'   => false,

    // Show receivers of email in cc (if you set this to true, it will show all recipients in the email)
    'preserve_recipients' => false,

    // Set a custom user agent
    'user_agent' => 'Fuel-Mandriller/0.1',

    // Custom headers (for example: array('List-Unsubscribe' => '<mailto:info@example.com>');)
    'custom_headers' => array();
);

