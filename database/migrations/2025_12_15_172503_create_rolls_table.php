
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rolls', function (Blueprint $table) {
            $table->id();

            // Human identity
            $table->string('name', 160);                 // e.g. "Flex 4ft", "Sticker 5ft"
            $table->string('slug', 200)->unique();       // for stable reference
            $table->string('material_type', 60);         // flex|sticker|vinyl|banner|etc (enum later if you want)

            // Physical constraint (store in inches)
            $table->decimal('width_in', 10, 3);          // fixed roll width in inches (e.g. 48.000)

            // Operational
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();            // future: supplier, gsm, finish, notes

            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['material_type', 'is_active']);
            $table->index(['width_in']);

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rolls');
    }
};
