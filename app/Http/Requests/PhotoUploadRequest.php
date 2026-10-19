<?php

namespace App\Http\Requests;

use App\Models\Kos;
use Illuminate\Foundation\Http\FormRequest;

class PhotoUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Kos $kos */
        $kos = $this->route('kos');

        return $this->user()?->can('managePhotos', $kos) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Kos $kos */
        $kos = $this->route('kos');
        $sisa = max(0, Kos::MAX_PHOTOS - $kos->photos()->count());

        return [
            'photos' => ['required', 'array', 'min:1', 'max:'.$sisa],
            'photos.*' => ['required', 'image', 'mimetypes:image/jpeg,image/png,image/webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        /** @var Kos $kos */
        $kos = $this->route('kos');

        return [
            'photos.max' => 'Maksimal '.Kos::MAX_PHOTOS.' foto per kos (kamu sudah punya '.$kos->photos()->count().').',
        ];
    }
}
