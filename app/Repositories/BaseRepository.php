<?php

namespace App\Repositories;

use App\Contracts\Repository;
use App\Models\Model;

abstract class BaseRepository implements Repository
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function find(int $id): Model
    {
        return $this->model->find($id);
    }
}