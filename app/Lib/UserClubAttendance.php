<?php namespace App\Lib;

class UserClubAttendance {
    public static function infoAt($userId, $clubId, $strDate){
        $output = (object) ['continuous_days' => 0, 'has_attended' => false, 'total_days' => 0];
        $query = \App\UserClubAttendance::where('user_id', $userId)->where('club_id', $clubId)->where('attended_at', '<=', $strDate);
        $output->total_days = $query->count();
        $date = new \Carbon\Carbon($strDate);
        $attendance = $query->orderBy('attended_at', 'desc')->first();
        if(empty($attendance)) {
            return $output;
        } else  if($attendance->attended_at->isSameDay($date)){
            $output->continuous_days = $attendance->days;
            $output->has_attended = true;
        } else if($attendance->attended_at->isSameDay($date->subDay())){
            $output->continuous_days = $attendance->days;
            $output->has_attended = false;
        } else {
            return $output;
        }
        return $output;
    }
}
