<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('provider');
            $table->boolean('is_success')->default(false);
            $table->string('tx_ref')->nullable();
            $table->string('provider_ref')->nullable();
            $table->string('status');
            $table->decimal('amount');
            $table->decimal('charge')->nullable();
            $table->string('currency', 10)->default('ETB');
            $table->json('data')->nullable();
            $table->unique(['provider', 'tx_ref']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('webhook_events');
    }
};
