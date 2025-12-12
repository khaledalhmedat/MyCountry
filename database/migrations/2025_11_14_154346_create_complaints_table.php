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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('destination');
            $table->string('site');
            $table->string('description');
            $table->string('image');
            $table->string('documents');
            $table->unsignedBigInteger('type_id');
            $table->string('status')->default('new');
            $table->timestamps();

            $table->foreign('type_id')->references('id')->on('types')->ondelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
