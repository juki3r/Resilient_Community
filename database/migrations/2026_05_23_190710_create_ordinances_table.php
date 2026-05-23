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
        Schema::create('ordinances', function (Blueprint $table) {
            $table->id();
            $table->string('barangay');
            $table->string('ordinance_number')->unique();
            $table->string('title');
            $table->text('description');

            $table->string('category')->nullable(); // safety, health, traffic, etc.
            $table->string('status')->default('active'); // active, amended, repealed

            $table->date('effectivity_date')->nullable();
            $table->date('approved_date')->nullable();

            $table->text('penalties')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordinances');
    }
};
