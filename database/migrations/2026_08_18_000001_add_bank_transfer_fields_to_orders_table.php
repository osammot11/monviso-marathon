<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('booking_code')->nullable()->unique()->after('group_code');
            $table->string('public_token_hash', 64)->nullable()->unique()->after('booking_code');
            $table->string('booking_otp_hash', 64)->nullable()->after('public_token_hash');
            $table->timestamp('booking_otp_expires_at')->nullable()->after('booking_otp_hash');
            $table->string('payment_contact_name')->nullable()->after('association_name');
            $table->string('payment_contact_email')->nullable()->after('payment_contact_name');
            $table->string('bank_reference')->nullable()->unique()->after('status');
            $table->timestamp('payment_due_at')->nullable()->after('bank_reference');
            $table->string('transfer_receipt_path')->nullable()->after('payment_due_at');
            $table->timestamp('transfer_receipt_uploaded_at')->nullable()->after('transfer_receipt_path');
            $table->timestamp('transfer_reminder_sent_at')->nullable()->after('transfer_receipt_uploaded_at');
            $table->timestamp('paid_at')->nullable()->after('transfer_reminder_sent_at');
            $table->timestamp('transfer_verified_at')->nullable()->after('paid_at');
            $table->string('transfer_verification_method')->nullable()->after('transfer_verified_at');
            $table->foreignId('transfer_verified_by')->nullable()->after('transfer_verification_method')->constrained('users')->nullOnDelete();
            $table->text('transfer_review_reason')->nullable()->after('transfer_verified_by');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('transfer_verified_by');
            $table->dropColumn([
                'booking_code', 'public_token_hash', 'booking_otp_hash', 'booking_otp_expires_at', 'payment_contact_name', 'payment_contact_email',
                'bank_reference', 'payment_due_at', 'transfer_receipt_path', 'transfer_receipt_uploaded_at',
                'transfer_reminder_sent_at', 'paid_at', 'transfer_verified_at',
                'transfer_verification_method', 'transfer_review_reason',
            ]);
        });
    }
};
