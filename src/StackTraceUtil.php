<?php
declare(strict_types=1);

namespace Azonmedia\Utilities;


class StackTraceUtil
{
    /**
     * Looks backwards in the stack for the provided class and/or method name and returns the data
     * Optionally accepts a third argument with backtrace data
     * @param string $class
     * @param string $method
     * @param array $bt Backtrace data
     * @param int $frame_offset To look for frames after the found one (positive number) or frames before it (negative number). Defaults to 0 (to return the frame found).
     * @return array Returns empty array if not found
     * @example k::get_stack_frame_by('classname','methodname', $exception->getTrace())
     * If not provided it gets the current backtrace internally.
     */
    public static function get_stack_frame_by(string $class = '', string $method = '', array $bt = array(), int $frame_offset = 0): array
    {
        if (!$class && !$method) {
            throw new \RuntimeException(sprintf('Neither $class nor $method was provided to get_stack_frame_by.'));
        }
        if (!is_string($class)) {
            throw new \RuntimeException('The provided $class to get_stack_frame_by must be string. Instead "%s" was provided.', gettype($class));
        }
        if (!is_string($method)) {
            throw new \RuntimeException('The provided $method to get_stack_frame_by must be string. Instead "%s" was provided.', gettype($method));
        }
        if (!$bt) {
            $bt = self::get_backtrace();
        }
        $ret = array();

        //foreach ($bt as $frame) {
        for ($aa = 0; $aa < count($bt); $aa++) {
            $frame = $bt[$aa];
            if ($class && $method) {
                if (isset($frame['class']) && strtolower($frame['class']) == strtolower($class) && isset($frame['function']) && strtolower($frame['function']) == strtolower($method)) {
                    //this is wrong as it will return the frame found if the offset is invalid
                    //instead an empty array should be returned even if the offset is invalid
                    // if ($frame_offset && isset($bt[$aa+$frame_offset])) {
                    //     $ret = $bt[$aa+$frame_offset];
                    // } else {
                    //     $ret = $frame;
                    // }
                    // break;
                    if (isset($bt[$aa + $frame_offset])) {
                        $ret = $bt[$aa + $frame_offset];
                        break;
                    }
                }
            } elseif ($class) {
                if (isset($frame['class']) && strtolower($frame['class']) == strtolower($class)) {
                    if (isset($bt[$aa + $frame_offset])) {
                        $ret = $bt[$aa + $frame_offset];
                        break;
                    }
                }
            } elseif ($method) {
                if (isset($frame['function']) && strtolower($frame['function']) == strtolower($method)) {
                    if (isset($bt[$aa + $frame_offset])) {
                        $ret = $bt[$aa + $frame_offset];
                        break;
                    }
                }
            } else {
                //nether class or method was provided
            }
        }

        return $ret;
    }

    /**
     * Gets the backtrace
     *
     * @return iterable
     */
    public static function get_backtrace(): iterable
    {
        ini_set('memory_limit', '2048M');
        return self::simplify_trace(debug_backtrace());
    }

    /**
     * Removes some data from the trace to simplify it
     *
     * @param array $debug_trace
     * @return iterable
     */
    public static function simplify_trace(array $debug_trace): iterable
    {
        foreach ($debug_trace as &$call) {
            unset($call['object']);
            unset($call['args']);
        }
        return $debug_trace;
    }
}