<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDota2LiveMatchesCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dota2_live_matches_comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dota2_live_matches_id');
            $table->unsignedBigInteger('members_id');
            $table->text('detail');
            $table->timestamps();

            $table->foreign('dota2_live_matches_id', 'dota2_live_matches_comments_foreign_1')->references('id')->on('dota2_live_matches')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('members_id', 'dota2_live_matches_comments_foreign_2')->references('id')->on('members')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dota2_live_matches_comments');
    }
}
