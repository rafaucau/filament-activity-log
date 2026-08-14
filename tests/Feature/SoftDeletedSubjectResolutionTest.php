<?php

declare(strict_types=1);

use Relaticle\ActivityLog\Filament\Livewire\ActivityLogLivewire;
use Relaticle\ActivityLog\Tests\Fixtures\Models\Person;

it('resolves the timeline when the subject has been soft deleted', function (): void {
    $person = Person::factory()->create(['name' => 'Soft Deleted Person']);
    $person->update(['name' => 'Renamed Before Deletion']);
    $person->delete();

    expect($person->trashed())->toBeTrue();

    $component = new ActivityLogLivewire;
    $component->subjectClass = $person::class;
    $component->subjectKey = $person->getKey();
    $component->mount();

    $view = $component->render();

    expect($view->name())->toBe('activity-log::timeline')
        ->and($view->getData()['entries'])->not->toBeEmpty();
});
