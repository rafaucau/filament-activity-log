<?php

declare(strict_types=1);

use Relaticle\ActivityLog\Filament\Livewire\ActivityLogLivewire;
use Relaticle\ActivityLog\Tests\Fixtures\Models\Person;

use function Pest\Livewire\livewire;

it('resolves soft-deleted subjects without throwing 404', function (): void {
    $person = Person::factory()->create(['name' => 'Soft Deleted Person']);
    $person->delete();

    expect($person->trashed())->toBeTrue();

    livewire(ActivityLogLivewire::class, [
        'subjectClass' => $person::class,
        'subjectKey' => $person->getKey(),
    ])->assertSuccessful();
});
