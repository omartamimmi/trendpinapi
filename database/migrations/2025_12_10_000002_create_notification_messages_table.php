<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->nullable()->constrained('notification_templates')->nullOnDelete();
            $table->string('tag'); // nearby, new_offer, offer_expiring, etc
            $table->string('title');
            $table->text('body');
            $table->json('channels'); // ["push", "email", "sms"]
            $table->string('target_type'); // all, location, segment, individual
            $table->json('target_criteria')->nullable(); // {radius: 5, city: "Amman"}
            $table->string('status')->default('draft'); // draft, scheduled, sending, sent, failed
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->json('delivery_stats')->nullable(); // {push: 100, email: 95, failed: 5}
            $table->json('action_data')->nullable(); // {type: "brands", ids: [1,2,3]}
            $table->string('image_url')->nullable();
            $table->string('deep_link')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
            $table->index('tag');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_messages');
    }
};
