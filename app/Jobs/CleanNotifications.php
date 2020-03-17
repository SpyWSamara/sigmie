<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Notification;

class CleanNotifications implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Remove notifications which are older than
     * one month
     *
     * @return void
     */
    public function handle()
    {
        $lastMonth = Carbon::now()->subMonth()->toDateString();

        DB::table('notifications')->where('created_at', '<', $lastMonth)->delete();
    }
}