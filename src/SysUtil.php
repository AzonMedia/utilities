<?php
declare(strict_types=1);


namespace Azonmedia\Utilities;

/**
 * Class SysUtil
 * @package Azonmedia\Utilities
 * Currently works only on Linux.
 * If a method can not determine the info returns NULL.
 */
abstract class SysUtil
{
    /**
     * Returns the memory in kB
     * @return int|null
     */
    public static function get_system_ram() : ?int
    {
        $fh = fopen('/proc/meminfo','r');
        $mem = NULL;
        while ($line = fgets($fh)) {
            $pieces = array();
            if (preg_match('/^MemTotal:\s+(\d+)\skB$/', $line, $pieces)) {
                $mem = $pieces[1];
                break;
            }
        }
        fclose($fh);
        return $mem;
    }

    public static function get_cpu_model() : ?string
    {
        $ret = NULL;
        $contents = file_get_contents('/proc/cpuinfo');
        preg_match('/model name.*: (.*)/', $contents, $matches);
        if (isset($matches[1])) {
            $ret = $matches[1];
        }
        return $ret;
    }

    /**
     * Returns the number of CPU threads.
     * @return int|null
     */
    public static function get_cpu_threads() : ?int
    {
        $ret = NULL;
        $contents = file_get_contents('/proc/cpuinfo');
        preg_match('/siblings.*: ([0-9]*)/', $contents, $matches);
        if (isset($matches[1])) {
            $ret = $matches[1];
        }
        return $ret;
    }

    /**
     * Returns the physical number of CPU cores.
     * @return int|null
     */
    public static function get_cpu_cores() : ?int
    {
        $ret = NULL;
        $contents = file_get_contents('/proc/cpuinfo');
        preg_match('/cores.*: ([0-9]*)/', $contents, $matches);
        if (isset($matches[1])) {
            $ret = $matches[1];
        }
        return $ret;
    }

    /**
     * Returns TRUE if hyperthreading is enabled.
     * @return bool|null
     */
    public static function hyperthreading_enabled() : ?bool
    {
        $ret = NULL;
        $cpu_threads = self::get_cpu_threads();
        $cpu_cores = self::get_cpu_cores();
        if ($cpu_threads && $cpu_cores) {
            if ($cpu_cores < $cpu_threads) {
                $ret = TRUE;
            } else {
                $ret = FALSE;
            }
        }
        return $ret;
    }

    /**
     * Returns a string with basic host info: CPU model, cores, threads, RAM
     * @return string
     */
    public static function get_basic_sysinfo() : string
    {
        $str = '';
        $ram = self::get_system_ram();
        $str .= 'CPU: '.(self::get_cpu_model() ?? 'Unknown');
        $str .= ' Cores: '.(self::get_cpu_cores() ?? 'Unknown');
        $str .= ' Threads: '.(self::get_cpu_threads() ?? 'Unknown');
        $str .= ' Memory: '.($ram ? round( $ram / (1024 * 1024), 2 ).'GB' : 'Unknown');
        return $str;
    }
}