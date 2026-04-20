<?php

declare(strict_types=1);

/**
 * translate "" to "á" in a string
 */
function translate_extended_ascii_to_utf(string $s_ext_ascii): string
{
    $s_utf = "";
    for ($i = 0; $i < strlen($s_ext_ascii); $i++) {
        // Our chars go from 128 to 188 ASCII codes and begin with ""
        if (ord($s_ext_ascii[$i]) >= 128) {
            $s_char = get_utf_from_extended_ascii($s_ext_ascii[$i]);
        } else {
            $s_char = $s_ext_ascii[$i];
        }
        $s_utf .= $s_char;
    }
    return $s_utf;
}

/**
 * translate "á" to ""
 */
function translate_utf_to_extended_ascii(string $s_utf): string
{
    $s_ext_ascii = "";
    for ($i = 0; $i < strlen($s_utf); $i++) {
        $s_char = "*";
        // Our chars go from 128 to 188 ASCII codes and begin with ""
        if ($s_utf[$i] === "") {
            $i++;
            $s_char = get_extended_ascii_from_utf($s_utf[$i]);
        } else {
            $s_char = $s_utf[$i];
        }
        $s_ext_ascii .= $s_char;
    }
    return $s_ext_ascii;
}

/**
 * remove special chars and replace with "__" for use in SQL string
 */
function remove_special_chars(string $s_utf): string
{
    $s_ext_ascii = "";
    for ($i = 0; $i < strlen($s_utf); $i++) {
        $s_char = "*";
        // Our chars go from 128 to 188 ASCII codes and begin with ""
        if ($s_utf[$i] === "") {
            $i++;
            $s_char = "__";
        } else {
            $s_char = $s_utf[$i];
        }
        $s_ext_ascii .= $s_char;
    }
    return $s_ext_ascii;
}

/**
 * translate "" to "" (meaning "á")
 */
function get_utf_from_extended_ascii(string $ext_ascii): string
{
    /** @var array<string, int> $chars_ext_ascii_to_utf */
    $chars_ext_ascii_to_utf = [
        '' => 161, '' => 169, '' => 173, '' => 179, '' => 186, '' => 160, '' => 168, '' => 172, '' => 178, '' => 185, '' => 163, '' => 181, '' => 162, '' => 170, '' => 174, '' => 180, '' => 164, '' => 171, '' => 175, '' => 182, '' => 188, '' => 167, '' => 129, '' => 137, '' => 141, '' => 147, '' => 154, '' => 128, '' => 136, '' => 140, '' => 146, '' => 153, '' => 131, '' => 149, '' => 130, '' => 138, '' => 142, '' => 148, '' => 155, '' => 132, '' => 139, '' => 143, '' => 150, '' => 156, '' => 135,
    ];
    return "" . chr($chars_ext_ascii_to_utf[$ext_ascii]);
}

/**
 * translate "" (meaning "á") to ""
 */
function get_extended_ascii_from_utf(string $utf): string
{
    /** @var array<int, string> $chars_utf_to_ext_ascii */
    $chars_utf_to_ext_ascii = [
        161 => '', 169 => '', 173 => '', 179 => '', 186 => '', 160 => '', 168 => '', 172 => '', 178 => '', 185 => '', 163 => '', 181 => '', 162 => '', 170 => '', 174 => '', 180 => '', 164 => '', 171 => '', 175 => '', 182 => '', 188 => '', 167 => '', 129 => '', 137 => '', 141 => '', 147 => '', 154 => '', 128 => '', 136 => '', 140 => '', 146 => '', 153 => '', 131 => '', 149 => '', 130 => '', 138 => '', 142 => '', 148 => '', 155 => '', 132 => '', 139 => '', 143 => '', 150 => '', 156 => '', 135 => '',
    ];
    return $chars_utf_to_ext_ascii[ord($utf)];
}
