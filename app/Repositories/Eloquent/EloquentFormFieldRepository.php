<?php

namespace App\Repositories\Eloquent;

use App\Models\Event;
use App\Models\FormField;
use App\Repositories\Contracts\FormFieldRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentFormFieldRepository implements FormFieldRepositoryInterface
{
    public function getFormFieldsForEvent(Event $event): Collection
    {
        return FormField::where('event_id', $event->id)
            ->where('show_in_form', true)
            ->orderBy('sort_order')
            ->get();
    }
}

