<?php

namespace App\Repositories\Contracts;

use App\Models\Event;
use Illuminate\Support\Collection;

interface EventRepositoryInterface
{
    /** @return Collection<int, Event> */
    public function getPublished(): Collection;

    public function findPublishedBySlug(string $slug): Event;

    public function findBySlug(string $slug): Event;
}
