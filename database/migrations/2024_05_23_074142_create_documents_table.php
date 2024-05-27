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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('signing_page_url')->nullable();
            $table->string('signed_filename')->nullable();
            $table->string('doc_id')->nullable();
            $table->timestamp('original_file_upload_at')->nullable();
            $table->timestamp('signed_file_upload_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
