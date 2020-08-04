<?php

declare(strict_types=1);

namespace Azonmedia\Utilities;

use InvalidArgumentException;

abstract class SourceUtil
{
    public static function check_syntax(string $file_path, ?string &$_error = null): bool
    {
        self::validate_file_path($file_path);
        exec("php -l {$file_path}", $output, $exit_code);
        if ($exit_code === 0) {
            return true;
        }
        
        $_error = $output[1];//the second line holds the actual error
        return false;
    }

    public static function get_file_namespace(string $file_path): ?string
    {
        $ret = null;
        self::validate_file_path($file_path);
        $lines = file($file_path);
        foreach ($lines as $line) {
            if (stripos(trim($line), 'namespace') === 0) { //starts with namespace
                if (preg_match('/namespace(.*);/', $line, $matches)) {
                    $ret = trim($matches[1]);
                }
            }
        }
        return $ret;
    }

    protected static function validate_file_path(string $file_path): void
    {
        if (!$file_path) {
            throw new InvalidArgumentException(sprintf('No file_path provided.'));
        }
        if (!file_exists($file_path)) {
            throw new InvalidArgumentException(sprintf('The provided file_path %1$s does not exist.', $file_path));
        }
        if (!is_readable($file_path)) {
            throw new InvalidArgumentException(sprintf('The provided file_path %1$s is not readable.', $file_path));
        }
        if (!is_file($file_path)) {
            throw new InvalidArgumentException(sprintf('The provided file_path %1$s is not a file.', $file_path));
        }
    }
}