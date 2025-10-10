<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Contracts\EventRepositoryInterface as Events;
use App\Repositories\Contracts\FormFieldRepositoryInterface as FormFields;
use App\Services\RegistrationService;

class RegistrationController extends Controller
{
    public function __construct(
        protected Events $events,
        protected FormFields $formFields,
        protected RegistrationService $registrations,
    ) {}

    public function create(string $slug)
    {
        $event = $this->events->findBySlug($slug);
        $fields = $this->formFields->getFormFieldsForEvent($event);
        return view('public.register.form', compact('event', 'fields'));
    }

    public function store(Request $request, string $slug)
    {
        $event = $this->events->findBySlug($slug);
        $rules = $this->registrations->buildValidationRules($event);
        $data = $request->validate($rules);

        $registration = $this->registrations->createRegistration($event, $data);

        return redirect()
            ->route('register.thanks', ['slug' => $event->slug])
            ->with('registration_id', $registration->id);
    }
}

