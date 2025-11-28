<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class)->in(__DIR__);
uses(RefreshDatabase::class)->in(__DIR__);
use function Pest\Livewire\livewire;

it('can load the page', function () {
    $skills = \App\Models\Skill::factory()->count(5)->create();

    livewire(\App\Filament\Clusters\Skills\Pages\ListSkills::class)
        ->assertOk()
        ->assertCanSeeTableRecords($skills);
});

it('shows proficiency only for technical category in form', function () {
    livewire(\App\Filament\Clusters\Skills\Pages\CreateSkill::class)
        ->fillForm([
            'name' => 'Non Technical Skill',
            'category' => 'management',
            'description' => 'desc',
            'is_active' => true,
        ])
        ->assertFormFieldIsHidden('proficiency_level')
        ->fillForm([
            'category' => 'technical',
            'proficiency_level' => 3,
        ])
        ->assertFormFieldIsVisible('proficiency_level')
        ->call('create')
        ->assertHasNoFormErrors();
});

it('validates proficiency range when technical', function () {
    livewire(\App\Filament\Clusters\Skills\Pages\CreateSkill::class)
        ->fillForm([
            'name' => 'Tech Skill',
            'category' => 'technical',
            'proficiency_level' => 6,
            'description' => 'desc',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['proficiency_level' => ['max']]);
});

it('validates required fields and unique name', function () {
    $existing = \App\Models\Skill::factory()->create(['name' => 'Existing Name']);

    livewire(\App\Filament\Clusters\Skills\Pages\CreateSkill::class)
        ->fillForm([
            'name' => '',
            'category' => '',
            'is_active' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => ['required'],
            'category' => ['required'],
            'is_active' => ['required'],
        ])
        ->fillForm([
            'name' => $existing->name,
            'category' => 'soft',
            'description' => 'x',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => ['unique']]);
});

it('validates notes max length business rule', function () {
    $longNotes = str_repeat('a', 201);

    livewire(\App\Filament\Clusters\Skills\Pages\CreateSkill::class)
        ->fillForm([
            'name' => 'Note Rule',
            'category' => 'soft',
            'description' => 'x',
            'is_active' => true,
            'notes' => $longNotes,
        ])
        ->call('create')
        ->assertHasFormErrors(['notes' => ['max']]);
});

it('business rule: Archive action visible only when active', function () {
    $active = \App\Models\Skill::factory()->create(['is_active' => true]);
    $inactive = \App\Models\Skill::factory()->create(['is_active' => false]);

    livewire(\App\Filament\Clusters\Skills\Pages\ListSkills::class)
        ->assertOk()
        ->assertTableActionVisible('Archive', $active)
        ->assertTableActionHidden('Archive', $inactive);
});

it('business rule: seeding skips existing names', function () {
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    \App\Models\Skill::factory()->create(['name' => 'Skill One']);

    Illuminate\Support\Facades\Http::fake([
        'https://dummyjson.com/*' => Illuminate\Support\Facades\Http::response([
            'products' => [
                ['title' => 'Skill One', 'category' => 'technical', 'description' => 'one'],
                ['title' => 'Skill New', 'category' => 'soft', 'description' => 'new'],
            ],
        ], 200),
    ]);

    \App\Filament\Clusters\Skills\Tables\SkillsTable::seedSkills();

    expect(\App\Models\Skill::where('name', 'Skill One')->count())->toBe(1);
    expect(\App\Models\Skill::where('name', 'Skill New')->exists())->toBeTrue();
});

it('can view, edit, and delete a skill via record pages', function () {
    $skill = \App\Models\Skill::factory()->create([
        'category' => 'technical',
        'proficiency_level' => 2,
        'is_active' => true,
    ]);

    livewire(\App\Filament\Clusters\Skills\Pages\ViewSkill::class, ['record' => $skill->getRouteKey()])
        ->assertOk()
        ->assertSee($skill->name);

    livewire(\App\Filament\Clusters\Skills\Pages\EditSkill::class, ['record' => $skill->getRouteKey()])
        ->assertOk()
        ->fillForm(['name' => 'Updated Name'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($skill->refresh()->name)->toBe('Updated Name');
});

it('table filters by category and proficiency_from', function () {
    \App\Models\Skill::factory()->create(['name' => 'Cat A', 'category' => 'A', 'proficiency_level' => 1]);
    \App\Models\Skill::factory()->create(['name' => 'Cat B', 'category' => 'B', 'proficiency_level' => 4]);

    livewire(\App\Filament\Clusters\Skills\Pages\ListSkills::class)
        ->assertOk()
        ->filterTable('category', 'B')
        ->assertCanSeeTableRecords(\App\Models\Skill::where('category', 'B')->get())
        ->assertCanNotSeeTableRecords(\App\Models\Skill::where('category', 'A')->get())
        ->filterTable('proficiency_level', ['proficiency_level_from' => 3])
        ->assertCanSeeTableRecords(\App\Models\Skill::where('proficiency_level', '>=', 3)->get())
        ->assertCanNotSeeTableRecords(\App\Models\Skill::where('proficiency_level', '<', 3)->get());
});

it('ternary filter toggles active state', function () {
    \App\Models\Skill::factory()->create(['name' => 'Active', 'is_active' => true]);
    \App\Models\Skill::factory()->create(['name' => 'Inactive', 'is_active' => false]);

    livewire(\App\Filament\Clusters\Skills\Pages\ListSkills::class)
        ->assertOk()
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords(\App\Models\Skill::where('is_active', true)->get())
        ->assertCanNotSeeTableRecords(\App\Models\Skill::where('is_active', false)->get());
});

it('record action Archive sets is_active to false', function () {
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    $skill = \App\Models\Skill::factory()->create(['is_active' => true]);

    livewire(\App\Filament\Clusters\Skills\Pages\ListSkills::class)
        ->assertOk()
        ->callTableAction('Archive', $skill);

    expect($skill->refresh()->is_active)->toBeFalse();
});

it('bulk archive updates selected records', function () {
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    $skills = \App\Models\Skill::factory()->count(3)->create(['is_active' => true]);

    livewire(\App\Filament\Clusters\Skills\Pages\ListSkills::class)
        ->assertOk()
        ->callTableBulkAction('Archive', $skills);

    foreach ($skills as $skill) {
        expect($skill->refresh()->is_active)->toBeFalse();
    }
});

it('header action seeds skills from external API on success', function () {
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    Illuminate\Support\Facades\Http::fake([
        'https://dummyjson.com/*' => Illuminate\Support\Facades\Http::response([
            'products' => [
                ['title' => 'Skill One', 'category' => 'technical', 'description' => 'one'],
                ['title' => 'Skill Two', 'category' => 'soft', 'description' => 'two'],
            ],
        ], 200),
    ]);

    \App\Filament\Clusters\Skills\Tables\SkillsTable::seedSkills();

    expect(\App\Models\Skill::where('name', 'Skill One')->exists())->toBeTrue();
    expect(\App\Models\Skill::where('name', 'Skill Two')->exists())->toBeTrue();
});

it('table columns sortable and searchable by name', function () {
    \App\Models\Skill::factory()->create(['name' => 'Alpha']);
    \App\Models\Skill::factory()->create(['name' => 'Beta']);

    livewire(\App\Filament\Clusters\Skills\Pages\ListSkills::class)
        ->assertOk()
        ->searchTable('Alpha')
        ->assertCanSeeTableRecords(\App\Models\Skill::where('name', 'Alpha')->get())
        ->assertCanNotSeeTableRecords(\App\Models\Skill::where('name', 'Beta')->get());
});
