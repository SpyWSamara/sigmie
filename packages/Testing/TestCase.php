<?php

declare(strict_types=1);

namespace Sigmie\Testing;

use Sigmie\Sigmie;
use Sigmie\Base\Index\Actions;

class TestCase extends \PHPUnit\Framework\TestCase
{
    use Testing, Actions, Assertions, TestIndex;

    protected Sigmie $sigmie;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupTestConnection();

        $this->createTestIndex();

        $this->sigmie = new Sigmie($this->httpConnection);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}