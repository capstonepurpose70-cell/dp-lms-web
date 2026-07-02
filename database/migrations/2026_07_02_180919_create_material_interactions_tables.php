<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Safety-net migration for material interactions (views / likes / comments).
 *
 * Each table is wrapped in Schema::hasTable() — so if these tables already
 * exist (the feature was built earlier), this migration does NOTHING and is
 * safe to run. It only creates whatever is missing. No existing data touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('material_views')) {
            Schema::create('material_views', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('learning_material_id');
                $t->unsignedBigInteger('user_id');
                $t->timestamps();
                $t->index('learning_material_id');
                $t->unique(['learning_material_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('material_likes')) {
            Schema::create('material_likes', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('learning_material_id');
                $t->unsignedBigInteger('user_id');
                $t->timestamps();
                $t->index('learning_material_id');
                $t->unique(['learning_material_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('material_comments')) {
            Schema::create('material_comments', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('learning_material_id');
                $t->unsignedBigInteger('user_id');
                $t->text('body');
                $t->timestamps();
                $t->index('learning_material_id');
            });
        }
    }

    public function down(): void
    {
        // Left intentionally empty so we never drop real data on rollback.
    }
};