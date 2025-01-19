<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->integer('room_id')->nullable();
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->integer('reciver_id')->nullable();
            $table->string('description', 500)->nullable();
            $table->string('file')->nullable();
            $table->tinyInteger('active')->default('0')->nullable();
            $table->tinyInteger('gnot')->default('0')->nullable();
            $table->tinyInteger('anot')->default('0')->nullable();
            $table->timestamps();
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobs');
    }
}
