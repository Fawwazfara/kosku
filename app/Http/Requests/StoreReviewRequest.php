<?php

namespace App\Http\Requests;

use App\Models\Kos;
use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $kos = Kos::query()->where('slug', $this->route('slug'))->first();

        if (! $kos || $kos->status !== Kos::STATUS_ACTIVE || $kos->owner_id === $this->user()?->id) {
            return false;
        }

        return $this->user()?->can('create', Review::class) === true;
    }

    protected function prepareForValidation(): void
    {
        $kos = Kos::query()->where('slug', $this->route('slug'))->first();

        $this->merge(['kos_id' => $kos?->id]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'kos_id' => ['required', Rule::unique('reviews', 'kos_id')->where(fn ($query) => $query->where('user_id', $this->user()->id))],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'kos_id.unique' => 'Kamu sudah pernah memberi ulasan untuk kos ini.',
        ];
    }
}
