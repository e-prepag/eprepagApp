<?php
die("Stop");

include_once('./ip2country.php');

$ip2c=new ip2country();
$ip2c->mysql_host='mysql01_3.renebmjr_1.pessoal_2.ws';
$ip2c->db_user='renebmjr****';
$ip2c->db_pass='proero4012******';
$ip2c->db_name='renebmjr****';
$ip2c->table_name='ip2c';

echo 'Your country name is '. $ip2c->get_country_name() . '<br>';
echo 'Your country code is ' . $ip2c->get_country_code();
echo "<hr>";
echo $ip2c->get_country_name('72.14.233.89');
?>
<hr>
<a href="http://www.sitevisitormaps.com" target="_blank"><img src="http://m.sitevisitormaps.com/13252.png" style="border:0;" /></a>