<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_import_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('mapping');
            $table->string('delimiter', 4)->default(';');
            $table->string('date_format')->default('d/m/Y');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('fingerprint', 64)->unique();
            $table->string('external_id')->nullable()->index();
            $table->date('booked_at');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->text('description');
            $table->foreignId('matched_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('match_status')->default('unmatched');
            $table->json('raw_data')->nullable();
            $table->timestamps();
        });

        Schema::create('bank_transfer_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transfer_audits');
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_import_profiles');
    }
};
