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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            
            // Quotation Basic Information
            $table->string('quotation_number')->unique();
            $table->string('reference_number')->nullable();
            
            // Customer Information
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->enum('customer_type', ['retail', 'wholesale'])->default('retail');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->text('customer_address')->nullable();
            
            // Dates
            $table->date('quotation_date');
            $table->date('valid_until');
            
            // Pricing Information
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('additional_discount', 10, 2)->default(0);
$table->string('additional_discount_type', 20)->default('fixed'); // 'fixed' or 'percentage'
$table->decimal('additional_discount_value', 10, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('shipping_charges', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            
            // Items Storage (JSON format for simplicity)
            $table->json('items')->nullable();
            
            // Terms & Conditions
            $table->text('terms_conditions')->nullable();
            $table->text('notes')->nullable();
            
            // Status Information
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired', 'converted'])->default('draft');
            $table->text('rejection_reason')->nullable();
            
            // Timestamps
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            
            // User Information
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('cascade');
            
            // Soft Deletes & Timestamps
            
            $table->timestamps();

            // Indexes for better performance
            $table->index('quotation_number');
            $table->index('customer_id');
            $table->index('quotation_date');
            $table->index('valid_until');
            $table->index('status');
            $table->index('created_by');
            $table->index(['status', 'valid_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};