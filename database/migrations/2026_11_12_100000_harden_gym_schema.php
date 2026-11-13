<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La columna medical_notes NO se toca: se deja en la base sin usar y solo se
        // quita de los formularios. Si algún día se necesita, se vuelve a mostrar el campo.

        // El documento es opcional (NULL), pero si se carga no se repite dentro del mismo gimnasio.
        // MySQL permite muchos NULL en un índice único, así que los gimnasios que
        // solo anotan el nombre no se ven afectados.
        Schema::table('members', function (Blueprint $table) {
            $table->unique(['gym_id', 'document_number']);
        });

        // Índices para que los reportes, la caja y los vencimientos sigan rápidos
        // cuando haya muchos gimnasios y años de datos.
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['gym_id', 'paid_at']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['gym_id', 'checked_in_at']);
        });

        Schema::table('memberships', function (Blueprint $table) {
            $table->index(['gym_id', 'ends_on']);
        });

        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->index(['gym_id', 'closed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->dropIndex(['gym_id', 'closed_at']);
        });

        Schema::table('memberships', function (Blueprint $table) {
            $table->dropIndex(['gym_id', 'ends_on']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['gym_id', 'checked_in_at']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['gym_id', 'paid_at']);
        });

        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['gym_id', 'document_number']);
        });
    }
};
