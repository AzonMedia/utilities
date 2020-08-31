<?php

declare(strict_types=1);

namespace Azonmedia\Utilities;

use Azonmedia\Translator\Translator as t;

abstract class FilesUtil
{

    /**
     * Validates the file/dir and returns an error string if there is an issue, otherwise null.
     * @param string $file_path
     * @param bool $is_writeable
     * @param bool $is_dir
     * @param string $arg_name
     * @return null|string If there is an issue an error message will be returned, null otherwise
     */
    public static function file_error(string $file_path, bool $is_writeable = false, bool $is_dir = false, string $arg_name = 'file'): ?string
    {

        if ($arg_name === 'file' && $is_dir) {
            $arg_name = 'directory';
        }
        if (!$file_path) {
            return sprintf(t::_('No %1$s argument provided.'), $arg_name);
        }
        if (!file_exists($file_path)) {
            return sprintf(t::_('The provided %1$s %2$s does not exist.'), $arg_name, $file_path);
        }
        if (!is_readable($file_path)) {
            return sprintf(t::_('The provided %1$s %2$s is not readable.'), $arg_name, $file_path);
        }
        if ($is_writeable && !is_writeable($file_path)) {
            return sprintf(t::_('The provided %1$s %2$s is not writeable.'), $arg_name, $file_path);
        }
        if ($is_dir && !is_dir($file_path)) {
            return sprintf(t::_('The provided %1$s %2$s is a file.'), $arg_name, $file_path);
        } elseif (!$is_dir && is_dir($file_path)) {
            return sprintf(t::_('The provided %1$s %2$s is a directory.'), $arg_name, $file_path);
        }
        return null;
    }

    /**
     * Deletes a directory recursively (including hidden files)
     * @param string $dir
     */
    public static function rmdir(string $dir): void
    {
        $files = array_diff( scandir($dir), ['.','..'] );
        foreach ($files as $file) {
            if (is_dir($dir.'/'.$file)) {
                self::rmdir($dir.'/'.$file);
            } else {
                unlink($dir.'/'.$file);
            }
        }
        rmdir($dir);
    }

    /**
     * Removes all files from a directory.
     * This is done by first deleting recursively the directory (@see self::rmdir()) and then recreating it
     * @param string $dir
     */
    public static function empty_dir(string $dir): void
    {
        self::rmdir($dir);
        mkdir($dir);
    }
}