<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('stages_logs', function (Blueprint $table) {
            $table->id()->primary();
            $table->foreignUuid('drivers_booking_uuid')->references('uuid')->on('drivers_booking');
            $table->enum('stage', ['drivers_home_to_patient','waiting_at_pacjent_home','drive_to_facility','waiting_in_facility','facility_to_patient_home','ended','outsider','critical_malfunction'])->default('drivers_home_to_patient');
            $table->timestamp('start_time', precision: 0);
            $table->timestamp('end_time', precision: 0)->nullable();
            $table->decimal('latitude', total: 10, places: 8)->nullable();
            $table->decimal('longitude', total: 10, places: 8)->nullable();
            $table->timestamps();
        });
        
        Schema::create('troubles', function (Blueprint $table) {
            $table->id()->primary(); 
            $table->unsignedInteger('stages_logs_id');
            $table->string('name', 32); 
            $table->integer('time_value')->nullable(); 
            $table->text('description')->nullable(); 
            $table->decimal('latitude', total: 10, places: 8)->nullable();
            $table->decimal('longitude', total: 10, places: 8)->nullable();
            $table->foreign('stages_logs_id')->references('id')->on('stages_logs')->onDelete('cascade');
            $table->timestamps();
        });
        Schema::create('received_bookings', function (Blueprint $table) {
            $table->bigInteger('drivers_uuid')->unsigned();
            $table->bigInteger('notifications_drivers_booking_uuid')->unsigned();
            $table->primary(['drivers_uuid', 'notifications_drivers_booking_uuid'], 'received_bookings_pk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stages_logs');
        Schema::dropIfExists('troubles');
        Schema::dropIfExists('received_bookings');

    }
};
