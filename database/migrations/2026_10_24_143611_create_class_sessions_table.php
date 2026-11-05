
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_schedule_id')
                ->constrained('course_schedules')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('session_number');
            $table->date('session_date');
            $table->string('topic')->nullable();
            $table->timestamps();

            $table->unique([
                'course_schedule_id',
                'session_number',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sessions');
    }
};
