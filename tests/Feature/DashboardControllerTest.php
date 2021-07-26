<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Subscription;
use Sigmie\Base\Index\Actions as IndexActions;
use Sigmie\Base\Index\Index;
use Sigmie\Testing\Laravel\ClearIndices;
use Tests\Helpers\WithRunningExternalCluster;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use ClearIndices, WithRunningExternalCluster, IndexActions;

    /**
     * @test
     */
    public function render_inertia_dashboard_with_id()
    {
        $this->withRunningExternalCluster();

        $this->actingAs($this->user);

        $route = route('dashboard', ['project' => $this->project->id]);

        $this->assertInertiaViewExists('dashboard/dashboard');

        $this->get($route)->assertInertia(
            'dashboard/dashboard',
            ['clusterId' => $this->cluster->id]
        );
    }

    /**
     * @test
     */
    public function can_not_see_dashboard_if_not_owning_the_project()
    {
        $this->withRunningExternalCluster();

        $user = Subscription::factory()->create()->billable;

        $this->actingAs($user);

        $route = route('dashboard', ['project' => $this->project->id]);

        $this->get($route)->assertForbidden();
    }

    /**
     * @test
     */
    public function dashboard_data_returns_cluster_info()
    {
        $this->withRunningExternalCluster();

        $this->actingAs($this->user);

        $this->setHttpConnection($this->cluster->newHttpConnection());

        $index = new Index($this->testId() . '_foo');

        $this->createIndex($index);

        $index->setAlias('baz');

        $response = $this->get(route('dashboard.data', ['project' => $this->project->id]));

        $json = $response->json();

        $expected = [
            'clusterState' => 'running',
            'clusterId' => $this->cluster->id,
            'indices' => [
                [
                    'aliases' => ['baz'],
                    'name' => $this->testId() . '_foo',
                    'size' => '230b',
                    'docsCount' => '0',
                ]
            ],
            'clusterInfo' => [
                'health' => 'yellow',
                'nodesCount' => 1,
                'name' => 'docker-cluster',
            ]
        ];

        $this->assertEquals($expected['clusterState'], $json['clusterState']);
        $this->assertEquals($expected['clusterId'], $json['clusterId']);
        $this->assertContains($expected['indices'][0], $json['indices']);
        $this->assertEquals($expected['clusterInfo'], $json['clusterInfo']);
    }
}
