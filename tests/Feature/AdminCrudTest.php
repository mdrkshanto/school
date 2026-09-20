<?php

use App\Models\AcademicSession;
use App\Models\GradeRule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Vehicle;

use function Pest\Laravel\actingAs;

it('allows an admin user to view the dashboard', function (): void {
    actingAs(panelUser('admin'), 'admin')
        ->get(route('admin.dashboard'))
        ->assertOk();
});

it('creates a grade rule', function (): void {
    actingAs(panelUser('admin'), 'admin')
        ->post(route('admin.grades.store'), [
            'name' => 'A+',
            'grade_point' => 5.0,
            'min_mark' => 90,
            'max_mark' => 100,
        ])
        ->assertRedirect(route('admin.grades.index'));

    $this->assertDatabaseHas('grade_rules', [
        'name' => 'A+',
        'grade_point' => 5.0,
        'min_mark' => 90,
        'max_mark' => 100,
    ]);
});

it('rejects a grade rule when max is not greater than min', function (): void {
    actingAs(panelUser('admin'), 'admin')
        ->post(route('admin.grades.store'), [
            'name' => 'A+',
            'grade_point' => 5.0,
            'min_mark' => 90,
            'max_mark' => 80,
        ])
        ->assertSessionHasErrors('max_mark');

    $this->assertDatabaseCount('grade_rules', 0);
});

it('updates a grade rule', function (): void {
    $rule = GradeRule::factory()->create(['name' => 'A']);

    actingAs(panelUser('admin'), 'admin')
        ->put(route('admin.grades.update', $rule), [
            'name' => 'B',
            'grade_point' => 4.0,
            'min_mark' => 80,
            'max_mark' => 89,
        ])
        ->assertRedirect(route('admin.grades.index'));

    $this->assertDatabaseHas('grade_rules', [
        'id' => $rule->id,
        'name' => 'B',
        'grade_point' => 4.0,
    ]);
});

it('creates a class with linked subjects', function (): void {
    $subject = Subject::factory()->create();

    actingAs(panelUser('admin'), 'admin')
        ->post(route('admin.classes.store'), [
            'name' => 'Nursery',
            'numeric_value' => 0,
            'status' => 'active',
            'subject_ids' => [$subject->id],
        ])
        ->assertRedirect(route('admin.classes.index'));

    $class = SchoolClass::where('name', 'Nursery')->first();

    $this->assertNotNull($class);
    $this->assertTrue($class->subjects()->whereKey($subject->id)->exists());
});

it('validates required fields when creating an academic session', function (): void {
    actingAs(panelUser('admin'), 'admin')
        ->post(route('admin.sessions.store'), [])
        ->assertSessionHasErrors(['name', 'starts_at', 'ends_at']);

    $this->assertDatabaseCount('academic_sessions', 0);
});

it('creates an academic session', function (): void {
    actingAs(panelUser('admin'), 'admin')
        ->post(route('admin.sessions.store'), [
            'name' => '2026-2027',
            'starts_at' => '2026-01-01',
            'ends_at' => '2026-12-31',
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.sessions.index'));

    $this->assertDatabaseHas('academic_sessions', [
        'name' => '2026-2027',
        'is_active' => true,
    ]);
});

it('ensures only one active session exists', function (): void {
    AcademicSession::factory()->create(['is_active' => true]);

    actingAs(panelUser('admin'), 'admin')
        ->post(route('admin.sessions.store'), [
            'name' => '2027-2028',
            'starts_at' => '2027-01-01',
            'ends_at' => '2027-12-31',
            'is_active' => true,
        ]);

    $this->assertSame(1, AcademicSession::where('is_active', true)->count());
});

it('allows an admin to delete a grade rule', function (): void {
    $rule = GradeRule::factory()->create();

    actingAs(panelUser('admin'), 'admin')
        ->delete(route('admin.grades.destroy', $rule))
        ->assertStatus(302);

    $this->assertDatabaseMissing('grade_rules', ['id' => $rule->id]);
});

it('allows an admin to view the vehicles index', function (): void {
    Vehicle::factory()->count(5)->create();

    actingAs(panelUser('admin'), 'admin')
        ->get(route('admin.vehicles.index'))
        ->assertOk();
});
