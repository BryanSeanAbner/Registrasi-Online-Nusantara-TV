<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;
use App\Models\Event;

interface FormFieldRepositoryInterface
{
    /**
     * Get form fields for event that should be shown on public form, ordered.
     *
     * @return Collection<int, \App\Models\FormField>
     */
    public function getFormFieldsForEvent(Event $event): Collection;
}

