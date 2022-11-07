<?php

declare(strict_types=1);

namespace Sigmie\Greek\Filter;

use Sigmie\Index\Analysis\TokenFilter\TokenFilter;

use function Sigmie\Functions\name_configs;

class Stemmer extends TokenFilter
{
    public function __construct(string $name = 'greek_stemmer')
    {
        parent::__construct($name);
    }

    public function type(): string
    {
        return 'stemmer';
    }

    public static function fromRaw(array $raw): static
    {
        [$name, $config] = name_configs($raw);

        return new static($name);
    }

    protected function getValues(): array
    {
        return [
            'language' => 'greek',
        ];
    }
}
