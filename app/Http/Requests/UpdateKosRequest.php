<?php

namespace App\Http\Requests;

use App\Models\Kos;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKosRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\Kos $kos */
        $kos = $this->route('kos');

        return $this->user()?->can('update', $kos) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->kosRules();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('whatsapp')) {
            $this->merge(['whatsapp' => normalize_whatsapp($this->input('whatsapp'))]);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function kosRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'string', Rule::in(Kos::TYPES)],
            'description' => ['required', 'string', 'max:3000'],
            'price_min' => ['required', 'integer', 'min:100000', 'max:99999999'],
            'price_max' => ['nullable', 'integer', 'gte:price_min', 'max:99999999'],
            'address' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:120'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'whatsapp' => ['required', 'string', 'regex:/^62[0-9]{8,14}$/'],
            'total_rooms' => ['required', 'integer', 'min:1', 'max:500'],
            'available_rooms' => ['required', 'integer', 'min:0', 'lte:total_rooms'],
            'rules' => ['nullable', 'string', 'max:2000'],
            'facility_ids' => ['nullable', 'array'],
            'facility_ids.*' => ['integer', 'exists:facilities,id'],
        ];
    }
}
