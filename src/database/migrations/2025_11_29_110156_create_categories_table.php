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
        Schema::create('categories_tab', function (Blueprint $table) {
            $table->id();
            $table->string('description')->nullable(false);
            $table->string('category_type')->nullable(false)->unique();
            $table->string('status')
                ->nullable(false)
                ->default('active');
            $table->timestamps();
            $table->softDeletes();

            // Audit columns matching users.id (unsigned big integer)
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

            // Indexes
            $table->index('category_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories_tab');
    }
};
