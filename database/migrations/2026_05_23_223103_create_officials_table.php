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
        Schema::create('officials', function (Blueprint $table) {
            $table->id();


            $table->string('barangay');
            $table->string('full_name');
            $table->string('gender')->nullable();

            $table->string('position'); // Captain, Kagawad, etc.
            $table->string('committee')->nullable();

            $table->string('address')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();

            $table->date('term_start')->nullable();
            $table->date('term_end')->nullable();

            $table->string('status')->default('active'); // active, inactive, former

            $table->string('photo')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('officials');
    }
};
