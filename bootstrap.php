<?php

/**
 * A package to use Mandrill api http://mandrill.com/.
 *
 * @package    Mandrill
 * @version    0.1
 * @author     Michiel Vugteveen
 * @license    MIT License
 * @copyright  2013 Michiel Vugteveen
 * @link       http://www.it-can.nl
 */

Autoloader::add_core_namespace('Mandriller');

Autoloader::add_classes(array(
    'Mandriller\\Mandriller' => __DIR__ . '/classes/mandriller.php',
));
