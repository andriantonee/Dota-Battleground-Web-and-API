<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchesQualificationsDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matches_qualifications_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('parent_matches_id');
            $table->unsignedBigInteger('child_matches_id');
            $table->unsignedTinyInteger('from_child_matches_result');
            $table->unsignedTinyInteger('side');
            $table->timestamps();

            $table->foreign('parent_matches_id')->references('id')->on('matches')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('child_matches_id')->references('id')->on('matches')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('matches_qualifications_details');
    }
}
