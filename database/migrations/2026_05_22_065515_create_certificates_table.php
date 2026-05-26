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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();

            // Admin panel user
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade');

            // Mobile app user
            $table->foreignId('mobile_user_id')
                ->nullable()
                ->constrained('mobile_users')
                ->onDelete('cascade');


            // Personal info
            $table->string('barangay');
            $table->string('full_name');
            $table->integer('age');
            $table->string('gender');
            $table->text('address');

            // Document request info
            $table->string('document_type');
            $table->text('purpose');

            // Business info (nullable because not always used)
            $table->string('company_name')->nullable();
            $table->string('business_nature')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
