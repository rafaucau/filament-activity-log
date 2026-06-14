<?php

declare(strict_types=1);

use Relaticle\ActivityLog\Tests\Fixtures\Models\Person;
use Relaticle\ActivityLog\Tests\Fixtures\Models\ScopedActivity;
use Relaticle\ActivityLog\Timeline\Sources\ActivityLogSource;
use Relaticle\ActivityLog\Timeline\Window;

it('reads through the plugin activity_model when set', function (): void {
    config()->set('activity-log.activity_model', ScopedActivity::class);

    $person = Person::factory()->create();

    $entries = collect((new ActivityLogSource(priority: 10))->resolve($person, new Window(cap: 10)));

    expect($entries)->toBeEmpty();
});

it('falls back to Spatie activitylog.activity_model when the plugin key is null', function (): void {
    config()->set('activity-log.activity_model', null);
    config()->set('activitylog.activity_model', ScopedActivity::class);

    $person = Person::factory()->create();

    $entries = collect((new ActivityLogSource(priority: 10))->resolve($person, new Window(cap: 10)));

    expect($entries)->toBeEmpty();
});

it('uses the base Spatie Activity when nothing is configured', function (): void {
    config()->set('activity-log.activity_model', null);
    config()->set('activitylog.activity_model', null);

    $person = Person::factory()->create();

    $entries = collect((new ActivityLogSource(priority: 10))->resolve($person, new Window(cap: 10)));

    expect($entries)->toHaveCount(1);
});
