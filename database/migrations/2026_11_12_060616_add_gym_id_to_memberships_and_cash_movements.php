<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->foreignId('gym_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->foreignId('gym_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // Completar los registros que ya existen
        DB::statement('UPDATE memberships SET gym_id = (SELECT gym_id FROM members WHERE members.id = memberships.member_id)');
        DB::statement('UPDATE cash_movements SET gym_id = (SELECT gym_id FROM cash_sessions WHERE cash_sessions.id = cash_movements.cash_session_id)');

        Schema::table('memberships', fn (Blueprint $t) => $t->foreignId('gym_id')->nullable(false)->change());
        Schema::table('cash_movements', fn (Blueprint $t) => $t->foreignId('gym_id')->nullable(false)->change());
    }

    public function down(): void
    {
        Schema::table('cash_movements', fn (Blueprint $t) => $t->dropConstrainedForeignId('gym_id'));
        Schema::table('memberships', fn (Blueprint $t) => $t->dropConstrainedForeignId('gym_id'));
    }
};