// database/migrations/xxxx_create_activity_logs_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // Which user sent this ping
            $table->unsignedBigInteger('user_id')->index();

            // Exact time the ping was recorded (from the tracker)
            $table->timestamp('logged_at');

            // "working" | "idle" | "offline"
            $table->enum('status', ['working', 'idle', 'offline'])
                  ->default('offline');

            // How many seconds in this interval were "working"
            $table->unsignedSmallInteger('active_seconds')->default(0);

            $table->timestamps(); // Laravel's created_at / updated_at

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
