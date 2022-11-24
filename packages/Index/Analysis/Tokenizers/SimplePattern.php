<?php

declare(strict_types=1);

namespace Sigmie\Index\Analysis\Tokenizers;

use function Sigmie\Functions\name_configs;
use Sigmie\Shared\Name;

class SimplePattern extends Tokenizer
{
    use Name;

    public function __construct(
        public readonly string $name,
        protected string $pattern,
        protected null|string $flags = null
    ) {
    }

    public static function fromRaw(array $raw): static
    {
        [$name, $config] = name_configs($raw);

        $flags = $config['flags'] ?? null;

        return new static($name, $config['pattern'], $flags);
    }

    public function toRaw(): array
    {
        $res = [
            $this->name => [
                'type' => 'simple_pattern',
                'pattern' => $this->pattern,
            ],
        ];

        if (is_null($this->flags)) {
            return $res;
        }

        $res[$this->name]['flags'] = $this->flags;

        return $res;
    }
}
