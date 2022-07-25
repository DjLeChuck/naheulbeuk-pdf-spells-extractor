<?php

namespace App;

use App\DTO\Spell;

class SpellFormatter
{
    private const SPELL_TYPES = [
        'generaliste',
        'combat',
        'domestique',
        'feu',
        'metamorphose',
        'thermodynamique',
        'invocation',
        'necromancie',
        'illusion',
        'eau',
        'terre',
        'air',
        'tzinntch',
        'pr-niourgl',
        'pr-dlul',
        'pr-youclidh',
        'pr-slanoush',
        'pr-adathie',
        'pa-niourgl',
        'pa-slanoush',
        'pa-dlul',
        'pa-braav',
        'pa-khornettoh',
    ];

    private string $content;
    /** @var string[] */
    private array $lines;

    public function __construct(string $content, string $type)
    {
        $this->content = $content;
        $this->lines = array_filter(explode("\n", $this->content));
        $this->spell = new Spell();

        $this->setType($type);
    }

    public function format(): Spell
    {
        $this->setLevel();
        $this->setName();
        $this->setCost();
        $this->setCastingTime();
        $this->setSpellDuration();
        $this->setTest();
        $this->setScope();
        $this->setDamage();
        $this->setDescription();

        return $this->spell;
    }

    private function setType(string $type): void
    {
        // @todo selon le libellé du PDF
        $this->spell->type = current(self::SPELL_TYPES);
    }

    private function setLevel(): void
    {
        $levelStr = array_shift($this->lines);
        $matches = [];
        preg_match('`^Niveau (\d+)$`', $levelStr, $matches);

        $this->spell->level = (int) $matches[1];
    }

    private function setName(): void
    {
        $this->spell->name = array_shift($this->lines);
    }

    private function setCost(): void
    {
        $matches = [];
        preg_match('`Coût(?: d’invocation)? : (.*)`u', $this->content, $matches);

        if (empty($matches)) {
            throw new \InvalidArgumentException('Can not determine spell cost.');
        }

        $this->spell->cost = $matches[1];
    }

    private function setCastingTime(): void
    {
        $matches = [];
        preg_match('`Durée (?:d’incantation|du rituel|d’invocation) : (.*)`u', $this->content, $matches);

        if (!empty($matches)) {
            $this->spell->castingTime = $matches[1];
        }
    }

    private function setSpellDuration(): void
    {
        $matches = [];
        preg_match('`Durée du sort : (.*)`u', $this->content, $matches);

        if (!empty($matches)) {
            $this->spell->spellDuration = $matches[1];
        }
    }

    private function setTest(): void
    {
        $matches = [];
        preg_match('`Épreuve(?: d’appel)? : (.*)`u', $this->content, $matches);

        if (empty($matches)) {
            throw new \InvalidArgumentException('Can not determine spell test.');
        }

        $this->spell->test = $matches[1];
    }

    private function setScope(): void
    {
        $matches = [];
        preg_match('`Portée : (.*)`u', $this->content, $matches);

        if (!empty($matches)) {
            $this->spell->scope = $matches[1];
        }
    }

    private function setDamage(): void
    {
        $matches = [];
        preg_match('`Dégâts : (.*)`u', $this->content, $matches);

        if (!empty($matches)) {
            $this->spell->damage = $matches[1];
        }
    }

    public function setDescription(): void
    {
        $description = '<p>';

        while (!\in_array(current($this->lines), ['Usages :', 'Caractéristiques :'], true)) {
            $description .= ' '.array_shift($this->lines);
        }

        $description .= <<<HTML
</p>
<hr />
<p>
HTML;

        $description .= '<strong>'.array_shift($this->lines).'</strong></p>';

        while (str_starts_with(current($this->lines), '- ')) {
            $description .= sprintf('<p>%s</p>', array_shift($this->lines));
        }

        $this->spell->description = $description;
    }
}
