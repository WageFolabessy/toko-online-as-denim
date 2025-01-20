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
        Schema::create('site_user_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_user_id');
            $table->unsignedBigInteger('address_id');
            $table->boolean('is_default');
            $table->timestamps();
            $table->foreign('site_user_id')->references('id')->on('site_users')->onDelete('cascade');
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sitr_user_addresses');
    }
};
