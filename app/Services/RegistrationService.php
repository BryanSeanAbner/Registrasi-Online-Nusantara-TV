<?php

namespace App\Services;

use App\Models\Event;
use App\Models\FormFieldValue;
use App\Models\Registration;
use App\Repositories\Contracts\FormFieldRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RegistrationService
{
    public function __construct(
        protected FormFieldRepositoryInterface $formFields,
    ) {}

    /**
     * Build validation rules array from event form fields.
     *
     * @return array<string,string>
     */
    public function buildValidationRules(Event $event): array
    {
        $rules = [];
        $fields = $this->formFields->getFormFieldsForEvent($event);

        foreach ($fields as $f) {
            $rule = [];

            // Required vs optional
            $rule[] = $f->is_required ? 'required' : 'nullable';

            // Basic type rules
            switch ($f->type) {
                case 'email':
                    $rule[] = 'email';
                    break;
                case 'numeric':
                    $rule[] = 'numeric';
                    break;
                case 'image':
                    $rule[] = 'image';
                    break;
                case 'date':
                    $rule[] = 'date';
                    break;
                case 'select':
                    if (!empty($f->meta['options']) && is_array($f->meta['options'])) {
                        $rule[] = Rule::in($f->meta['options']);
                    }
                    break;
            }

            if (!empty($f->meta['rules'])) {
                $rule[] = $f->meta['rules'];
            }

            $rules[$f->name] = $rule;
        }

        return $rules;
    }

    /**
     * Create registration and persist form answers.
     */
    public function createRegistration(Event $event, array $data): Registration
    {
        $fields = $this->formFields->getFormFieldsForEvent($event);

        return DB::transaction(function () use ($event, $fields, $data) {
            $registration = Registration::create([
                'event_id' => $event->id,
                'status'   => Registration::ST_PENDING,
            ]);

            foreach ($fields as $f) {
                $val = $data[$f->name] ?? null;

                if ($f->type === 'image' && $val) {
                    $path = $val->store("registrations/{$registration->id}", 'public');
                    FormFieldValue::create([
                        'registration_id' => $registration->id,
                        'field_id'        => $f->id,
                        'value'           => $path,
                    ]);
                    continue;
                }

                FormFieldValue::create([
                    'registration_id' => $registration->id,
                    'field_id'        => $f->id,
                    'value'           => is_array($val) ? null : (string) $val,
                    'value_json'      => is_array($val) ? $val : null,
                    'value_number'    => is_numeric($val) ? $val : null,
                ]);
            }

            return $registration;
        });
    }
}
