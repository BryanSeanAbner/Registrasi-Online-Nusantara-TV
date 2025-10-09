<?php

namespace App\Repositories\Eloquent;

use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentEventRepository implements EventRepositoryInterface
{
    public function getPublished(): Collection
    {
        return Event::where('is_published', true)
            ->orderBy('starts_at', 'desc')
            ->get();
    }

    public function findPublishedBySlug(string $slug): Event
    {
        return Event::whereSlug($slug)->where('is_published', true)->firstOrFail();
    }

    public function findBySlug(string $slug): Event
    {
        return Event::whereSlug($slug)->firstOrFail();
    }
}
