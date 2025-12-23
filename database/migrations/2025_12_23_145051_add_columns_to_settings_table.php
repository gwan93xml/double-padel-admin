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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('booking_url')->nullable()->before('created_at');
            $table->json('home_navigations')->nullable()->after('booking_url');
            $table->string('home_hero_image')->nullable()->after('home_navigations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['booking_url', 'home_navigations', 'home_hero_image']);
        });
    }
};
