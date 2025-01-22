<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('likes', function (Blueprint $table) {
            // Eloquent does not support composite PK :-(
            // $table->primary(['user_id', 'post_id']);
            // Eloquent compatibility workaround :-)
            // Use unique restriction instead of composite PK
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->unsignedBigInteger('post_id');
            $table->foreign('post_id')
                  ->references('id')->on('posts')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->unique(['user_id', 'post_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('likes');
    }
};
