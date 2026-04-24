<?php

use Symfony\Component\Dotenv\Dotenv;

require_once '/www/vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load('/www/.env');
