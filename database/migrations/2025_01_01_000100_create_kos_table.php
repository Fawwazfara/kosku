<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type');
            $table->text('description');
            $table->unsignedInteger('price_min');
            $table->unsignedInteger('price_max')->nullable();
            $table->string('address');
            $table->string('district');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('whatsapp', 20);
            $table->unsignedInteger('total_rooms');
            $table->unsignedInteger('available_rooms');
            $table->text('rules')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedInteger('wa_clicks')->default(0);
            $table->timestamps();

            $table->index(['type', 'status', 'price_min']);
            $table->index('available_rooms');
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kos');
    }
};
