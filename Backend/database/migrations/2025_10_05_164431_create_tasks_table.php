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
        Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description');
    $table->enum('status',['pending','assigned','in_progress','completed','rejected'])->default('pending');
    $table->foreignId('admin_id')->constrained('users');
    $table->foreignId('developer_id')->nullable()->constrained('users');
    $table->foreignId('client_id')->constrained('users');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
