<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gyms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('timezone')->default('America/Argentina/Buenos_Aires');
            $table->string('currency', 3)->default('ARS');
            $table->unsignedSmallInteger('grace_days')->default(14);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // Los usuarios que trae Laravel pasan a pertenecer a un gimnasio y tener rol
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('gym_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('reception');   // owner | reception
            $table->boolean('active')->default(true);
        });

        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained()->cascadeOnDelete();
            $table->string('document_number');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->text('medical_notes')->nullable();
            $table->string('qr_code')->unique();
            $table->string('status')->default('active');   // active | inactive | suspended
            $table->date('joined_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['gym_id', 'document_number']);
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedBigInteger('price');
            $table->unsignedSmallInteger('duration_days')->default(30);
            $table->unsignedTinyInteger('visits_per_week')->nullable();     // null = libre
            $table->json('allowed_weekdays')->nullable();                   // [1,3,5] = lun, mié, vie
            $table->unsignedSmallInteger('capacity_per_slot')->nullable();  // 20 en spinning
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained();
            $table->string('time_slot')->nullable();        // tarde | noche, solo spinning
            $table->date('starts_on');
            $table->date('ends_on')->index();
            $table->unsignedBigInteger('price_charged');
            $table->date('grace_until')->nullable();       // prórroga
            $table->text('notes')->nullable();
            $table->string('status')->default('active');    // active | cancelled
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained();
            $table->foreignId('membership_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('method');                       // cash | transfer | card | online
            $table->dateTime('paid_at');
            $table->string('reference')->nullable();
            $table->foreignId('received_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamp('voided_at')->nullable();     // los pagos no se borran, se anulan
            $table->string('void_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->dateTime('checked_in_at');
            $table->string('method')->default('document'); // document | qr | manual
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['member_id', 'checked_in_at']);
        });

        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained()->cascadeOnDelete();
            $table->foreignId('opened_by')->constrained('users');
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->unsignedBigInteger('opening_amount')->default(0);
            $table->unsignedBigInteger('counted_amount')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_session_id')->constrained()->cascadeOnDelete();
            $table->string('type');                         // income | expense
            $table->string('concept');
            $table->unsignedBigInteger('amount');
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('message_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('type');                         // expiring | overdue | welcome
            $table->string('channel')->default('whatsapp');
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('sent_at');
            $table->string('status')->default('opened');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_logs');
        Schema::dropIfExists('cash_movements');
        Schema::dropIfExists('cash_sessions');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('members');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gym_id');
            $table->dropColumn(['role', 'active']);
        });

        Schema::dropIfExists('gyms');
    }
};