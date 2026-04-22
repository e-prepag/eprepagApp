<?php

if (!function_exists('writeIfPossible')) {
    function writeIfPossible($stream, $content)
    {
        if (is_resource($stream)) {
            fwrite($stream, $content);
        }
    }
}

if (!function_exists('closeIfPossible')) {
    function closeIfPossible($stream)
    {
        if (is_resource($stream)) {
            fclose($stream);
        }
    }
}

if (!function_exists('writeFileIfPossible')) {
    function writeFileIfPossible($path, $content, $mode = 'a+')
    {
        $stream = @fopen($path, $mode);
        if ($stream === false) {
            return false;
        }

        writeIfPossible($stream, $content);
        closeIfPossible($stream);
        return true;
    }
}

if (!function_exists('writeLinesIfPossible')) {
    function writeLinesIfPossible($path, array $lines, $mode = 'a+')
    {
        $stream = @fopen($path, $mode);
        if ($stream === false) {
            return false;
        }

        foreach ($lines as $line) {
            writeIfPossible($stream, $line . "\n");
        }

        closeIfPossible($stream);
        return true;
    }
}
