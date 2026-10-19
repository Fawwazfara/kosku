<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->whereNull('role')->update(['role' => 'student']);
        DB::table('users')->whereNull('password')->update(['password' => Hash::make(Str::random(40))]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['provider', 'provider_id']);
            $table->dropColumn(['provider', 'provider_id', 'avatar']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable(false)->default('student')->change();
            $table->string('password')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('provider', 20)->nullable()->after('email');
            $table->string('provider_id')->nullable()->after('provider');
            $table->string('avatar')->nullable()->after('provider_id');
            $table->string('password')->nullable()->change();
            $table->string('role')->nullable()->change();
            $table->unique(['provider', 'provider_id']);
        });
    }
};
