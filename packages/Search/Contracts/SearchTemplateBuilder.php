<?php

declare(strict_types=1);

namespace Sigmie\Search\Contracts;

interface SearchTemplateBuilder extends SearchBuilder, SearchqueryBuilder
{
    public function filterable(bool $filterable): static;

    public function sortable(bool $sortable): static;
}
