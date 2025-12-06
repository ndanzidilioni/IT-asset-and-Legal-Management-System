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
            $table->json('privileges')->nullable()->after('status');
            $table->timestamp('password_changed_at')->nullable()->after('password');
            $table->boolean('must_change_password')->default(false)->after('password_changed_at');
            $table->integer('failed_login_attempts')->default(0)->after('must_change_password');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'privileges',
                'password_changed_at',
                'must_change_password',
                'failed_login_attempts',
                'locked_until'
            ]);
        });
    }
};
