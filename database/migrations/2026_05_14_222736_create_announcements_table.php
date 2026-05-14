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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            // 👤 WHO POSTED IT
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('title');
            $table->longText('content');

            // classification
            $table->enum('type', ['info', 'warning', 'urgent', 'emergency'])
                ->default('info');

            $table->enum('priority', ['low', 'medium', 'high', 'critical'])
                ->default('low');

            // status control
            $table->enum('status', ['draft', 'scheduled', 'published', 'archived'])
                ->default('published');

            $table->boolean('is_pinned')->default(false);

            // scheduling
            $table->timestamp('publish_at')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
