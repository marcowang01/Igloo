<?php
use App\Models\PushNotificationToken;
use Illuminate\Support\Facades\Http;

if (! function_exists('send_notifications')) {
    function send_notifications($user_ids, $title, $body, $data = [])
    {
        /**
         * This sends notifications. Needs to proceed with caution to avoid issues with spams.
         * See response info from: https://laravel.com/docs/8.x/http-client#making-requests
         * Example:
         * Route::get('/send_notification', function() {
         *      $notification = send_notifications([
         *          7
         *      ], "Hi", "This is Body.", [
         *          "navigateTo" => "space",
         *          "spaceId" => 1
         *      ]);
         *      return $notification;
         *  });
         */
        $tokens = PushNotificationToken::whereIn("user_id", $user_ids);
        $raw_tokens = $tokens->pluck("token")->toArray();
        if (count($raw_tokens) == 0) {
            return null;
        }
        $response = Http::post('https://exp.host/--/api/v2/push/send', [
            "to" => $raw_tokens,
            "title" => $title,
            "body" => $body,
            "data" => $data
        ]);
        return $response;
    }
}
