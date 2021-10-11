<?php

declare(strict_types=1);

namespace Sigmie\Base\Search\Queries\Term;

use Sigmie\Base\Search\Queries\QueryClause;

class Wildcard extends QueryClause
{
    public function __construct(
        protected string $field,
        protected string $value
    ) {
    }

    public function toRaw(): array
    {
        return [
            'wildcard' => [
                $this->field => [
                    'value' => $this->value
                ]
            ]
        ];
    }
}