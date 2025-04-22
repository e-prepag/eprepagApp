<?php
/*
 * paypal_process.php
 *
 * PHP Toolkit for PayPal v0.51
 * http://www.paypal.com/pdn
 *
 * Copyright (c) 2004 PayPal Inc
 *
 * Released under Common Public License 1.0
 * http://opensource.org/licenses/cpl.php
 *
 */


//Configuration File
include_once('includes/config.inc.php'); 

//Global Configuration File
include_once('includes/global_config.inc.php');

 // echo 'paypal: ';
 // echo '<pre>'.print_r($paypal,true).'</pre>';
 // echo "<hr>";

 // print_r($_REQUEST);
 // echo "<hr>";
 // die('stop');
?> 

<html>
<head>
	<title>::PHP PayPal::</title>
	<script language="JavaScript">
		function get_form_fields() {
			for(i=0; i<document.FormName.elements.length; i++) {
				document.write("The field name is: " + document.FormName.elements[i].name + " and it’s value is: " + document.FormName.elements[i].value + ".<br />");
			}
		}
	</script>
</head>
<body onLoad="document.paypal_form.submit();">
<form method="post" name="paypal_form" action="<?php echo $paypal[url];?>">

<?php 
//show paypal hidden variables

showVariables(); 

?> 

<center><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="333333">Processing Transaction . . . </font></center>

</form>
<hr>
	<script language="JavaScript">
		get_form_fields(); 
	</script>

</body>   
</html>
