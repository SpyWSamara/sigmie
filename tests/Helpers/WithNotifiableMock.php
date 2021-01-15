<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Models\User;
use Illuminate\Notifications\Notifiable;
use PHPUnit\Framework\MockObject\MockObject;

trait WithNotifiableMock
{
    private $notifiableMock;

    /**
     * @return MockObject|User
     */
    public function withNotifiableMock()
    {
        $methods = [
            'getKey', 'notify', 'getAttribute'
        ];

        $this->notifiableMock = $this->getMockBuilder(Notifiable::class)->setMethods($methods)->getMockForTrait();
    }
}