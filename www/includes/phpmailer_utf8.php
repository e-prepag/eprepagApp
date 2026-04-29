<?php

if (!function_exists('eprepag_phpmailer_to_utf8')) {
	function eprepag_phpmailer_to_utf8($str)
	{
		if ($str && !mb_check_encoding($str, 'UTF-8')) {
			return mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
		}

		return $str;
	}
}

if (!function_exists('eprepag_phpmailer_prepare_utf8')) {
	function eprepag_phpmailer_prepare_utf8($mail, &$subject, &$body_html, &$body_plain = null)
	{
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$subject = eprepag_phpmailer_to_utf8($subject);
		$body_html = eprepag_phpmailer_to_utf8($body_html);
		$body_plain = eprepag_phpmailer_to_utf8($body_plain);

		$mail->isHTML(true);
	}
}
