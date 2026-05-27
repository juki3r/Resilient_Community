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
        Schema::create('concerns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('mobile_users')->onDelete('cascade');
            $table->string('barangay');
            $table->string('title');
            $table->string('location');
            $table->text('description');

            // 🚚 tracking system
            $table->enum('status', [
                'submitted',
                'received',
                'under_review',
                'in_progress',
                'resolved',
                'rejected'
            ])->default('submitted');

            $table->integer('progress')->default(10);

            $table->text('admin_reply')->nullable();
            $table->boolean('admin_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concerns');
    }
};
