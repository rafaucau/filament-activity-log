<?php

declare(strict_types=1);

namespace Relaticle\ActivityLog\Support;

enum ActivityLogOperation: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Restored = 'restored';

    public function icon(): string
    {
        return match ($this) {
            self::Created => 'heroicon-o-plus',
            self::Deleted => 'heroicon-o-trash',
            self::Restored => 'heroicon-o-arrow-uturn-left',
            self::Updated => 'heroicon-o-pencil-square',
        };
    }

    public function verb(): string
    {
        return $this->value;
    }
}
