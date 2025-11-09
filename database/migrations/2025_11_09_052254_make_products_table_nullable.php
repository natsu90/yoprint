<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->string('style')->nullable()->change();
            $table->string('mainframe_color', length: 30)->nullable()->change();
            $table->string('size', length: 10)->nullable()->change();
            $table->string('color', length: 30)->nullable()->change();
            $table->decimal('price')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('title')->change();
            $table->text('description')->change();
            $table->string('style')->change();
            $table->string('mainframe_color', length: 30)->change();
            $table->string('size', length: 10)->change();
            $table->string('color', length: 30)->change();
            $table->decimal('price')->change();
        });
    }
};
