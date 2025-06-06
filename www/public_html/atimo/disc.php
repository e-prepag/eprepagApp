<?php

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

     $file = fopen("/mnt/logs/sale.txt", "a+");
     fwrite($file, "E-PREPAG\n");
