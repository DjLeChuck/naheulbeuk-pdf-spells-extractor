<?php

namespace App;

use App\DTO\Spell;
use Symfony\Component\Serializer\SerializerInterface;

class SpellSerializer
{
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
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
                'cout' => $spell->cost,
                'incantation' => $spell->castingTime,
                'duree' => $spell->spellDuration,
                'portee' => $spell->cost,
                'epreuve' => $spell->test,
                'degat' => $spell->damage,
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
}
