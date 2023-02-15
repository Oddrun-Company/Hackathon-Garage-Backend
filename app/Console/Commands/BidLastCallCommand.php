<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SmsService;
use Illuminate\Console\Command;

class BidLastCallCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bid:last-call';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(SmsService $sms): void
    {
        User::query()->get()->each(function ($user) use ($sms) {
            $sms->bidLastCall($user->phone_number, $this->timeDiff(env('BID_LAST_CALL_NOTIFICATION_TIME'), env('BID_DEADLINE_TIME')));
        });
    }

    private function timeDiff($firstTime, $lastTime): string
    {
        $firstTime = strtotime($firstTime);
        $lastTime = strtotime($lastTime);

        $difference = $lastTime - $firstTime;

        $data['years'] = abs(floor($difference / 31536000));
        $data['days'] = abs(floor(($difference - ($data['years'] * 31536000)) / 86400));
        $data['hours'] = abs(floor(($difference - ($data['years'] * 31536000) - ($data['days'] * 86400)) / 3600));
        $data['minutes'] = abs(floor(($difference - ($data['years'] * 31536000) - ($data['days'] * 86400) - ($data['hours'] * 3600)) / 60));

        $timeString = '';

        if ($data['years'] > 0) {
            $timeString .= $data['years'] . " " . trans("messages.times.year") . ", ";
        }

        if ($data['days'] > 0) {
            $timeString .= $data['days'] . " " . trans("messages.times.day") . ", ";
        }

        if ($data['hours'] > 0) {
            $timeString .= $data['hours'] . " " . trans("messages.times.hour") . ", ";
        }

        if ($data['minutes'] > 0) {
            $timeString .= $data['minutes'] . " " . trans("messages.times.minute");
        }

        return $timeString;
    }
}
