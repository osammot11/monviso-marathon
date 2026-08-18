<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->decimal('price', 8, 2);
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('event_categories')->insert([
            ['code' => '21k', 'name' => '21KM Competitiva', 'price' => 30, 'capacity' => 1600, 'is_active' => true, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '10k', 'name' => '10KM non Competitiva', 'price' => 10, 'capacity' => 1600, 'is_active' => true, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '5k', 'name' => '5KM Passeggiata', 'price' => 5, 'capacity' => null, 'is_active' => true, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('event_category_id')->nullable()->after('route_choice')->constrained()->nullOnDelete();
        });

        foreach (DB::table('event_categories')->get() as $category) {
            DB::table('tickets')->where('route_choice', $category->name)->update(['event_category_id' => $category->id]);
        }
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_category_id');
        });
        Schema::dropIfExists('event_categories');
    }
};
