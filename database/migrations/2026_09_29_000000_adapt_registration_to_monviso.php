<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $categories = [
            ['code' => 'm160k', 'name' => 'M160K', 'price' => 175, 'capacity' => 300, 'sort_order' => 1],
            ['code' => 'm50k', 'name' => 'M50K', 'price' => 60, 'capacity' => 500, 'sort_order' => 2],
            ['code' => 'm20k', 'name' => 'M20K', 'price' => 30, 'capacity' => 700, 'sort_order' => 3],
            ['code' => 'mtrail', 'name' => 'MTRAIL', 'price' => 40, 'capacity' => 500, 'sort_order' => 4],
        ];

        foreach ($categories as $category) {
            DB::table('event_categories')->updateOrInsert(
                ['code' => $category['code']],
                array_merge($category, ['is_active' => true, 'updated_at' => now(), 'created_at' => now()])
            );
        }

        DB::table('event_categories')->whereNotIn('code', array_column($categories, 'code'))->update(['is_active' => false]);

        foreach ($categories as $category) {
            $categoryId = DB::table('event_categories')->where('code', $category['code'])->value('id');
            DB::table('event_category_price_tiers')->updateOrInsert(
                ['event_category_id' => $categoryId, 'name' => 'Quota corrente'],
                ['price' => $category['price'], 'sort_order' => 0, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('membership_type')->nullable()->after('nationality');
            $table->string('membership_number')->nullable()->after('membership_type');
            $table->date('medical_certificate_expiry')->nullable()->after('membership_number');
            $table->string('emergency_contact_name')->nullable()->after('phone');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'membership_type', 'membership_number', 'medical_certificate_expiry',
                'emergency_contact_name', 'emergency_contact_phone',
            ]);
        });

        DB::table('event_categories')->whereIn('code', ['m160k', 'm50k', 'm20k', 'mtrail'])->delete();
        DB::table('event_categories')->whereIn('code', ['21k', '10k', '5k'])->update(['is_active' => true]);
    }
};
