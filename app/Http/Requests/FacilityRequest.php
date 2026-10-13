<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\Facility $facility */
        $facility = $this->route('facility');

        return (string) $this->method() === 'POST'
            ? $this->user()?->can('create', \App\Models\Facility::class) === true
            : $this->user()?->can('update', $facility) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60', 'unique:facilities,name'.($this->route('facility') ? ','.$this->route('facility')->id : '')],
            'icon' => ['nullable', 'string', 'max:50'],
        ];
    }
}
