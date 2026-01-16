<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
		Schema::create('employee_lifecycle', function (Blueprint $table) {
			$table->id();
			$table->foreignId('ad_user_id')->constrained('ad_users')->cascadeOnDelete();
			$table->string('event');
			$table->text('description')->nullable();
			$table->json('context')->nullable();
			$table->timestamps();
		});
    }
};
