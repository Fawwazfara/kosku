const MAX_DIMENSION = 1600;
const JPEG_QUALITY = 0.82;

/**
 * Kompres + resize gambar di sisi klien sebelum diunggah (hemat kuota mahasiswa).
 * @param {File} file
 * @returns {Promise<File>}
 */
export function resizeImage(file) {
    if (!file.type.startsWith('image/') || file.type === 'image/gif') {
        return Promise.resolve(file);
    }

    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = () => {
            const img = new Image();
            img.onload = () => {
                let { width, height } = img;
                const scale = Math.min(1, MAX_DIMENSION / Math.max(width, height));
                width = Math.round(width * scale);
                height = Math.round(height * scale);

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(
                    (blob) => {
                        if (!blob) return resolve(file);
                        resolve(
                            new File([blob], file.name.replace(/\.\w+$/, '') + '.jpg', {
                                type: 'image/jpeg',
                                lastModified: Date.now(),
                            }),
                        );
                    },
                    'image/jpeg',
                    JPEG_QUALITY,
                );
            };
            img.onerror = () => resolve(file);
            img.src = reader.result;
        };
        reader.onerror = () => resolve(file);
        reader.readAsDataURL(file);
    });
}
