<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentProgressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipment_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('shipper_id');
            $table->string('tracking_number');
            $table->string('status');
            $table->string('schedule_delivery_date')->default('0000:00:00 00:00:00');
            $table->string('delivery_date')->default('0000:00:00 00:00:00');
            $table->string('create_datetime')->default('0000:00:00 00:00:00');
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
        Schema::dropIfExists('shipment_progress');
    }
}
