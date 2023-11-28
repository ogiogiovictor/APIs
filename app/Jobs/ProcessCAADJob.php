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
       if($this->process->id == 1){
        $email = User::where("region", $this->process->region)->where("business_hub", $this->process->business_hub)
        ->where("user_role", "district_accountant")->value("email");
        $name = User::where("region", $this->process->region)->where("business_hub", $this->process->business_hub)
        ->where("user_role", "district_accountant")->value("name");
       }else if($this->process->id == 2){
        $email = User::where("region", $this->process->region)->where("business_hub", $this->process->business_hub)
        ->where("user_role", "businesshub_manager")->value("email");
        $name = User::where("region", $this->process->region)->where("business_hub", $this->process->business_hub)
        ->where("user_role", "district_accountant")->value("name");
       }else if($this->process->id == 3){
        $email = User::where("region", $this->process->region)->where("business_hub", $this->process->business_hub)
        ->where("user_role", "audit")->value("email");
        $name = User::where("region", $this->process->region)->where("business_hub", $this->process->business_hub)
        ->where("user_role", "district_accountant")->value("name");
       }else if($this->process->id == 4){
        $email = User::where("region", $this->process->region)->where("business_hub", $this->process->business_hub)
        ->where("user_role", "regional_manager")->value("email");
        $name = User::where("region", $this->process->region)->where("business_hub", $this->process->business_hub)
        ->where("user_role", "district_accountant")->value("name");
       }else if($this->process->id == 5){
        $email = User::where("region", $this->process->region)->where("business_hub", $this->process->business_hub)
        ->where("user_role", "hcs")->value("email");
        $name = User::where("region", $this->process->region)->where("business_hub", $this->process->business_hub)
        ->where("user_role", "district_accountant")->value("name");
       }else if($this->process->id == 6){
        $email = User::where("region", $this->process->region)->where("business_hub", $this->process->business_hub)
        ->where("user_role", "ccu")->value("email");
        $name = User::where("region", $this->process->region)->where("business_hub", $this->process->business_hub)
        ->where("user_role", "district_accountant")->value("name");
       }else if($this->process->id == 7){
        $email = User::where("region", $this->process->region)->where("business_hub", $this->process->business_hub)
        ->where("user_role", "md")->value("email");
        $name = User::where("region", $this->process->region)->where("business_hub", $this->process->business_hub)
        ->where("user_role", "district_accountant")->value("name");
       }else if($this->process->id == 9){
        $email = User::where("region", $this->process->region)->where("business_hub", $this->process->business_hub)
        ->where("user_role", "billing")->value("email");
        $name = User::where("region", $this->process->region)->where("business_hub", $this->process->business_hub)
        ->where("user_role", "district_accountant")->value("name");
       }
      

        // $levelFormat = $this->process->region." ".$this->process->business_hub;
        
        // $email = isset($levelFormat) ?  User::where("level", $levelFormat)->value("email") : "victor.ogiogio@ibedc.com";
        // $name = isset($levelFormat)  ? User::where("level", $levelFormat)->value("name") : "Victor Ogiogio";

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
