<?php

namespace Ni\Elastic\Index\Action;

use Ni\Elastic\Contract\Subscribable;
use Elasticsearch\Client as Elasticsearch;
use Ni\Elastic\Contract\Action;

class Create implements Action, Subscribable
{
    public function prepare($data): array
    {
        $params = [
            'index' => $data->getIdentifier()
        ];

        return $params;
    }

    public function beforeEvent(): string
    {
        return 'before.index.create';
    }

    public function execute(Elasticsearch $elasticsearch, array $params): array
    {
        return $elasticsearch->indices()->create($params);
    }

    public function afterEvent(): string
    {
        return 'after.index.create';
    }
}
