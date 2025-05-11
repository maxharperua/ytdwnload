<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('download_tasks', function (Blueprint $table) {
            $table->string('type')->default('video')->after('format');
        });
    }

    public function down()
    {
        Schema::table('download_tasks', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}; 