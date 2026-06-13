<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convert cloud_share.user_id from users.id integer FK to users.uid UUID FK.
     */
    public function up(): void
    {
        Schema::table('cloud_share', function (Blueprint $table) {
            $table->uuid('user_uid')->nullable()->after('uid');
        });

        DB::statement('UPDATE cloud_share cs INNER JOIN users u ON u.id = cs.user_id SET cs.user_uid = u.uid');

        Schema::table('cloud_share', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('cloud_share', function (Blueprint $table) {
            $table->uuid('user_id')->nullable()->after('uid');
        });

        DB::statement('UPDATE cloud_share SET user_id = user_uid');

        Schema::table('cloud_share', function (Blueprint $table) {
            $table->dropColumn('user_uid');
            $table->index('user_id');
            $table->foreign('user_id')->references('uid')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Revert cloud_share.user_id back to users.id integer FK.
     */
    public function down(): void
    {
        Schema::table('cloud_share', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id_int')->nullable()->after('uid');
        });

        DB::statement('UPDATE cloud_share cs INNER JOIN users u ON u.uid = cs.user_id SET cs.user_id_int = u.id');

        Schema::table('cloud_share', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('cloud_share', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('uid');
        });

        DB::statement('UPDATE cloud_share SET user_id = user_id_int');

        Schema::table('cloud_share', function (Blueprint $table) {
            $table->dropColumn('user_id_int');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
