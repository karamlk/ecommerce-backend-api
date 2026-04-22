<?php

namespace App\Jobs;

use App\Mail\SendOtpMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class SendOtpJob implements ShouldQueue
{
    use Queueable;


    protected $email;
    protected $otp;
    /**
     * Create a new job instance.
     */
    public function __construct(string $email, string $otp)
    {
        $this->email = $email;
        $this->otp = $otp;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // TASK 2: Resource Management & Capacity Control - throttle emails-jobs
        Redis::throttle('send-otp')
            ->allow(10)
            ->every(60)
            ->then(function () {
                Log::info('OTP SENT', [
                    'email' => $this->email,
                    'time' => now()->toDateTimeString()
                ]);
                $mailable = new SendOtpMail($this->otp);
                Mail::to($this->email)->send($mailable);
            }, function () {
                Log::warning('OTP DELAYED', [
                    'email' => $this->email,
                    'time' => now()->toDateTimeString()
                ]);

                // (Capacity reached), 
                return $this->release(30);
            });
    }
}
