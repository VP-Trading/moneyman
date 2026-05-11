<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('boa_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('refresh_token')->nullable();
            $table->text('access_token')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('boa_tokens');
    }
};
