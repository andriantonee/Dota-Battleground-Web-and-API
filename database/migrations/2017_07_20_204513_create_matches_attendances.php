<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchesAttendances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matches_attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('matches_id');
            $table->string('qr_identifier', 20);
            $table->timestamps();

            $table->unique(['matches_id', 'qr_identifier'], 'matches_attendances_unique_1');

            $table->foreign('matches_id', 'matches_attendances_foreign_1')->references('id')->on('matches')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('matches_attendances');
    }
}
