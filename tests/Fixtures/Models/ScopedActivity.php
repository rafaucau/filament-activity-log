<?php

declare(strict_types=1);

namespace Relaticle\ActivityLog\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

/**
 * Stand-in for a host's tenant-scoped Activity subclass: a global scope that
 * hides every row, so a read path honoring it returns nothing.
 */
final class ScopedActivity extends SpatieActivity
{
    protected static function booted(): void
    {
        static::addGlobalScope('scoped', function (Builder $query): void {
            $query->whereRaw('1 = 0');
        });
    }
}
