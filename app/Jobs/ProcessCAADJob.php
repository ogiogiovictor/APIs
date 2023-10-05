<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;
use App\Mail\ProcessCAADMail;
use App\Models\User;
use App\Services\GeneralService;


class ProcessCAADJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $process;
    /**
     * Create a new job instance.
     */
    public function __construct($process)
    {
        $this->process = $process;
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
       // $email = $this->process->email;
       //Get email of the User
       //$email = $this->getUser($this->process->region, $this->process->business_hub);
        $levelFormat = $this->process->region.",".$this->process->business_hub.",";
        
        $email = isset($levelFormat) ?  User::where("level", $levelFormat)->value("email") : "victor.ogiogio@ibedc.com";
        $name = isset($levelFormat)  ? User::where("level", $levelFormat)->value("name") : "Victor Ogiogio";

        Mail::to($email)->send(new ProcessCAADMail($this->process, $name));
    }

    // private function getUser($reqion, $businesshub) {

    //     $email = [];

    //     $format = $this->process->region .",". $this->process->business_hub.",";
    //     $getUser = User::where("level", $format)
    //     //loop through all the users that are in the region but you need to explode
    //     $getUserRoleObject = (new GeneralService)->getUserLevelRole();

    //     $getAllUser = User::get()->chunk(2, function ($users) use (&$data) {
    //         foreach ($users as $user) {
    //             $level = explode(",", $user->level);
    //             $region = $level[0];
    //             $bhub = $level[1] ?: $level[0];
    //             $email[] = 
    //         }
    //     });
    // }
}
