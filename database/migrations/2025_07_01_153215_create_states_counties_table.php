<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('states_counties', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 2); // RO, GB, US, etc.
            $table->string('code', 10); // AB, B, CJ, etc. sau GB-ENG, US-CA
            $table->string('name'); // Alba, București, England, California
            $table->string('type')->default('county'); // county, state, region, province
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['country_code', 'is_active']);
            $table->unique(['country_code', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('states_counties');
    }
};
