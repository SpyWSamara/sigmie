<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners;

use App\Events\Cluster\ClusterWasCreated;
use App\Listeners\Cluster\PollClusterState;
use App\Models\Cluster;
use App\Repositories\ClusterRepository;
use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Helpers\NeedsModel;
use Tests\TestCase;

class PollClusterStateTest extends TestCase
{
    use NeedsModel;

    /**
     * @var PollClusterState
     */
    private $listener;

    /**
     * @var ClusterWasCreated
     */
    private $eventMock;

    /**
     * @return ClusterRepository|MockObject
     */
    private $repository;

    /**
     * @var string
     */
    private $domain = 'test-domain.com';

    /**
     * @var integer
     */
    private $clusterId = 0;

    public function setUp(): void
    {
        parent::setUp();

        Event::fake();

        Config::set('services.cloudflare.domain', $this->domain);

        $this->repository = $this->createMock(ClusterRepository::class);
        $this->repository->method('find')->willReturn($this->createCluster());

        $this->eventMock = $this->createEventMock();

        $this->listener = new PollClusterState($this->repository);
    }

    /**
     * @test
     */
    public function handle_make_call_updates_state_on_200_response_and_triggers_cluster_was_booted_event()
    {
        $this->clusterCallWillReturn(true);

        $this->repository->expects($this->once())->method('update')->with(0, ['state' => 'running']);

        $this->listener->handle($this->eventMock);

        Event::assertDispatched(function (\App\Events\Cluster\ClusterWasBooted $event) {
            return $event->clusterId === 0;
        });
    }

    /**
     * @test
     */
    public function handle_make_call_updates_state_on_not_500_response_and_throws_exception()
    {
        $this->expectException(Exception::class);

        $this->clusterCallWillReturn(false);

        $this->listener->handle($this->eventMock);
    }

    /**
     * @test
     */
    public function handle_makes_correct_http_call()
    {
        $this->repository->expects($this->once())->method('find')->with(0);

        Http::shouldReceive('withBasicAuth')->once()->with('foo', 'bar')->andReturnSelf();
        Http::shouldReceive('timeout')->once()->with(3)->andReturnSelf();
        Http::shouldReceive('get')->with("https://baz.{$this->domain}")->andReturnSelf();
        Http::shouldReceive('successful')->once()->andReturn(true);

        $this->listener->handle($this->eventMock);
    }

    /**
     * @test
     */
    public function failed_changes_cluster_status_and_saves()
    {
        $this->repository->expects($this->once())->method('update')->with(0, ['state' => 'failed']);

        $this->listener->failed($this->eventMock, new Exception());

        Event::assertDispatched(function (\App\Events\Cluster\ClusterHasFailed $event) {
            return $event->clusterId === 0;
        });
    }

    /**
     * @test
     */
    public function tries()
    {
        $this->assertEquals(10, $this->listener->tries);
    }

    /**
     * @test
     */
    public function delay_seconds()
    {
        $this->assertEquals(15, $this->listener->delay);
    }

    /**
     * @test
     */
    public function retry_after_seconds()
    {
        $this->assertEquals(15, $this->listener->backoff);
    }

    private function createEventMock()
    {
        $event = $this->createMock(ClusterWasCreated::class);
        $event->clusterId = $this->clusterId;

        return $event;
    }


    private function createCluster()
    {
        $cluster = $this->model(Cluster::class);
        $cluster->method('getAttribute')->willReturnMap([
            ['username', 'foo'],
            ['password', encrypt('bar')],
            ['name', 'baz'],
        ]);

        return $cluster;
    }

    private function clusterCallWillReturn($success)
    {
        Http::shouldReceive('withBasicAuth')->andReturnSelf();
        Http::shouldReceive('timeout')->andReturnSelf();
        Http::shouldReceive('get')->andReturnSelf();
        Http::shouldReceive('successful')->andReturn($success);
    }

    private function responseWithCode(int $code)
    {
        $response = $this->createMock(Response::class);
        $response->method('getStatusCode')->willReturn($code);

        return $response;
    }
}