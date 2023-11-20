<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CRMDJOB implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $crmd;
    /**
     * Create a new job instance.
     */
    public function __construct($crmd)
    {
        $this->crmd = $crmd;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $email = $this->crmd->email;
        
        Mail::to($email)->send(new ProcessCAADMail($this->crmd));
    }
}
