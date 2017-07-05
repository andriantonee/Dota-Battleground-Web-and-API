<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTournamentsRegistrationsConfirmationsApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournaments_registrations_confirmations_approvals', function (Blueprint $table) {
            $table->unsignedBigInteger('tournaments_registrations_confirmations_id');
            $table->unsignedBigInteger('members_id');
            $table->boolean('status');
            $table->timestamps();

            $table->primary('tournaments_registrations_confirmations_id', 'tournaments_registrations_confirmations_approvals_primary');

            $table->foreign('tournaments_registrations_confirmations_id', 'tournaments_registrations_confirmations_approvals_foreign_1')->references('tournaments_registrations_id')->on('tournaments_registrations_confirmations')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('members_id', 'tournaments_registrations_confirmations_approvals_foreign_2')->references('id')->on('members')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tournaments_registrations_confirmations_approvals');
    }
}
