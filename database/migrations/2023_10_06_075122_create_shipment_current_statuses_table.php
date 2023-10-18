<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentCurrentStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipment_current_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('tracking_number');
            $table->string('status');
            $table->string('schedule_delivery_date');
            $table->string('delivery_date');
            $table->string('create_datetime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipment_current_statuses');
    }
}
