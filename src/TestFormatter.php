<?php

namespace App;

class TestFormatter
{
    private const MAPPING = [
        'COU' => '@cou',
        'INT' => '@int',
        'AD' => '@ad',
        'MagiePhys' => '@mphy',
        'MagiePsy' => '@mpsy',
    ];

    public function format(string $test): string
    {
        return str_replace(array_keys(self::MAPPING), array_values(self::MAPPING), $test);
    }
}
