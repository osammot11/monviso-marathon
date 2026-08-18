<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_category_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price', 8, 2);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('event_addons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->decimal('price', 8, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->decimal('base_price', 8, 2)->default(0)->after('price_paid');
            $table->decimal('addon_amount', 8, 2)->default(0)->after('base_price');
        });
        DB::table('tickets')->update(['base_price' => DB::raw('price_paid')]);

        $now = now();
        foreach (DB::table('event_categories')->get() as $category) {
            DB::table('event_category_price_tiers')->insert([
                'event_category_id' => $category->id, 'name' => 'Quota corrente',
                'price' => $category->price, 'sort_order' => 0, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        DB::table('event_addons')->insert([
            'code' => 'shuttle', 'name' => 'Navetta', 'price' => 0, 'is_active' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::table('tickets', fn (Blueprint $table) => $table->dropColumn(['base_price', 'addon_amount']));
        Schema::dropIfExists('event_addons');
        Schema::dropIfExists('event_category_price_tiers');
    }
};
