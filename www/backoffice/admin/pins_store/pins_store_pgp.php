<?php
die("Stop");
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

putenv("GNUPGHOME=/tmp");

$pubkey = "-----BEGIN PGP PUBLIC KEY BLOCK-----
Version: GnuPG v2.0.17 (MingW32)

mQENBFGWpjcBCADIikG0cB+5lNb/DlhttBgtCVgkkj8jHYZzA35VSqFlvXWZt/IE
dq38yhda1AoxxcWo/lhswAliWF+y3JXsTul29BMIhHmkUv3S+sRXJQ1DxscvPARW
XyXLB9DDZ8dIyGKNO/8MNgzSNATqbV+mk25NApgNnZPpn4GtrWQgLxo2xOAhA/Q0
qytFJDq6dZG70/TweKzH1tqUgN6IYFSMQ3psfm/OFCQTg6izU0JWkFPHCCmJkTBf
ihVf+gD49wd0j4Q0mtG7vhnInXuy0YFodTgIXXXgx6Mf880y6MePeOr0e1rCtUWJ
GetM02EAIYlu7wvNYS+tfp/oy63PpoT7q+FXABEBAAG0LUUtUFJFUEFHIChEZXZl
bG9wZXIpIDx3YWduZXJAZS1wcmVwYWcuY29tLmJyPokBNwQTAQIAIgUCUZamNwIb
DwYLCQgHAwIGFQgCCQoLBBYCAwECHgECF4AACgkQLy5kWr/e02pO1Af2JjZstDg6
7pFJuiD2swsMUyLDYMkU3NXN81kEp6yLhLWLPRa+85nLE7KHK2s8hB+UMhGjCH6v
yz5IKAnoIjL1eNyFCUUL2BSlw+c4RcbKVV/popSI1p2agp9IzcAl3OIgdoR8gUt0
GCP+0gxg6mSXsnd/DzL4QAVRnXJy4VDAI927KQy2haAWDDQ+1Mr6XKms8J/IfOXI
ZY46gkowhVmTK16LY2bGcTcD9JXKOzQWcid1sI8pH7iIRp2sK1WLj3uHF9+bmT8T
a7CPAtxlHT81HINsvQYA1wIcW3otwneQrXUEXT5KpckOr6YYCocQyEsdp5PHfdPa
Ej3SVsfb8E8j
=J4me
-----END PGP PUBLIC KEY BLOCK-----";

$enc = (null);
$res = gnupg_init();
echo "gnupg_init RTV = <br/><pre>\n";
var_dump($res);
echo "</pre>\n";
$rtv = gnupg_import($res, $pubkey);
echo "gnupg_import RTV = <br/><pre>\n";
var_dump($rtv);
echo "</pre>\n";
$rtv = gnupg_addencryptkey($res, "C26297715C7FEAE23B86E1D72F2E645ABFDED36A");
// C26297715C7FEAE23B86E1D72F2E645ABFDED36A
echo "gnupg_addencryptkey RTV = <br /><pre>\n";
var_dump($rtv);
echo "</pre>\n";
$enc = gnupg_encrypt($res, "just a test to see if anything works");
echo "Encrypted Data: " . $enc . "<br/>";


?>
