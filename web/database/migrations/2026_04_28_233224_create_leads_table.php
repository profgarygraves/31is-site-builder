<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();

            // Full submitted payload as JSON — captures any/all form fields,
            // not just name/email/phone. Lets the same lead pipeline serve
            // pasted HTML (Path A) where forms can have arbitrary fields.
            $table->json('payload_json');

            // Stable hash from form rewrite — lets us know which form on the
            // site captured the lead (hero CTA vs. footer signup, etc.).
            $table->string('source_form', 64)->nullable();

            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('referer', 500)->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index('site_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
