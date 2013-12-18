fuel-mandriller
======

Mandrill FuelPHP composer package (www.mandrill.com)

# Installation

Through Composer:

```json
{
    "require": {
        "it-can/fuel-mandriller": "dev-master"
    }
}
```

Next you will need to publish the config:

You can setup your config inside `fuel/app/config/mandriller.php`.

```php
<?php

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
```
