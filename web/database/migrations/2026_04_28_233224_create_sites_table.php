<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Subdomain — validated in code against ^[a-z0-9][a-z0-9-]{1,30}[a-z0-9]$
            $table->string('subdomain', 32)->unique();

            // Source: 'html' (Path A — pasted HTML) or 'template' (Path B — prelaunch_v1 etc.)
            $table->string('source_type', 16);

            // Path A
            $table->mediumText('html_content_raw')->nullable(); // original pasted, for re-edit
            $table->mediumText('html_content')->nullable();     // sanitized + form-rewritten output served to visitors

            // Path B
            $table->string('template_id', 64)->nullable();      // e.g. 'prelaunch_v1'
            $table->json('template_data')->nullable();          // structured fields the template renders

            $table->string('notify_email');                      // where leads get emailed
            $table->mediumText('thank_you_html')->nullable();    // optional custom thank-you content

            $table->boolean('is_published')->default(false);

            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
