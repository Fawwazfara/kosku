<?php

namespace App\Http\Requests;

use App\Models\KosPhoto;
use Illuminate\Foundation\Http\FormRequest;

class PhotoUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\KosPhoto $photo */
        $photo = $this->route('photo');

        return $this->user()?->can('managePhotos', $photo->kos) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'is_primary' => ['required', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:50'],
        ];
    }
}
