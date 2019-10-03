<?php
declare(strict_types=1);

namespace Azonmedia\Utilities;


abstract class TypesUtil
{

    public const TYPES_SYNONYMS = [
        ['int', 'integer'],
        ['float', 'double'],
        ['bool', 'boolean'],
        ['', 'mixed'],//pseudotype
    ];

    public static function validate_type( /* mixed */ $value, string $expected_type) : bool
    {
        $value_type = gettype($value);

        if ($expected_type==='' || $expected_type==='mixed') {
            return TRUE;
        }

        if (strtolower($value_type) === strtolower($expected_type)) {
            return TRUE;
        }

        foreach (self::TYPES_SYNONYMS as $synonyms) {
            if (in_array( strtolower($value_type), array_map('strtolower', $synonyms)) && in_array( strtolower($expected_type), array_map('strtolower',$synonyms) ) ) {
                return TRUE;
            }
        }

        return FALSE;
    }
}