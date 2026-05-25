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
        Schema::create('news', function (Blueprint $table) {
            $table->id();

            $table->string('barangay');
            $table->string('title');
            $table->longText('content');

            $table->string('category')->nullable();
            // announcement, alert, disaster, event

            $table->string('image')->nullable();

            $table->enum('status', ['draft', 'published', 'archived'])->default('published');

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
