<?php

namespace Tests\Feature;

use App\Filament\Resources\ClassSessionResource\Pages\CreateClassSession;
use App\Filament\Resources\CourseResource\Pages\CreateCourse;
use App\Filament\Resources\StudyClassResource\Pages\CreateStudyClass;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\StudyClass;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UniqueValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private CourseSchedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $studyClass = StudyClass::create([
            'code' => 'TI-4A',
            'name' => 'Teknik Informatika 4A',
            'academic_year' => '2026/2027',
            'semester' => 1,
            'is_active' => true,
        ]);

        $course = Course::create([
            'code' => 'PBW001',
            'name' => 'Pemrograman Web',
            'credits' => 3,
            'is_active' => true,
        ]);

        $this->schedule = CourseSchedule::create([
            'study_class_id' => $studyClass->id,
            'course_id' => $course->id,
            'lecturer_name' => 'Budi',
            'day_of_week' => 'Senin',
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'room' => 'R-101',
            'is_active' => true,
        ]);

        ClassSession::create([
            'course_schedule_id' => $this->schedule->id,
            'session_number' => 1,
            'session_date' => '2026-11-05',
            'topic' => 'Dasar Web',
        ]);
    }

    public function test_duplicate_course_code_is_rejected(): void
    {
        Livewire::test(CreateCourse::class)
            ->fillForm([
                'code' => 'PBW001',
                'name' => 'Mata Kuliah Lain',
                'credits' => 2,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['code']);

        $this->assertDatabaseCount('courses', 1);
    }

    public function test_duplicate_study_class_code_is_rejected(): void
    {
        Livewire::test(CreateStudyClass::class)
            ->fillForm([
                'code' => 'TI-4A',
                'name' => 'Kelas Lain',
                'academic_year' => '2026/2027',
                'semester' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['code']);

        $this->assertDatabaseCount('study_classes', 1);
    }

    public function test_duplicate_session_number_on_same_schedule_is_rejected(): void
    {
        Livewire::test(CreateClassSession::class)
            ->fillForm([
                'course_schedule_id' => $this->schedule->id,
                'session_number' => 1,
                'session_date' => '2026-11-12',
            ])
            ->call('create')
            ->assertHasFormErrors(['session_number']);

        $this->assertDatabaseCount('class_sessions', 1);
    }

    public function test_same_session_number_on_another_schedule_is_allowed(): void
    {
        $otherStudyClass = StudyClass::create([
            'code' => 'TI-4B',
            'name' => 'Teknik Informatika 4B',
            'academic_year' => '2026/2027',
            'semester' => 1,
            'is_active' => true,
        ]);

        $otherSchedule = CourseSchedule::create([
            'study_class_id' => $otherStudyClass->id,
            'course_id' => Course::first()->id,
            'lecturer_name' => 'Budi',
            'day_of_week' => 'Selasa',
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'room' => 'R-102',
            'is_active' => true,
        ]);

        Livewire::test(CreateClassSession::class)
            ->fillForm([
                'course_schedule_id' => $otherSchedule->id,
                'session_number' => 1,
                'session_date' => '2026-11-12',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseCount('class_sessions', 2);
    }
}
