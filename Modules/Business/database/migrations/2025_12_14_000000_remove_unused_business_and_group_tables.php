<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Removes unused Business and Group functionality
     */
    public function up(): void
    {
        // First, remove foreign key constraints and columns from brands table
        Schema::table('brands', function (Blueprint $table) {
            // Drop foreign keys if they exist
            if (Schema::hasColumn('brands', 'business_id')) {
                try {
                    $table->dropForeign(['business_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
                $table->dropColumn('business_id');
            }

            if (Schema::hasColumn('brands', 'group_id')) {
                try {
                    $table->dropForeign(['group_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
                $table->dropColumn('group_id');
            }
        });

        // Drop the groups table
        Schema::dropIfExists('groups');

        // Drop the businesses table
        Schema::dropIfExists('businesses');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate businesses table
        if (!Schema::hasTable('businesses')) {
            Schema::create('businesses', function (Blueprint $table) {
                $table->id();
                $table->string('retailer_name');
                $table->string('retailer_email')->nullable();
                $table->string('phone_number')->nullable();
                $table->string('business_type')->nullable();
                $table->string('license_file')->nullable();
                $table->string('logo')->nullable();
                $table->string('status')->default('pending');
                $table->string('operation_type')->nullable();
                $table->string('slug')->nullable();
                $table->unsignedBigInteger('create_user')->nullable();
                $table->unsignedBigInteger('update_user')->nullable();
                $table->timestamps();
            });
        }

        // Recreate groups table
        if (!Schema::hasTable('groups')) {
            Schema::create('groups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->nullable();
                $table->string('name');
                $table->timestamps();

                $table->foreign('business_id')->references('id')->on('businesses')->onDelete('set null');
            });
        }

        // Add columns back to brands
        Schema::table('brands', function (Blueprint $table) {
            if (!Schema::hasColumn('brands', 'business_id')) {
                $table->unsignedBigInteger('business_id')->nullable()->after('id');
                $table->foreign('business_id')->references('id')->on('businesses')->onDelete('set null');
            }

            if (!Schema::hasColumn('brands', 'group_id')) {
                $table->unsignedBigInteger('group_id')->nullable();
                $table->foreign('group_id')->references('id')->on('groups')->onDelete('set null');
            }
        });
    }
};
