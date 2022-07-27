<?php

namespace App;

use App\DTO\Spell;

class SpellFormatter
{
    private const NEW_LINE_TERMS = [
        '- ', 'Usages :', 'Caractéristiques :', 'Durée d’incantation :', 'Durée d’invocation :',
        'Coût', 'Coût du rituel :', 'Coût d’invocation :', 'Durée du sort :', 'Dégâts :',
        'Épreuve :', 'Épreuve d’appel :', 'Épreuve de contrôle :', 'Épreuve de révocation :',
        'Portée :', 'Mot de pouvoir :', 'Notes diverses :', 'Échec critique :', 'Réussite critique :',
        'Effets :', 'Bénéfices :', 'Restrictions :',
    ];

    private string $content;
    /** @var string[] */
    private array $lines;

    public function __construct(string $content, string $type)
    {
        $this->content = $content;
        $this->lines = array_filter(explode("\n", $this->content));
        $this->spell = new Spell();
        $this->spell->type = $type;
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

    private function setLevel(): void
    {
        $levelStr = array_shift($this->lines);
        $matches = [];
        preg_match('`^Niveau (\d+)$`', $levelStr, $matches);

        if (!isset($matches[1])) {
            throw new \InvalidArgumentException('Impossible de déterminer le niveau du sort.');
        }

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
            throw new \InvalidArgumentException('Impossible de déterminer le coût du sort.');
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
            throw new \InvalidArgumentException('Impossible de déterminer l\'épreuve du sort.');
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

    private function setDescription(): void
    {
        $description = '<p>';

        // Retrait des 2 dernières lignes (caractère vide + livre et n° de page)
        array_pop($this->lines);
        array_pop($this->lines);

        foreach ($this->lines as $line) {
            if ($this->needNewLine($line)) {
                $description .= "</p>\n<p>";
            } elseif (!str_starts_with($line, ' ')) {
                $description .= ' ';
            }

            $description .= $line;
        }

        $terms = self::NEW_LINE_TERMS;
        array_shift($terms);
        $patterns = array_map(static fn(string $term) => '/'.$term.'/', $terms);
        $description = preg_replace($patterns, '<strong>$0</strong>', $description);

        // Séparation entre description courte et le reste
        $description = preg_replace('/<p><strong>(Usages|Caractéristiques) :<\/strong><\/p>/', "<hr />\n$0", $description);

        $this->spell->description = $description;
    }

    private function needNewLine(string $line): bool
    {
        foreach (self::NEW_LINE_TERMS as $term) {
            if (str_starts_with($line, $term)) {
                return true;
            }
        }

        return false;
    }
}
