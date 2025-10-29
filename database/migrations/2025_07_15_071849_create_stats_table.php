<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('stats', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['messages_received', 'emails_created']);
            $table->unsignedInteger('count')->default(1);
            $table->date('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('stats');
    }
};
