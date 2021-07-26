<?php

declare(strict_types=1);

namespace App\Http\Middleware\Shares;

use App\Models\Cluster;
use App\Models\Project;
use Closure;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ShareSelectedProjectToView
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next)
    {
        $projectId = $request->get('project_id');

        $project = Auth::user()->getAttribute('projects')->first();
        $projectName = '';
        $cluster = null;

        if ($projectId === null && $project instanceof Project) {
            $projectId = $project->getAttribute('id');
        }

        if ($project instanceof Project) {
            $projectName = $project->getAttribute('name');
            /** @var  Cluster $cluster */
            $cluster = $project->clusters->first();
        }

        $clusterUrl = '';
        $clusterType = '';

        if ($cluster !== null) {
            $clusterUrl = $cluster->getAttribute('url');
            $clusterType = $cluster->getMorphClass();
        }

        Inertia::share('project_id', $projectId);
        Inertia::share('project_name', $projectName);
        Inertia::share('project_cluster_type', $clusterType);
        Inertia::share('project_cluster_url', $clusterUrl);

        return $next($request);
    }
}
