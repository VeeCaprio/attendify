<?php

namespace Tests\Feature;

use App\Filament\Resources\AttendanceResource\Pages\CreateAttendance;
use App\Filament\Resources\AttendanceResource\Pages\EditAttendance;
use App\Filament\Resources\AttendanceResource\Pages\ListAttendances;
use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\Student;
use App\Models\StudyClass;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AttendanceCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private ClassSession $classSession;

    private Student $student;

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
            'semester' => 4,
            'is_active' => true,
        ]);

        $course = Course::create([
            'code' => 'PBW001',
            'name' => 'Pemrograman Web',
            'credits' => 3,
            'is_active' => true,
        ]);

        $schedule = CourseSchedule::create([
            'study_class_id' => $studyClass->id,
            'course_id' => $course->id,
            'lecturer_name' => 'Budi',
            'day_of_week' => 'Senin',
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'room' => 'R-101',
            'is_active' => true,
        ]);

        $this->classSession = ClassSession::create([
            'course_schedule_id' => $schedule->id,
            'session_number' => 1,
            'session_date' => '2026-11-05',
            'topic' => 'Dasar Web',
        ]);

        $this->student = Student::create([
            'nim' => '2024001',
            'name' => 'Alice',
            'status' => 'aktif',
        ]);
    }

    public function test_list_attendance_page_renders(): void
    {
        Livewire::test(ListAttendances::class)
            ->assertSuccessful()
            ->assertSee('Data Absensi');
    }

    public function test_can_create_attendance(): void
    {
        Livewire::test(CreateAttendance::class)
            ->fillForm([
                'class_session_id' => $this->classSession->id,
                'student_id' => $this->student->id,
                'status' => 'Hadir',
                'notes' => 'Tepat waktu',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('attendances', [
            'class_session_id' => $this->classSession->id,
            'student_id' => $this->student->id,
            'status' => 'Hadir',
            'notes' => 'Tepat waktu',
        ]);
    }

    public function test_can_edit_attendance(): void
    {
        $attendance = Attendance::create([
            'class_session_id' => $this->classSession->id,
            'student_id' => $this->student->id,
            'status' => 'Hadir',
            'notes' => null,
        ]);

        Livewire::test(EditAttendance::class, ['record' => $attendance->getKey()])
            ->fillForm([
                'status' => 'Sakit',
                'notes' => 'Demam',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 'Sakit',
            'notes' => 'Demam',
        ]);
    }

    public function test_can_delete_attendance_from_list(): void
    {
        $attendance = Attendance::create([
            'class_session_id' => $this->classSession->id,
            'student_id' => $this->student->id,
            'status' => 'Alpa',
        ]);

        Livewire::test(ListAttendances::class)
            ->callTableAction('delete', $attendance)
            ->assertSuccessful();

        $this->assertDatabaseMissing('attendances', [
            'id' => $attendance->id,
        ]);
    }

    public function test_duplicate_attendance_is_rejected(): void
    {
        Attendance::create([
            'class_session_id' => $this->classSession->id,
            'student_id' => $this->student->id,
            'status' => 'Hadir',
        ]);

        Livewire::test(CreateAttendance::class)
            ->fillForm([
                'class_session_id' => $this->classSession->id,
                'student_id' => $this->student->id,
                'status' => 'Izin',
            ])
            ->call('create')
            ->assertHasFormErrors(['student_id']);
    }
}
