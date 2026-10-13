<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\Kos $kos */
        $kos = $this->route('kos');

        return $this->user()?->can('updateStock', $kos) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var \App\Models\Kos $kos */
        $kos = $this->route('kos');

        return [
            'available_rooms' => ['required', 'integer', 'min:0', 'max:'.$kos->total_rooms],
        ];
    }
}
