<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('download_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('format');
            $table->string('status')->default('pending'); // pending, processing, finished, error
            $table->integer('progress')->default(0); // 0-100
            $table->string('file_path')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('download_tasks');
    }
}; 