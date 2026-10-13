<?php

namespace App\Http\Requests;

use App\Models\Kos;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class PhotoUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\Kos $kos */
        $kos = $this->route('kos');

        return $this->user()?->can('managePhotos', $kos) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var \App\Models\Kos $kos */
        $kos = $this->route('kos');
        $sisa = Kos::MAX_PHOTOS - $kos->photos()->count();

        return [
            'photos' => ['required', 'array', 'max:'.max($sisa, 1)],
            'photos.*' => ['required', 'image', 'mimetypes:image/jpeg,image/png,image/webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        /** @var \App\Models\Kos $kos */
        $kos = $this->route('kos');

        return [
            'photos.max' => 'Maksimal '.Kos::MAX_PHOTOS.' foto per kos (kamu sudah punya '.$kos->photos()->count().').',
        ];
    }
}
