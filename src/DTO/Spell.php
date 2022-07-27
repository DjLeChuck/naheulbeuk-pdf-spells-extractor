<?php

namespace App\DTO;

class Spell
{
    public int $level;
    public string $name;
    public string $type;
    public string $description;
    public string $cost;
    public ?string $castingTime = null;
    public ?string $spellDuration = null;
    public string $test;
    public ?string $scope = null;
    public ?string $damage = null;
}
