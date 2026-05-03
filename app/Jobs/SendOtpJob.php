<?php

namespace App\Jobs;

use App\Mail\SendOtpMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class SendOtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    protected $email;
    protected $otp;

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
        // TASK 3: Asynchronous Queues
        // TASK 2: Resource Management & Capacity Control - throttle emails-jobs
        Redis::throttle('send-otp')
            ->allow(10)
            ->every(60)
            ->then(function () {
                Log::info('OTP SENT', [
                    'email' => $this->email,
                    'time'  => now()->toDateTimeString()
                ]);
                Mail::to($this->email)->send(new SendOtpMail($this->otp));
            }, function () {
                Log::warning('OTP DELAYED', [
                    'email' => $this->email,
                    'time'  => now()->toDateTimeString()
                ]);

                // (Capacity reached), 
                return $this->release(30);
            });
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('OTP JOB FAILED PERMANENTLY', [
            'email'     => $this->email,
            'exception' => $exception->getMessage(),
        ]);
    }
}
