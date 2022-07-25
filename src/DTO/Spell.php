<?php

namespace App\DTO;

class Spell
{
    public int $level;
    public string $name;
    public string $type;
    public string $description;
    public string $cost;
    public string $castingTime;
    public ?string $spellDuration;
    public string $test;
    public ?string $scope;
    public ?string $damage;
}
