<?php

namespace App;

use App\DTO\Spell;
use Symfony\Component\Serializer\SerializerInterface;

class SpellSerializer
{
    private SerializerInterface $serializer;
    private TestFormatter $testFormatter;

    public function __construct(SerializerInterface $serializer, TestFormatter $testFormatter)
    {
        $this->serializer = $serializer;
        $this->testFormatter = $testFormatter;
    }

    public function serialize(Spell $spell): string
    {
        return $this->serializer->serialize([
            '_id' => $this->generateId(),
            'name' => $spell->level.'-'.$spell->name,
            'type' => 'sort',
            'img' => 'icons/svg/book.svg',
            'data' => [
                'description' => $spell->description,
                'img' => '',
                'spellLevel' => $spell->level,
                'type' => $spell->type,
                'cout' => $this->lower($spell->cost),
                'incantation' => $this->lower($spell->castingTime ?? '-'),
                'duree' => $this->lower($spell->spellDuration ?? '-'),
                'portee' => $this->lower($spell->scope ?? '-'),
                'epreuve' => $this->getConvertedTest($spell->test),
                'degat' => $this->lower($spell->damage ?? '-'),
                'effet' => '',
                'name1' => '',
                'name2' => '',
                'name3' => '',
                'name4' => '',
                'name5' => '',
                'epreuve1' => '',
                'epreuve2' => '',
                'epreuve3' => '',
                'epreuve4' => '',
                'epreuve5' => '',
                'jet1' => '',
                'jet2' => '',
                'jet3' => '',
                'jet4' => '',
                'jet5' => '',
                'epreuvecustom' => false,
            ],
            'effects' => [],
            'folder' => null,
            'sort' => 0,
            'permission' => [
                'default' => 0,
            ],
            'flags' => []
        ], 'json');
    }

    private function generateId(): string
    {
        return mb_substr(str_replace('.', '', uniqid('', true)), 0, 16);
    }

    private function getConvertedTest(?string $test): string
    {
        if (null === $test || 'non' === $this->lower($test)) {
            return '-';
        }

        return $this->testFormatter->format($test);
    }

    public function lower(string $value): string
    {
        return mb_convert_case($value, MB_CASE_LOWER);
    }
}
