<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DailyUsageTime;
use Illuminate\Support\Carbon;

class AnalyticsController extends Controller
{
    function admin_validation($request, $user_id=null)
    {   
        if ($user_id == $request->user()->id){
            return;
        }

        if ($request->password == env("ADMIN_PASSWORD")){
            return;
        }
        abort(403);
    }

    function get_dates_and_existing_users($request)
    {
        $start_date = new Carbon($request->date);
        $interval = $request->interval;
        
        if (!$interval){
            $interval = 7;
        }
        $end_date = (new Carbon($request->date))->addDays($interval);

        if (!$start_date || $end_date->gt(new Carbon('today midnight'))){
            $start_date = (new Carbon('today midnight'))->subWeeks(1);
            $interval = 7;
            $end_date = new Carbon('today midnight');
        }
        
        $existing_users_count = User::whereDate('created_at', '<', $end_date)->count();
        return[
            'start_date' => $start_date,
            'end_date'=>$end_date,
            'existing_users_count'=>$existing_users_count,
            'interval'=>$interval
        ];
    }

    public function weekly_growth_rate(Request $request)
    {
        $this->admin_validation($request);
        $start_date = new Carbon($request->date);
        $end_date = (new Carbon($request->date))->addWeek(1);

        if (!$start_date || $end_date->gt(new Carbon('today midnight'))){
            $end_date = new Carbon('today midnight');
            $start_date = (new Carbon('today midnight'))->subWeeks(1);
        }

        $existing_users_count = User::whereDate('created_at', '<', $start_date)->count();

        $new_users_count = User::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<', $end_date)->count();

        if ($existing_users_count){
            $growth_rate = round(($new_users_count/$existing_users_count) * 100, 2);
            return [
                'ret' => 0,
                'start' => $start_date,
                'end' => $end_date,
                "growth" => $growth_rate
            ];
        } else {
            return [
                'ret' => -1,
                'msg' => 'no existing users'
            ];
        }
    }

    public function average_daily_usage_time(Request $request, $user_id=null)
    {
        $this->admin_validation($request, $user_id);
        
        $temp = $this->get_dates_and_existing_users($request);
        $start_date = $temp["start_date"];
        $end_date = $temp["end_date"];
        $existing_users_count = $temp["existing_users_count"];
        $interval = $temp["interval"];

        if ($user_id === null) {
            if (!$interval || !$existing_users_count){
                return [
                    'ret' => -1,
                    'msg' => "no existing users"
                ];
            }
            $total_time_usage = DailyUsageTime::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<', $end_date)->sum('time');
            $average_per_day = $total_time_usage / $existing_users_count / $interval;
            return [
                'ret' => 0,
                'start' => $start_date,
                'end' => $end_date,
                'daily avg' => round($average_per_day, 2),
                'total usage' => $total_time_usage * 1,
                'user count' => $existing_users_count
            ];  
        } 

        $online_user = DailyUsageTime::where("user_id", $user_id);
        if ($online_user && $interval){
            $total_time_usage = $online_user->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<', $end_date)->sum('time');
            $average_per_day = $total_time_usage / $interval;
            return [
                'ret' => 1,
                'start' => $start_date,
                'end' => $end_date,
                'avg usage' => round($average_per_day, 2),
                'total usage' => $total_time_usage * 1,
                'user'=>User::find($user_id)
            ];
        }
        return [
            'ret' => -1,
            'msg' => "user has no logged activity"
        ];
    }

    public function percent_usage(Request $request)
    {
        $this->admin_validation($request);
        
        $temp = $this->get_dates_and_existing_users($request);
        $start_date = $temp["start_date"];
        $end_date = $temp["end_date"];
        $existing_users_count = $temp["existing_users_count"];
        $interval = $temp["interval"];

        $daily_active_users = DailyUsageTime::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<', $end_date)->count();
        if (!$interval || !$existing_users_count){
            return [
                'ret' => -1,
                'msg' => "no existing users"
            ];
        }
        $percent_usage = $daily_active_users / $existing_users_count / $interval;
        return [
            'ret' => 0,
            'start' => $start_date,
            'end' => $end_date,
            'active' => $daily_active_users,
            'total' => $existing_users_count,
            'usage' => round($percent_usage * 100, 2) 
        ]; 
    }

    public function log_activity(Request $request, $user_id)
    {
        $this->admin_validation($request, $user_id);

        $user = User::findOrFail($user_id);
        $time = new Carbon('today midnight');

        $online_user = DailyUsageTime::where('user_id', $user_id)->whereDate('created_at', '>=', $time)->first();
        
        if ($online_user) { 
            $interval = 1;
            $online_user->time = $online_user->time + $interval;
            $online_user->save();
            return [
                "ret" => 0,
                "msg" => "logged usage time: ".$interval." min",
                "online user" => $online_user
            ];
        }

        $online_user = DailyUsageTime::create([
            "user_id" => $user->id,
            "time" => 1
        ]);

        return [
            "ret" => 1,
            "msg" => "new online user logged",
            "online user" => $online_user
        ];
    }
}
