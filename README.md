# !!! NOT MAINTAINED ANYMORE !!!
======


fuel-mandriller
======

Use at your own risk! Api is subject to change!

[![Build Status](https://travis-ci.org/it-can/fuel-mandriller.png?branch=master)](https://travis-ci.org/it-can/fuel-mandriller)
[![Total Downloads](https://poser.pugx.org/it-can/fuel-mandriller/downloads.png)](https://packagist.org/packages/it-can/fuel-mandriller)

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
    'async' => false,

    // Show receivers of email in cc (if you set this to true, it will show all recipients in the email)
    'preserve_recipients' => false,

    // Set a custom user agent
    'user_agent' => 'Fuel-Mandriller/0.1',

    // Custom headers (for example: array('List-Unsubscribe' => '<mailto:info@example.com>');)
    'custom_headers' => array(),

    // Set your server ip address (optional, only if your server has multiple ip addresses)
    'ip' => '',
);
```

EXAMPLE (this only works for send-template method, send-template is the only supported Mandrill method at this time)

```php
<?php

// Send email
try {
    $mandrill = new \Mandriller\Mandriller();
    $mandrill->from('from@example.com', 'From name');
    $mandrill->to('user@example.com');
    $mandrill->reply_to('from@example.com');
    $mandrill->subject('Mandrill template test');
    $mandrill->template('mandrill-test-template');
    $mandrill->mergeVars(array(
        'name' => 'NAME',
        'content' => 'John',
    ));
    $mandrill->important(true); // default = false
    $mandrill->sendTemplate();

    // Do other stuff
} catch (\FuelException $e) {
    echo 'ERROR! ' . $e;
}

```
