<?php
declare(strict_types=1);

namespace Azonmedia\Utilities;

use Azonmedia\Patterns\ScopeReference;

abstract class StackTraceUtil
{

    /**
     * Returns the stackframe by the provided index.
     * Returns an empty array if not found.
     * @param int $index
     * @return array
     */
    public static function get_stack_frame(int $index) : array
    {
        $bt = self::get_backtrace();
        $ret = [];
        if (isset($bt[$index])) {
            $ret = $bt[$index];
        }
        return $ret;
    }

    /**
     * @param int $frame
     * @return array
     */
    public static function get_caller(int $frame = 3) : array
    {
        $bt = self::get_backtrace();
        $class = $bt[$frame]['class'] ?? NULL;
        $method = $bt[$frame]['function'] ?? NULL;
        return [$class, $method];
    }

    /**
     * Validates the caller and throws exception if the caller is not the expected one.
     * Can be used to protect public methods.
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @param string $expected_class
     * @param string $expected_method
     * @param bool $throw_exception If set to FALSE then boolean result will be returned instead.
     * @return bool
     */
    public static function validate_caller(string $expected_class, string $expected_method, $throw_exception = TRUE) : bool
    {

        if (!$expected_class && !$expected_method) {
            throw new \InvalidArgumentException(sprintf('%s() expects at least one of the arguments to be provided.'));
        }

        list($class, $method) = self::get_caller(4);

        $ret = TRUE;
        if ($expected_class && !is_a($class, $expected_class, TRUE)) {
            $ret = FALSE;
        }
        if ($expected_method && $expected_method !== $method) {
            $ret = FALSE;
        }
        if (!$ret && $throw_exception) {
            //print_r(self::get_backtrace());
            $frame = self::get_stack_frame(3);
            $called_from_str =  ( $expected_class ? : '*' ).'::'.( $expected_method ? : '*' ).'()';
            throw new \BadMethodCallException(sprintf('%s::%s() can be called only from %s. It was called from %s::%s().', $frame['class'], $frame['function'], $called_from_str, $class, $method ));
        }
        return $ret;
    }

    /**
     * Looks backwards in the stack for the provided class and/or method name and returns the data
     * Optionally accepts a third argument with backtrace data
     * @param string $class
     * @param string $method
     * @param array $bt Backtrace data
     * @param int $frame_offset To look for frames after the found one (positive number) or frames before it (negative number). Defaults to 0 (to return the frame found).
     * @return array Returns NULL if not found
     * @example k::get_stack_frame_by('classname','methodname', $exception->getTrace())
     * If not provided it gets the current backtrace internally.
     */
    public static function get_stack_frame_by(string $class = '', string $method = '', array $bt = array(), int $frame_offset = 0): array
    {
        if (!$class && !$method) {
            throw new \RuntimeException(sprintf('Neither $class nor $method was provided to %s.'), __METHOD__);
        }
        if (!is_string($class)) {
            throw new \RuntimeException('The provided $class to %s must be string. Instead "%s" was provided.', __METHOD__, gettype($class));
        }
        if (!is_string($method)) {
            throw new \RuntimeException('The provided $method to %s must be string. Instead "%s" was provided.', __METHOD__, gettype($method));
        }
        if (!$bt) {
            $bt = self::get_backtrace();
        }
        $ret = [];

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
        //ini_set('memory_limit', '2048M');
        return self::simplify_trace(debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS));
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

    /**
     * Checks the provided backtrace for a frame containing @see ScopeReference::__destruct() and if found checks was it triggered by a throw statement.
     * @param array $backtrace
     * @return bool
     */
    public static function check_stack_for_scope_ref_destruct_due_throw(array $backtrace = []): bool
    {
        $ret = FALSE;
        if (!$backtrace) {
            $backtrace = self::get_backtrace();
        }
        foreach ($backtrace as $frame) {
            $class = $frame['class'] ?? '';
            $function = $frame['function'] ?? '';
            if ($class && is_a($class, ScopeReference::class, TRUE) && $function === '__destruct') {
                //a scope reference destructor is found
                //check the given line for a throw statement
                $lines = file($frame['file']);
                $line = trim($lines[$frame['line'] - 1]);
                if (stripos($line, 'throw') === 0) {
                    $ret = TRUE;
                }
                break;
            }
        }
        return $ret;
    }
}