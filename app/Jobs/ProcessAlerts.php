<?php

namespace App\Jobs;

use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAlerts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $alertId,
        public string $eventType // opened|resolved
    ) {
        $this->onConnection('sqs');
        $this->onQueue('sentinel-alerts');
    }

    public function handle(): void
    {
        $alert = Alert::find($this->alertId);

        if (!$alert) {
            return;
        }

        //This is where alerts from AWS SQS would be processed. I am going to leave this here and avoid emails.
        
        // Do side effects here:
        // - send email/slack
        // - push to another system
    }

    public function backoff(): array
    {
        return [10, 30, 60];
    }
}