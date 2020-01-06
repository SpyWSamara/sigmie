<?php

namespace Tests\Unit;

use App\Http\Requests\StoreNewsletterSubscription;
use Tests\TestCase;

class StoreNewsletterSubscriptionTest extends TestCase
{
    /**
     * Store Newsletter subscription request
     *
     * @var StoreNewsletterSubscription
     */
    private $request;

    /**
     * Setup method
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new StoreNewsletterSubscription();
    }

    /**
     * @test
     */
    public function has_email_validation(): void
    {
        $this->assertArrayHasKey('email', $this->request->rules());
    }

    /**
     * @test
     */
    public function email_is_required(): void
    {
        $this->assertContains('required', $this->request->rules()['email']);
    }

    /**
     * @test
     */
    public function email_is_email(): void
    {
        $this->assertContains('email:rfc,dns', $this->request->rules()['email']);
    }
}