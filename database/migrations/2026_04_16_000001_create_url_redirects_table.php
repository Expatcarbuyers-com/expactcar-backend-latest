<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('url_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('old_slug', 250)->unique();
            $table->string('new_slug', 250);
            $table->boolean('is_permanent')->default(true); // true = 301, false = 302
            $table->timestamps();

            $table->index('old_slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('url_redirects');
    }
};
