<?php

declare(strict_types=1);

namespace Relaticle\ActivityLog\Timeline\Sources;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use InvalidArgumentException;
use Relaticle\ActivityLog\Contracts\TimelineSource;

abstract class AbstractTimelineSource implements TimelineSource
{
    public function __construct(protected int $priority) {}

    public function priority(): int
    {
        return $this->priority;
    }

    /**
     * Resolve the Activity model used for the read path.
     *
     * Prefers the plugin's own key, then falls back to Spatie's canonical
     * `activitylog.activity_model` — the same key that governs writes — so the
     * rendered timeline reads through whatever (possibly tenant-scoped) Activity
     * subclass the host configured, even when the hyphenated plugin config was
     * never published.
     *
     * @return class-string<\Spatie\Activitylog\Contracts\Activity&\Illuminate\Database\Eloquent\Model>
     */
    protected function activityModelClass(): string
    {
        /** @var class-string<\Spatie\Activitylog\Contracts\Activity&\Illuminate\Database\Eloquent\Model> */
        return config('activity-log.activity_model')
            ?? config('activitylog.activity_model')
            ?? \Spatie\Activitylog\Models\Activity::class;
    }

    protected function dedupKeyFor(string $class, int|string $id, CarbonImmutable $occurredAt): string
    {
        return sprintf(
            '%s:%s:%s',
            $class,
            $id,
            $occurredAt->utc()->format('Y-m-d\TH:i:s'),
        );
    }

    /**
     * Dedup key for an activity-log row. Unlike the second-precision key above,
     * this includes the activity id so several distinct activities written for
     * the same subject in the same second (e.g. a multi-field save) stay separate.
     */
    protected function dedupKeyForActivity(string $class, int|string $id, CarbonImmutable $occurredAt, int|string $activityId): string
    {
        return $this->dedupKeyFor($class, $id, $occurredAt).':'.$activityId;
    }

    /**
     * @return Relation<Model, Model, mixed>
     */
    protected function assertRelation(Model $subject, string $relation): Relation
    {
        if (! method_exists($subject, $relation)) {
            throw new InvalidArgumentException(sprintf(
                '%s has no relation method named "%s".',
                $subject::class,
                $relation,
            ));
        }

        $result = $subject->{$relation}();

        if (! $result instanceof Relation) {
            throw new InvalidArgumentException(sprintf(
                '%s::%s() did not return an Eloquent relation.',
                $subject::class,
                $relation,
            ));
        }

        return $result;
    }
}
