<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AnalyticsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/register', [UserController::class, 'register'])->name('register');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/user/change_password', [UserController::class, 'change_pwd']);
    Route::post('/user/space/{space_id}/exit', [UserController::class,'exit_space']);
    Route::post('/user/edit', [UserController::class, 'edit']);
    Route::post('/user/avatar', [UserController::class, 'avatar']);
    Route::get('/user/search', [UserController::class, 'search']);
    Route::post('/user/push_notification/register', [UserController::class, 'push_notification_register']);
    Route::post('/user/push_notification/unregister', [UserController::class, 'push_notification_unregister']);
    Route::get('/user/{user_id?}', [UserController::class, 'user']);
    Route::post('/user/update_friend_invititation/{other_user_id?}', [UserController::class, 'update_friend_invititation']);
    Route::get('/user/{user_id}/friends', [UserController::class, 'friends']);
    Route::get('/user/{user_id}/follow/{other_user_id?}', [UserController::class, 'follow']);
    Route::get('/user/{user_id}/unfollow/{other_user_id?}', [UserController::class, 'unfollow']);
    Route::get('/user/{user_id}/friend_invite/{other_user_id?}', [UserController::class, 'friend_invite']);
    Route::get('/user/{user_id}/followers', [UserController::class, 'followers']);
    Route::get('/user/{user_id}/followings', [UserController::class, 'followings']);
    Route::get('/user/{user_id}/spaces', [UserController::class, 'spaces']);
    Route::get('/logout', [UserController::class, 'logout'])->name('logout');

    Route::post('/space/create', [SpaceController::class, 'create']);
    Route::get('/space/search', [SpaceController::class, 'search']);
    Route::get('/space/updates', [SpaceController::class, 'updates']);
    Route::get('/space/{space_id?}', [SpaceController::class, 'space']);
    Route::post('/space/{space_id?}/edit', [SpaceController::class, 'edit_space']);
    Route::post('/space/{space_id?}/edit_title', [SpaceController::class, 'edit_title']);
    Route::post('/space/{space_id?}/accept_invite/{user_id?}', [SpaceController::class, 'accept_invitation']);
    Route::post('/space/{space_id?}/mute', [SpaceController::class, 'mute_space']);
    Route::post('/space/{space_id?}/unmute', [SpaceController::class, 'unmute_space']);
    Route::post('/space/{space_id?}/user/{user_id?}/delete', [SpaceController::class, 'kick_member']);
    Route::post('/space/{space_id?}/invite', [SpaceController::class, 'invite_multiple_members']);
    Route::post('/space/{space_id?}/invite/{user_id?}', [SpaceController::class, 'invite_member']);
    Route::get('/space/{space_id?}/members', [SpaceController::class, 'members']);
    Route::post('/space/{space_id?}/avatar', [SpaceController::class, 'avatar']);

    Route::post('/space/{space_id?}/channel/create', [ChannelController::class, 'create']);
    Route::get('/channel/{channel_id?}/', [ChannelController::class, 'channel']);
    Route::post('/channel/{channel_id?}/edit', [ChannelController::class, 'edit']);
    Route::delete('/channel/delete/{channel_id?}', [ChannelController::class, 'delete']);

    Route::post('/channel/{channel_id?}/block/create', [BlockController::class, 'create']);
    Route::post('/channel/{channel_id?}/block/{block_id?}/forward', [BlockController::class, 'forward']);
    Route::get('/block/{block_id?}', [BlockController::class, 'block']);
    Route::get('/block/{block_id?}/messages', [BlockController::class, 'messages']);
    Route::delete('/block/delete/{block_id?}', [BlockController::class, 'delete']);

    Route::post('/block/{block_id?}/message/create', [MessageController::class, 'create']);
    Route::delete('/message/delete/{message_id?}', [MessageController::class, 'delete']);

    Route::post('/notifications', [UserController::class, 'notifications']);

    Route::post('/analytics/growth_rate', [AnalyticsController::class, 'weekly_growth_rate']);
    Route::post('/analytics/average_usage/{user_id?}', [AnalyticsController::class, 'average_daily_usage_time']);
    Route::post('/analytics/percent_usage', [AnalyticsController::class, 'percent_usage']);
    Route::post('/analytics/log_activity/{user_id?}', [AnalyticsController::class, 'log_activity']);
});
