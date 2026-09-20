<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\GradeRule;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Support\Collection;

class ResultService
{
    /**
     * Resolve the grade (letter + point) for a given score.
     *
     * @return array{name: string, point: string}|null
     */
    public function gradeFor(float $marks): ?array
    {
        $rule = GradeRule::query()
            ->where('min_mark', '<=', $marks)
            ->where('max_mark', '>=', $marks)
            ->orderBy('grade_point', 'desc')
            ->first();

        if ($rule === null) {
            return null;
        }

        return [
            'name' => $rule->name,
            'point' => (string) $rule->grade_point,
        ];
    }

    /**
     * Generate (or refresh) result rows for every student of a class/section
     * across the exam's schedules. Returns the number of rows created/updated.
     */
    public function generateResults(Exam $exam, SchoolClass $class, ?int $sectionId = null): int
    {
        $schedules = ExamSchedule::query()
            ->where('exam_id', $exam->id)
            ->where('class_id', $class->id)
            ->get();

        if ($schedules->isEmpty()) {
            return 0;
        }

        $students = Student::query()
            ->where('class_id', $class->id)
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->active()
            ->get();

        /** @var Collection<int, array{name:string, point:string, min:float, max:float}> $rules */
        $rules = GradeRule::query()
            ->get()
            ->map(fn (GradeRule $rule) => [
                'name' => $rule->name,
                'point' => (string) $rule->grade_point,
                'min' => (float) $rule->min_mark,
                'max' => (float) $rule->max_mark,
            ])
            ->sortByDesc('min')
            ->values();

        $count = 0;

        foreach ($students as $student) {
            foreach ($schedules as $schedule) {
                $current = Result::query()
                    ->where('exam_id', $exam->id)
                    ->where('student_id', $student->id)
                    ->where('subject_id', $schedule->subject_id)
                    ->first();

                if ($current === null) {
                    Result::query()->create([
                        'exam_id' => $exam->id,
                        'class_id' => $class->id,
                        'section_id' => $sectionId ?? $student->section_id,
                        'student_id' => $student->id,
                        'subject_id' => $schedule->subject_id,
                        'obtained_marks' => null,
                        'is_absent' => false,
                    ]);
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Build a marksheet for a student for the given exam.
     *
     * @return array<string, mixed>
     */
    public function marksheet(Student $student, Exam $exam): array
    {
        $schedules = ExamSchedule::query()
            ->where('exam_id', $exam->id)
            ->with('subject')
            ->get()
            ->keyBy('subject_id');

        $results = Result::query()
            ->where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->get();

        $rows = $results->map(function (Result $result) use ($schedules) {
            $schedule = $schedules->get($result->subject_id);

            return [
                'subject' => $result->subject->name ?? ($schedule->subject->name ?? ''),
                'full_marks' => (float) ($schedule->full_marks ?? 100),
                'pass_marks' => (float) ($schedule->pass_marks ?? 33),
                'obtained' => $result->is_absent ? null : (float) $result->obtained_marks,
                'grade' => $result->grade,
                'grade_point' => $result->grade_point,
                'is_absent' => $result->is_absent,
            ];
        })->values();

        $graded = $rows->filter(fn ($row) => ! $row['is_absent'] && $row['obtained'] !== null);
        $totalFull = $rows->sum('full_marks');
        $totalObtained = $graded->sum('obtained');

        $gpa = $graded->count() > 0
            ? round($graded->sum('grade_point') / $graded->count(), 2)
            : 0;

        $passRows = $graded->filter(fn ($row) => $row['obtained'] >= $row['pass_marks']);
        $passed = $graded->count() === $passRows->count();

        return [
            'rows' => $rows,
            'total_marks' => round($totalObtained, 2),
            'total_full' => $totalFull,
            'gpa' => $gpa,
            'passed' => $passed,
            'grade' => $this->resolveGpaGrade($gpa),
        ];
    }

    public function resolveGpaGrade(float $gpa): string
    {
        return match (true) {
            $gpa >= 5 => 'A+',
            $gpa >= 4 => 'A',
            $gpa >= 3.5 => 'A-',
            $gpa >= 3 => 'B',
            $gpa >= 2 => 'C',
            $gpa >= 1 => 'D',
            default => 'F',
        };
    }
}
