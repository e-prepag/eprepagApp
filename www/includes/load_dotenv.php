<?php
require_once '/www/libs/Autoloader.php';

$loader = new Autoloader([
    'Symfony\\Component\\Dotenv' => '/www/libs/dotenv'
]);

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load('/www/.env');
?>