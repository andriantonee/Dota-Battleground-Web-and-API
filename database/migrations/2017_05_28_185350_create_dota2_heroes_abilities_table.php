<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDota2HeroesAbilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dota2_heroes_abilities', function (Blueprint $table) {
            $table->unsignedInteger('dota2_heroes_id');
            $table->unsignedInteger('dota2_abilities_id');
            $table->unsignedInteger('include_dota2_abilities_id')->nullable();
            $table->boolean('status');
            $table->timestamps();

            $table->primary(['dota2_heroes_id', 'dota2_abilities_id'], 'dota2_heroes_abilities_primary');

            $table->foreign('dota2_heroes_id', 'dota2_heroes_abilities_foreign_1')->references('id')->on('dota2_heroes')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('dota2_abilities_id', 'dota2_heroes_abilities_foreign_2')->references('id')->on('dota2_abilities')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('include_dota2_abilities_id', 'dota2_heroes_abilities_foreign_3')->references('id')->on('dota2_abilities')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dota2_heroes_abilities');
    }
}
