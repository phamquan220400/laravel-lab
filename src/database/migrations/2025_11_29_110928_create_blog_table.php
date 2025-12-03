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
        Schema::create('blog_tab', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(false)->unique();
            $table->text('content')->nullable(true);
            $table->timestamps();
            $table->softDeletes();

            // Audit columns matching users.id (unsigned big integer)
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

            //Primary Key
            $table->primary('id');

            // Indexes
            $table->index('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog');
    }
};
