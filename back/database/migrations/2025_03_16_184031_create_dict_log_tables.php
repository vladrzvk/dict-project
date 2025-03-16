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
        // Table pour les logs de requêtes
        Schema::create('dict_request_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_id')->index();
            $table->string('method', 10);
            $table->string('url');
            $table->string('ip', 45);
            $table->text('user_agent')->nullable();
            $table->text('referer')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('status_code')->nullable();
            $table->decimal('duration_ms', 10, 2)->nullable();
            $table->timestamps();
            
            $table->index('created_at');
        });
        
        // Table pour les logs d'erreurs
        Schema::create('dict_error_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_id')->nullable()->index();
            $table->string('type');
            $table->text('message');
            $table->text('context')->nullable();
            $table->text('stack_trace')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            
            $table->index('created_at');
        });
        
        // Table pour les événements de sécurité
        Schema::create('dict_event_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->text('description');
            $table->json('data')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            
            $table->index(['event_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dict_request_logs');
        Schema::dropIfExists('dict_error_logs');
        Schema::dropIfExists('dict_event_logs');
    }
};