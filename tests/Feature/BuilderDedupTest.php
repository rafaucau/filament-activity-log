<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Relaticle\ActivityLog\Tests\Fixtures\Models\Email;
use Relaticle\ActivityLog\Tests\Fixtures\Models\Person;
use Relaticle\ActivityLog\Timeline\Sources\RelatedModelSource;
use Relaticle\ActivityLog\Timeline\TimelineBuilder;
use Relaticle\ActivityLog\Timeline\TimelineEntry;
use Spatie\Activitylog\Models\Activity;

it('dedups RelatedActivityLogSource and RelatedModelSource at same second, RelatedModel wins by priority', function (): void {
    $person = Person::factory()->create();
    $email = Email::factory()->for($person)->create();

    Activity::query()
        ->where('subject_type', $email->getMorphClass())
        ->where('subject_id', $email->id)
        ->update(['created_at' => $email->created_at]);

    $entries = TimelineBuilder::make($person)
        ->fromActivityLogOf(['emails'])
        ->fromRelation('emails', fn (RelatedModelSource $s): RelatedModelSource => $s->event('created_at', 'email_created'))
        ->deduplicate()
        ->get();

    expect($entries)->toHaveCount(1)
        ->and($entries->first()->event)->toBe('email_created')
        ->and($entries->first()->type)->toBe('related_model');
});

it('priority override swaps the winner', function (): void {
    $person = Person::factory()->create();
    $email = Email::factory()->for($person)->create();

    Activity::query()
        ->where('subject_type', $email->getMorphClass())
        ->where('subject_id', $email->id)
        ->update(['created_at' => $email->created_at]);

    $entries = TimelineBuilder::make($person)
        ->fromActivityLogOf(['emails'], priority: 100)
        ->fromRelation('emails', fn (RelatedModelSource $s): RelatedModelSource => $s->event('created_at', 'email_created'), priority: 20)
        ->deduplicate()
        ->get();

    expect($entries)->toHaveCount(1)
        ->and($entries->first()->type)->toBe('related_activity_log');
});

it('dedupKeyUsing() overrides the per-entry dedup key', function (): void {
    $person = Person::factory()->create();
    Email::factory()->for($person)->create([
        'sent_at' => CarbonImmutable::parse('2026-04-17T10:00:00Z'),
        'received_at' => CarbonImmutable::parse('2026-04-17T10:00:00Z'),
    ]);

    $entries = TimelineBuilder::make($person)
        ->fromRelation('emails', fn (RelatedModelSource $s): RelatedModelSource => $s->event('sent_at', 'email_sent')->event('received_at', 'email_received'))
        ->dedupKeyUsing(fn (TimelineEntry $e): string => $e->relatedModel::class.':'.$e->relatedModel->id)
        ->deduplicate()
        ->get();

    expect($entries)->toHaveCount(1);
});

it('keeps distinct own-activity entries that land in the same second (multi-change save)', function (): void {
    $person = Person::factory()->create();
    $person->update(['name' => 'First change']);
    $person->update(['name' => 'Second change']);

    // Simulate one save producing several activity rows in the same second.
    $stamp = CarbonImmutable::parse('2026-04-17T10:00:00Z');
    Activity::query()
        ->where('subject_type', $person->getMorphClass())
        ->where('subject_id', $person->id)
        ->update(['created_at' => $stamp]);

    $count = Activity::query()
        ->where('subject_type', $person->getMorphClass())
        ->where('subject_id', $person->id)
        ->count();

    $entries = TimelineBuilder::make($person)
        ->fromActivityLog()
        ->deduplicate()
        ->get();

    expect($entries)->toHaveCount($count);
});

it('deduplicate(false) skips dedup entirely', function (): void {
    $person = Person::factory()->create();
    $email = Email::factory()->for($person)->create();

    Activity::query()
        ->where('subject_type', $email->getMorphClass())
        ->where('subject_id', $email->id)
        ->update(['created_at' => $email->created_at]);

    $entries = TimelineBuilder::make($person)
        ->fromActivityLogOf(['emails'])
        ->fromRelation('emails', fn (RelatedModelSource $s): RelatedModelSource => $s->event('created_at', 'email_created'))
        ->deduplicate(false)
        ->get();

    expect($entries->count())->toBeGreaterThan(1);
});
