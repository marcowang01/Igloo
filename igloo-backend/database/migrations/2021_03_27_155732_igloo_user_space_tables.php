<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IglooUserSpaceTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Users

        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number')->unique(); // The phone number needs to be formatted
            $table->timestamp('phone_number_verified_at')->nullable();
            $table->string('username')->unique();
            $table->string('avatar')->nullable();
        });

        Schema::create('user_friend', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained();
            $table->foreignId('friend_id')->constrained('users');
            $table->string('status'); // user to friend invitation status: pending, yes, no. If status is "yes", the table is bi-directional (we should take the union of two sides). Otherwise, it's not bi-directional (A user -> B friend means user A has sent an invitation to a prospective friend B).

            $table->timestamps();
            $table->primary(['user_id', 'friend_id']);
        });

        Schema::create('user_muted_friend', function (Blueprint $table) {
            // This is not bi-directional.

            $table->foreignId('user_id')->constrained();
            $table->foreignId('friend_id')->constrained('users');

            $table->timestamps();
            $table->primary(['user_id', 'friend_id']);
        });

        Schema::create('user_follower', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained();
            $table->foreignId('follower_id')->constrained('users');

            $table->timestamps();
            $table->primary(['user_id', 'follower_id']);
        });

        // Spaces

        Schema::create('spaces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('space_type'); // space/chat/personal (chat has to be secret)
            $table->string('visibility'); // public/friends-only/admin-only/secret
            $table->string('avatar')->nullable();
            $table->string('invite_mechanism'); // anyone_join/anyone_request/member_request/admin_request/none

            $table->foreignId('creator_id')->constrained('users');

            $table->timestamps();
        });

        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // main_update (return all updates in all blocks in the space subscribed)/update/list/album
            $table->string('visibility'); // public/secret/friends-only
            $table->string('display_method'); // feed/list/album
            $table->boolean('can_send_message'); // message outside block
            $table->foreignId('space_id')->constrained();

            $table->timestamps();
        });

        Schema::create('space_member', function (Blueprint $table) {
            $table->foreignId('space_id')->constrained();
            $table->foreignId('member_id')->constrained('users');

            $table->foreignId('inviter_id')->constrained('users');
            $table->string('status'); // status: pending, yes
            $table->timestamps();

            $table->primary(['space_id', 'member_id']);
        });

        // Blocks and Space to Block
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('location');
            $table->boolean('homepage_block')->default(false); // If yes, then we show it on the homepage.
            $table->boolean('message_block'); // If yes, this block is actually a message (non-repliable and non-clickable).

            $table->foreignId('user_id')->constrained();
            $table->foreignId('space_id')->constrained();
            $table->foreignId('channel_id')->constrained();

            $table->timestamps();
        });

        // Messages and Media
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->string('type'); // text, media, event (invitation to a channel), forward (a block)
            $table->bigInteger('message_extension_id')->nullable(); // matches id of the event and forward

            $table->foreignId('user_id')->constrained();
            $table->foreignId('block_id')->constrained();

            $table->string('message')->nullable();

            $table->timestamps();
        });

        Schema::create('message_extension_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('channel_id')->constrained();

            $table->timestamps();
        });

        Schema::create('message_extension_forwards', function (Blueprint $table) {
            $table->id();

            $table->foreignId('block_id')->constrained();

            $table->timestamps();
        });

        Schema::create('message_media', function (Blueprint $table) { // This is not a relation table. The Model should be called "MessageMedia".
            $table->id();

            $table->foreignId('message_id')->constrained('messages');
            $table->string('media_file')->nullable();

            $table->timestamps();
        });

        // Columns with additional constraints
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('personal_space_id')->nullable()->constrained('spaces');
        });

        Schema::create('user_muted_space', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained();
            $table->foreignId('space_id')->constrained('spaces');

            $table->timeStamps();
            $table->primary(['user_id', 'space_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone_number');
            $table->dropColumn('phone_number_verified_at');
            $table->dropColumn('username');
            $table->dropColumn('avatar');
            $table->dropForeign(['personal_space_id']);
            $table->dropColumn('personal_space_id');
        });

        Schema::drop('user_friend');

        Schema::drop('user_muted_space');

        Schema::drop('user_muted_friend');

        Schema::drop('user_follower');

        Schema::drop('space_member');

        Schema::drop('space_admin');

        Schema::drop('message_extension_events');

        Schema::drop('message_extension_forwards');

        Schema::drop('message_media');

        Schema::drop('messages');

        Schema::drop('blocks');

        Schema::drop('channels');

        Schema::drop('spaces');
    }
}
