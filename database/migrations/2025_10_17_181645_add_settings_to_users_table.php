<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('bio')->nullable()->after('phone');
            $table->string('avatar')->nullable()->after('bio');
            $table->string('timezone')->default('UTC')->after('avatar');
            $table->string('language')->default('en')->after('timezone');
            $table->string('date_format')->default('Y-m-d')->after('language');
            $table->boolean('two_factor_enabled')->default(false)->after('date_format');
            $table->boolean('email_notifications')->default(true)->after('two_factor_enabled');
            $table->boolean('push_notifications')->default(true)->after('email_notifications');
            $table->boolean('task_reminders')->default(true)->after('push_notifications');
            $table->boolean('transaction_alerts')->default(true)->after('task_reminders');
            $table->json('connected_apps')->nullable()->after('transaction_alerts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'bio',
                'avatar',
                'timezone',
                'language',
                'date_format',
                'two_factor_enabled',
                'email_notifications',
                'push_notifications',
                'task_reminders',
                'transaction_alerts',
                'connected_apps'
            ]);
        });
    }
};
