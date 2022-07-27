<?php

namespace App;

class SpellTypeGuesser
{
    private const SPELL_TYPES = [
        'Gen' => 'generaliste',
        'Combat' => 'combat',
        // 'domestique',
        'Feu' => 'feu',
        'Meta' => 'metamorphose',
        'THERMO' => 'thermodynamique',
        'Invoc' => 'invocation',
        'Necro' => 'necromancie',
        // 'illusion',
        'EauGlace' => 'eau',
        'Terre' => 'terre',
        'Air' => 'air',
        'Tzinntch' => 'tzinntch',
        // 'pr-niourgl',
        // 'pr-dlul',
        // 'pr-youclidh',
        // 'pr-slanoush',
        // 'pr-adathie',
        // 'pa-niourgl',
        // 'pa-slanoush',
        // 'pa-dlul',
        // 'pa-braav',
        // 'pa-khornettoh',
    ];

    public function guess(string $filename): string
    {
        foreach (self::SPELL_TYPES as $namePart => $type) {
            if (sprintf('Grimoire-Magie-%s-BLANC.pdf', $namePart) === $filename) {
                return $type;
            }
        }

        throw new \InvalidArgumentException(sprintf('Impossible de détecter le type du sort du fichier %s', $filename));
    }
}
