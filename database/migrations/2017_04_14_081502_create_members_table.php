<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email', 255);
            $table->unsignedTinyInteger('member_type');
            $table->string('password', 255);
            $table->string('name', 255);
            $table->string('steam32_id', 255)->nullable();
            $table->string('picture_file_name', 255)->nullable();
            $table->string('document_file_name', 255)->nullable();
            $table->boolean('verified')->default('1');
            $table->boolean('banned')->default('0');
            $table->timestamps();

            $table->unique(['email', 'member_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('members');
    }
}
