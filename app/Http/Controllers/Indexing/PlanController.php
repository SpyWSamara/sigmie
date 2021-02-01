<?php

declare(strict_types=1);

namespace App\Http\Controllers\Indexing;

use App\Http\Requests\Indexing\StorePlan;
use App\Http\Requests\UpdatePlan;
use App\Models\FileType;
use App\Models\IndexingPlan;
use App\Models\IndexingPlanDetails;
use PhpParser\Node\Stmt\Catch_;
use Throwable;

class PlanController extends \App\Http\Controllers\Controller
{
    public function __construct()
    {
        $this->authorizeResource(IndexingPlan::class, 'plan');
    }

    public function store(StorePlan $request)
    {
        $validated = $request->validated();

        $plan = new IndexingPlan([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'cluster_id' => $validated['cluster_id'],
        ]);


        if ($validated['type']['type'] === 'file') {
            $type = FileType::create([
                'location' => $validated['type']['location'],
                'index_alias' => $validated['type']['index_alias']
            ]);
        }

        $plan->type()->associate($type)->save();

        return redirect(route('indexing.indexing'));
    }

    public function update(UpdatePlan $request, IndexingPlan $plan)
    {
        $validated = $request->validated();

        $plan->fill(
            [
                'name' => $validated['name'],
                'description' => $validated['description'],
            ]
        );

        $plan->type->delete();

        if ($validated['type']['type'] === 'file') {
            $type = FileType::create([
                'location' => $validated['type']['location'],
                'index_alias' => $validated['type']['index_alias']
            ]);
        }

        $plan->type()->associate($type)->save();

        return redirect(route('indexing.indexing'));
    }

    public function destroy(IndexingPlan $plan)
    {
        $plan->delete();

        return redirect(route('indexing.indexing'));
    }
}
