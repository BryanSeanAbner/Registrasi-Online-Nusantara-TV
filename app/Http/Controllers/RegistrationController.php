<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\FormField;
use App\Models\FormFieldValue;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function create(string $slug) { 
        $event = Event::whereSlug($slug)->firstOrFail();

        $fields = $event->formFields()
            ->where('show_in_form', true)
            ->orderBy('sort_order')
            ->get();

        return view('public.register.form', compact('event', 'fields'));
    }

    public function store(Request $request, string $slug)
    {
        $event = Event::whereSlug($slug)->firstOrFail();    

        $rules = [];
        $fields = FormField::where('event_id', $event->id)
            ->where('show_in_form', true)
            ->orderBy('sort_order')
            ->get();

        foreach ($fields as $f) {
            $rule = [];

            if ($f->is_required) $rule[] = 'required';
            switch ($f->type) {
                case 'email':   $rule[] = 'email'; break;
                case 'numeric': $rule[] = 'numeric'; break;
                case 'image':   $rule[] = 'image'; break;
                case 'date':    $rule[] = 'date'; break;
                case 'select':
                    if (!empty($f->meta['options']) && is_array($f->meta['options'])) {
                        $allowed = implode(',', array_map(fn($v) => str_replace(',', '\,', $v), $f->meta['options']));
                        $rule[] = "in:{$allowed}";
                    }
                break;
            }

            if (!empty($f->meta['rules'])) $rule[] = $f->meta['rules'];
            $rules[$f->name] = implode('|', $rule);
        }

        $data = $request->validate($rules);

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

            return redirect()
                ->route('register.thanks', ['slug' => $event->slug])
                ->with('registration_id', $registration->id);
        });
    }
}
