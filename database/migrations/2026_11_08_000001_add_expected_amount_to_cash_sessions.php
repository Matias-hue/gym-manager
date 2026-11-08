<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_sessions', function (Blueprint $table) {
            // Efectivo que debía haber al cerrar. Se guarda para que la diferencia
            // de una caja cerrada no cambie si después se anula un pago.
            $table->unsignedBigInteger('expected_amount')->nullable()->after('opening_amount');
        });
    }

    public function down(): void
    {
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->dropColumn('expected_amount');
        });
    }
};
