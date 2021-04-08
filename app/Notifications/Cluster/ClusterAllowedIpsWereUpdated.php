<?php

declare(strict_types=1);

namespace App\Notifications\Cluster;

use App\Models\User;
use App\Notifications\UserNotification;
use Illuminate\Bus\Queueable;

class ClusterAllowedIpsWereUpdated extends UserNotification
{
    use Queueable;

    public function __construct(public string $projectName)
    {
    }

    /**
     * @param User $notifiable
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Authorized Addresses',
            'body' => "Your cluster's <b>addresses</b> were updated.",
            'project' => $this->projectName
        ];
    }
}