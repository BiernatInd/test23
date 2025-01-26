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

        Schema::create('phones', function (Blueprint $table) {
            $table->id()->primary();
            $table->string('imei', length: 100);
            $table->string('model', length: 100);
            $table->text('description');
            $table->timestamps();
        });
        Schema::create('cars', function (Blueprint $table) {
            $table->id()->primary();
            $table->string('registration_number', length: 100);
            $table->string('brand', length: 100);
            $table->string('color', length: 100);
            $table->timestamps();
        });
        Schema::create('address', function (Blueprint $table) {
            $table->id()->primary();
            $table->string('raw_address', length: 100);
            $table->string('postal_code', length: 6);
            $table->string('city', length: 32);
            $table->decimal('latitude', total: 10, places: 8);
            $table->decimal('longitude', total: 10, places: 8);
            $table->timestamps();
        });

        Schema::create('drivers', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignId('basic_user_data')->references('id')->on('users');
            $table->string('phone_number', length: 9); 
            $table->foreignId('address_id')->references('id')->on('address');
            $table->foreignId('phones_id')->references('id')->on('phones');
            $table->foreignId('cars_id')->references('id')->on('cars');
            $table->string('status', length: 32); 
            $table->timestamps();
        });

        Schema::create('current_position', function (Blueprint $table) {
            $table->foreignUuid('drivers_uuid')->primary()->references('uuid')->on('drivers');
            $table->decimal('latitude', total: 10, places: 8)->nullable();
            $table->decimal('longitude', total: 10, places: 8)->nullable();
            $table->timestamps();
        });
    
        /*
        Schema::create('additional_drivers_info', function (Blueprint $table) {
            $table->foreignUuid('drivers_uuid')->primary()->references('uuid')->on('drivers');
            $table->string('pesel', length: 11); 
            $table->date('driving_license_expiration_date');
            $table->date('OC_expiration_date');
            $table->string('kopia_licencji_transportowej', length: 32); 
            $table->string('dowod_rejestracyjny', length: 11); 
            $table->string('zgoda_na_uzywanie_pojazdu', length: 11); 
            $table->timestamps();
        });
        */
        Schema::create('drivers_booking', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('drivers_uuid')->nullable()->references('uuid')->on('drivers');
            $table->integer('distance');
            $table->integer('travel_time');
            $table->timestamp('visit_date_start');
            $table->timestamp('visit_date_end');
            $table->integer('patient_address_id');
            $table->string('facility_name', 100);
            $table->integer('facility_address_id');
            $table->json('additional_info')->nullable();
            $table->text('comment')->nullable();
            $table->boolean('important')->default(false);
            $table->enum('booking_travel_status', ['waiting_for_driver','driver_cancelled','in_progress','driver_found', 'no_drivers_nearby', 'failure_on_road', 'ended'])->default('waiting_for_driver');
            $table->string('editing_status', 32);
            $table->timestamps();
        });

        Schema::create('contact_data', function (Blueprint $table) {
            $table->foreignUuid('drivers_booking_uuid')->primary()->references('uuid')->on('drivers_booking');
            $table->string('patient_phone_number', length: 32); 
            $table->string('patient_first_name', length: 32); 
            $table->string('patient_last_name', length: 32);
            $table->string('opiekun_phone_number', length: 32); 
            $table->string('opiekun_first_name', length: 32); 
            $table->string('opiekun_last_name', length: 32);  
            $table->timestamps();
        });

        Schema::create('outsider_data', function (Blueprint $table) {
            $table->foreignUuid('drivers_booking_uuid')->primary()->references('uuid')->on('drivers_booking');
            $table->string('outsider_name', length: 32); 
            $table->text('event_description');
            $table->text('actions_taken');
            $table->integer('price');
            $table->timestamps();
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->foreignUuid('drivers_booking_uuid')->primary()->references('uuid')->on('drivers_booking');
            $table->integer('notified_drivers_within_50km');
            $table->integer('notified_drivers_within_100km');
            $table->timestamps();
        });
   
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('phones');
        Schema::dropIfExists('cars');
        Schema::dropIfExists('address');
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('current_position');
        // Schema::dropIfExists('additional_drivers_info');
        Schema::dropIfExists('facility');
        Schema::dropIfExists('drivers_booking');
        Schema::dropIfExists('contact_data');
        Schema::dropIfExists('outsider_data');
    }
};
