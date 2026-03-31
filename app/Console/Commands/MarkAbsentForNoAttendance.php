<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance;

class MarkAbsentForNoAttendance extends Command
{
    protected $signature = 'attendance:mark-absent';
    protected $description = 'Mark absent for users without attendance record for today';

    public function handle()
    {
        $today = now()->toDateString();
        $userIdsWithAttendance = Attendance::whereDate('date', $today)->pluck('user_id')->toArray();
        $allUserIds = User::pluck('id')->toArray();
        $absentIds = array_diff($allUserIds, $userIdsWithAttendance);

        foreach ($absentIds as $userId) {
            Attendance::updateOrCreate(
                ['user_id' => $userId, 'date' => $today],
                [
                    'status' => 'absent',
                    'present_status' => 'absent',
                ]
            );
        }

        $this->info('Absent records marked for users without attendance today.');
    }
}