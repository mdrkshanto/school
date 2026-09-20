<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\ClassTeacher;
use App\Models\Event;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\GradeRule;
use App\Models\Guardian;
use App\Models\Notice;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TransportRoute;
use App\Models\TransportStop;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoSeeder extends Seeder
{
    private AcademicSession $session;

    /** @var array<int, SchoolClass> */
    private array $classes = [];

    public function run(): void
    {
        $this->session = AcademicSession::firstOrCreate(
            ['name' => '2026-2027'],
            ['starts_at' => '2026-07-01', 'ends_at' => '2027-06-30', 'is_active' => true]
        );
        $this->session->update(['is_active' => true]);
        AcademicSession::query()->where('id', '!=', $this->session->id)->update(['is_active' => false]);

        $role = [
            'teacher' => Role::firstOrCreate(['name' => 'teacher']),
            'student' => Role::firstOrCreate(['name' => 'student']),
            'parent' => Role::firstOrCreate(['name' => 'parent']),
        ];

        $this->seedClassesAndSections();
        $this->seedSubjects();
        $this->seedTeachers($role['teacher']);
        $this->seedGuardians($role['parent']);
        $this->seedStudents($role['student']);
        $this->seedClassTeachers();
        $this->seedExams();
        $this->seedFees();
        $this->seedNotices();
        $this->seedEvents();
        $this->seedLibrary();
        $this->seedTransport();
    }

    private function seedClassesAndSections(): void
    {
        $names = ['Nursery', 'KG', 'One', 'Two', 'Three', 'Four', 'Five'];

        foreach ($names as $index => $name) {
            $class = SchoolClass::query()->firstOrCreate(
                ['name' => $name],
                ['numeric_value' => $index + 1, 'status' => 'active']
            );
            foreach (['A', 'B'] as $sectionName) {
                Section::query()->firstOrCreate(
                    ['class_id' => $class->id, 'name' => $sectionName],
                    ['capacity' => 40, 'status' => 'active']
                );
            }
            $this->classes[$class->id] = $class;
        }
    }

    private function seedSubjects(): void
    {
        $subjects = [
            ['Bangla', 'BAN'],
            ['English', 'ENG'],
            ['Mathematics', 'MATH'],
            ['Science', 'SCI'],
            ['Social Studies', 'SOC'],
            ['ICT & Digital Skills', 'ICT'],
        ];

        foreach ($subjects as [$name, $code]) {
            $subject = Subject::query()->firstOrCreate(
                ['name' => $name],
                ['code' => $code, 'status' => 'active']
            );
            foreach (array_slice($this->classes, 0, 6, true) as $class) {
                $class->subjects()->syncWithoutDetaching([$subject->id]);
            }
        }
    }

    private function seedTeachers(Role $role): void
    {
        $specializations = ['Bangla', 'English', 'Mathematics', 'Science', 'Social Studies', 'ICT'];

        foreach ($specializations as $i => $specialization) {
            $email = $i === 0 ? 'teacher@school.test' : "teacher{$i}@school.test";
            $user = User::query()->firstOrCreate(
                ['email' => $email],
                ['name' => "Mrs. ".($specializations[$i] ?? 'Teacher'), 'password' => Hash::make('password'), 'is_active' => true]
            );
            $user->assignRole($role);
            Teacher::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_no' => 'T-'.(100 + $i),
                    'name' => $user->name,
                    'gender' => $i % 2 === 0 ? 'female' : 'male',
                    'phone' => '01'.str_pad((string) (2000000000 + $i), 9, '0'),
                    'email' => $email,
                    'qualification' => ['BSc', 'MSc', 'M.Ed', 'M.Ed', 'BSc', 'MSc'][$i],
                    'specialization' => $specialization,
                    'join_date' => now()->subYears(1 + $i % 4)->toDateString(),
                    'status' => 'active',
                ]
            );
        }
    }

    private function seedGuardians(Role $role): void
    {
        for ($i = 0; $i < 8; $i++) {
            $email = $i === 0 ? 'parent@school.test' : "parent{$i}@school.test";
            $user = User::query()->firstOrCreate(
                ['email' => $email],
                ['name' => "Guardian User {$i}", 'password' => Hash::make('password'), 'is_active' => true]
            );
            $user->assignRole($role);
            Guardian::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $user->name,
                    'email' => $email,
                    'phone' => '01'.str_pad((string) (3000000000 + $i), 9, '0'),
                    'occupation' => ['Business', 'Teacher', 'Engineer', 'Doctor', 'Farmer', 'Banker', 'Civil Servant', 'Designer'][$i],
                    'status' => 'active',
                ]
            );
        }
    }

    private function seedStudents(Role $role): void
    {
        $classIds = array_keys($this->classes);
        $sections = Section::query()->get()->groupBy('class_id');
        $guardians = Guardian::query()->limit(8)->get();

        for ($i = 0; $i < 60; $i++) {
            $classId = $classIds[$i % count($classIds)];
            $section = $sections[$classId]->get($i % 2);
            $email = $i === 0 ? 'student@school.test' : "student{$i}@school.test";

            $user = User::query()->firstOrCreate(
                ['email' => $email],
                ['name' => "Student {$i}", 'password' => Hash::make('password'), 'is_active' => true]
            );
            $user->assignRole($role);

            $student = Student::query()->firstOrCreate(
                ['admission_no' => 'S-'.(10000 + $i)],
                [
                    'user_id' => $user->id,
                    'class_id' => $classId,
                    'section_id' => $section->id,
                    'session_id' => $this->session->id,
                    'name' => $user->name,
                    'gender' => $i % 2 === 0 ? 'female' : 'male',
                    'dob' => now()->subYears(5 + $i % 12)->subMonths($i % 12)->toDateString(),
                    'blood_group' => ['A+', 'B+', 'O+', 'AB-', 'O-', 'B-'][$i % 6],
                    'phone' => '01'.str_pad((string) (4000000000 + $i), 9, '0'),
                    'status' => 'active',
                ]
            );

            $guardian = $guardians->get($i % $guardians->count());
            $student->guardians()->syncWithoutDetaching([
                $guardian->id => ['relation' => $i % 2 === 0 ? 'father' : 'mother'],
            ]);
        }
    }

    private function seedClassTeachers(): void
    {
        $teachers = Teacher::query()->get();
        $writingTeachers = $teachers->take(4);

        foreach ($this->classes as $class) {
            foreach ($class->sections as $section) {
                $teacher = $writingTeachers->get($class->id % 4);
                ClassTeacher::query()->firstOrCreate(
                    ['session_id' => $this->session->id, 'class_id' => $class->id, 'section_id' => $section->id, 'teacher_id' => $teacher->id],
                    ['is_class_teacher' => true]
                );
            }
        }
    }

    private function seedExams(): void
    {
        $exam = Exam::query()->firstOrCreate(
            ['session_id' => $this->session->id, 'name' => 'Midterm 2026'],
            [
                'description' => 'First semester assessment.',
                'starts_at' => now()->subWeek()->startOfDay(),
                'ends_at' => now()->addWeeks(2)->endOfDay(),
                'status' => 'published',
            ]
        );

        GradeRule::query()->get()->each(fn (GradeRule $rule) => $rule);
        $rules = GradeRule::query()->orderByDesc('min_mark')->get();

        foreach (array_slice($this->classes, 0, 6, true) as $class) {
            foreach ($class->subjects as $subject) {
                ExamSchedule::query()->firstOrCreate(
                    ['exam_id' => $exam->id, 'class_id' => $class->id, 'subject_id' => $subject->id],
                    [
                        'date' => now()->addDays($class->id % 10)->toDateString(),
                        'start_time' => '10:00',
                        'end_time' => '12:00',
                        'full_marks' => 100,
                        'pass_marks' => 33,
                    ]
                );
            }
        }

        $students = Student::query()->with('class.subjects')->limit(30)->get();

        foreach ($students as $student) {
            $sectionId = $student->section_id;
            foreach ($student->class->subjects as $subject) {
                $isAbsent = fake()->boolean(10);
                $obtained = $isAbsent ? null : fake()->randomFloat(2, 25, 100);
                $grade = null;
                $point = null;

                if (! $isAbsent) {
                    foreach ($rules as $rule) {
                        if ($obtained >= (float) $rule->min_mark && $obtained <= (float) $rule->max_mark) {
                            $grade = $rule->name;
                            $point = $rule->grade_point;
                            break;
                        }
                    }
                }

                Result::query()->updateOrCreate(
                    ['exam_id' => $exam->id, 'student_id' => $student->id, 'subject_id' => $subject->id],
                    [
                        'class_id' => $student->class_id,
                        'section_id' => $sectionId,
                        'obtained_marks' => $obtained,
                        'grade' => $grade,
                        'grade_point' => $point,
                        'is_absent' => $isAbsent,
                    ]
                );
            }
        }
    }

    private function seedFees(): void
    {
        $classIds = collect($this->classes)->keys();
        $structures = [
            ['Admission Fee', 'admission', 5000, false],
            ['Tuition Fee', 'monthly', 1200, false],
            ['Exam Fee', 'one_time', 500, false],
            ['Transport Fee', 'transport', 800, true],
        ];

        foreach ($structures as [$name, $type, $amount, $optional]) {
            FeeStructure::query()->firstOrCreate(
                ['session_id' => $this->session->id, 'name' => $name],
                ['class_id' => null, 'type' => $type, 'amount' => $amount, 'is_optional' => $optional, 'status' => 'active']
            );
        }

        $structures = FeeStructure::query()->get();
        $students = Student::query()->limit(30)->get();

        foreach ($students as $student) {
            foreach ($structures as $idx => $structure) {
                if ($structure->type === 'transport' && $idx % 2 === 1) {
                    continue;
                }
                $invoice = FeeInvoice::query()->firstOrCreate(
                    ['session_id' => $this->session->id, 'student_id' => $student->id, 'fee_structure_id' => $structure->id],
                    [
                        'invoice_no' => 'INV-'.$student->admission_no.'-'.$structure->id,
                        'amount' => $structure->amount,
                        'paid_amount' => 0,
                        'due_date' => now()->addDays(15)->toDateString(),
                        'status' => 'pending',
                    ]
                );
                if ($idx % 2 === 0) {
                    $total = (float) $structure->amount;
                    FeePayment::query()->create([
                        'invoice_id' => $invoice->id,
                        'amount' => $total,
                        'method' => ['cash', 'bank', 'online'][$idx % 3],
                        'paid_at' => now()->subDays($idx + 1),
                    ]);
                    $invoice->update(['paid_amount' => $total, 'status' => 'paid']);
                }
            }
        }
    }

    private function seedNotices(): void
    {
        $samples = [
            ['Annual Sports Day', 'The annual sports day will be held next month. All students must register with their class teacher.', 'academic', 'all'],
            ['Parent-Teacher Conference', 'A parent-teacher meeting is scheduled for the last Friday of the month in the main auditorium.', 'general', 'parents'],
            ['Emergency Closure Notice', 'School will remain closed tomorrow due to the weather advisory. Stay safe.', 'urgent', 'all'],
        ];

        foreach ($samples as $i => [$title, $body, $category, $audience]) {
            Notice::query()->firstOrCreate(
                ['title' => $title],
                ['body' => $body, 'category' => $category, 'audience' => $audience, 'published_at' => now()->subDays($i), 'user_id' => User::where('email', 'admin@school.test')->first()?->id]
            );
        }
    }

    private function seedEvents(): void
    {
        $samples = [
            ['Parents Day', 'Celebration with parents and guardians.', 'Main Hall', 'indigo'],
            ['Science Fair', 'Student science projects exhibition.', 'Laboratory Block', 'emerald'],
            ['Cultural Week', 'A week of cultural performances.', 'Auditorium', 'rose'],
        ];

        foreach ($samples as $i => [$title, $desc, $location, $color]) {
            Event::query()->firstOrCreate(
                ['title' => $title],
                ['description' => $desc, 'location' => $location, 'starts_at' => now()->addDays(10 + $i * 5)->startOfDay(), 'ends_at' => now()->addDays(10 + $i * 5)->addHours(4), 'color' => $color, 'is_public' => true]
            );
        }
    }

    private function seedLibrary(): void
    {
        $titles = [
            ['One Hundred Years of Solitude', 'Gabriel Garcia Marquez', 'Fiction'],
            ['A Brief History of Time', 'Stephen Hawking', 'Science'],
            ['The Great Gatsby', 'F. Scott Fitzgerald', 'Fiction'],
            ['Elementary Mathematics', 'Prof. A. Rahman', 'Reference'],
            ['World History Atlas', 'R. Chandra', 'History'],
            ['Chemistry Made Easy', 'L. Novak', 'Science'],
        ];

        foreach ($titles as $i => [$title, $author, $category]) {
            $book = Book::query()->firstOrCreate(
                ['title' => $title],
                ['author' => $author, 'isbn' => '978'.sprintf('%012d', 10000000 + $i), 'category' => $category, 'quantity' => 5, 'available_count' => 5, 'status' => 'active']
            );

            if ($i === 0) {
                $borrower = User::where('email', 'student@school.test')->first();
                if ($borrower) {
                    BookIssue::query()->firstOrCreate(
                        ['book_id' => $book->id, 'user_id' => $borrower->id, 'status' => 'issued'],
                        ['issued_at' => now()->subDays(5), 'due_date' => now()->addDays(10)->toDateString()]
                    );
                    $book->decrement('available_count');
                }
            }
        }
    }

    private function seedTransport(): void
    {
        $routeA = TransportRoute::query()->firstOrCreate(['name' => 'Route North'], ['status' => 'active']);
        $routeB = TransportRoute::query()->firstOrCreate(['name' => 'Route South'], ['status' => 'active']);

        $stops = [
            [$routeA->id, 'Main Gate', 50, 0],
            [$routeA->id, 'Garden Road', 120, 1],
            [$routeA->id, 'Central Bus Stand', 200, 2],
            [$routeB->id, 'City Center', 50, 0],
            [$routeB->id, 'River View', 150, 1],
        ];

        foreach ($stops as [$routeId, $name, $fare, $order]) {
            TransportStop::query()->firstOrCreate(
                ['route_id' => $routeId, 'name' => $name],
                ['fare' => $fare, 'order_index' => $order]
            );
        }

        $v1 = Vehicle::query()->firstOrCreate(['registration_no' => 'DHA-11-1234'], ['model' => 'Toyota HiAce', 'capacity' => 24, 'driver_name' => 'Abdul Karim', 'status' => 'active']);
        $v2 = Vehicle::query()->firstOrCreate(['registration_no' => 'DHA-12-9876'], ['model' => 'Mitsubishi Rosa', 'capacity' => 30, 'driver_name' => 'Shahid Khan', 'status' => 'active']);

        $routeA->vehicles()->syncWithoutDetaching([$v1->id]);
        $routeB->vehicles()->syncWithoutDetaching([$v2->id]);
    }
}