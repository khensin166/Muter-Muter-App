/**
 * Composable for validating, optimizing, and auto-compressing image uploads.
 * Adheres to Emil Kowalski's philosophy: handles edge cases gracefully,
 * prevents server crashes (PostTooLargeException), and optimizes large smartphone photos automatically.
 */
export function useImageUpload() {
  const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 MB per file
  const MAX_DIMENSION = 2048; // 2048px max width/height
  const COMPRESSION_QUALITY = 0.85;

  /**
   * Optimize an image file: auto-downscale and compress if large (> 1.5MB or > 2048px).
   *
   * @param {File} file
   * @returns {Promise<{ file: File, previewUrl: string, wasOptimized: boolean }>}
   */
  const optimizeImage = async (file) => {
    if (!file.type.startsWith('image/')) {
      throw new Error(`Berkas "${file.name}" bukan format gambar yang valid.`);
    }

    // If file is already compact (<= 1.5MB), check dimensions or return as-is
    if (file.size <= 1.5 * 1024 * 1024) {
      return {
        file,
        previewUrl: URL.createObjectURL(file),
        wasOptimized: false,
      };
    }

    // For larger files (common for 8MB - 20MB phone photos), auto-downscale via offscreen canvas
    return new Promise((resolve, reject) => {
      const reader = new FileReader();

      reader.onload = (e) => {
        const img = new Image();

        img.onload = () => {
          try {
            let width = img.width;
            let height = img.height;

            if (width > MAX_DIMENSION || height > MAX_DIMENSION) {
              if (width > height) {
                height = Math.round((height * MAX_DIMENSION) / width);
                width = MAX_DIMENSION;
              } else {
                width = Math.round((width * MAX_DIMENSION) / height);
                height = MAX_DIMENSION;
              }
            }

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;

            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);

            // Export as JPEG with 0.85 quality
            canvas.toBlob(
              (blob) => {
                if (blob && blob.size < file.size) {
                  const optimizedFile = new File(
                    [blob],
                    file.name.replace(/\.[^.]+$/, '.jpg'),
                    { type: 'image/jpeg', lastModified: Date.now() }
                  );

                  if (optimizedFile.size > MAX_FILE_SIZE) {
                    reject(
                      new Error(
                        `Foto "${file.name}" tetap terlalu besar (${(optimizedFile.size / 1024 / 1024).toFixed(1)} MB) setelah dioptimasi. Batas maksimal adalah 5 MB.`
                      )
                    );
                    return;
                  }

                  resolve({
                    file: optimizedFile,
                    previewUrl: URL.createObjectURL(optimizedFile),
                    wasOptimized: true,
                  });
                } else {
                  if (file.size > MAX_FILE_SIZE) {
                    reject(
                      new Error(
                        `Foto "${file.name}" (${(file.size / 1024 / 1024).toFixed(1)} MB) melebihi batas maksimal 5 MB.`
                      )
                    );
                    return;
                  }
                  resolve({
                    file,
                    previewUrl: URL.createObjectURL(file),
                    wasOptimized: false,
                  });
                }
              },
              'image/jpeg',
              COMPRESSION_QUALITY
            );
          } catch (err) {
            // Fallback to original file if canvas fails
            if (file.size > MAX_FILE_SIZE) {
              reject(
                new Error(
                  `Foto "${file.name}" (${(file.size / 1024 / 1024).toFixed(1)} MB) melebihi batas maksimal 5 MB.`
                )
              );
              return;
            }
            resolve({
              file,
              previewUrl: URL.createObjectURL(file),
              wasOptimized: false,
            });
          }
        };

        img.onerror = () => {
          reject(new Error(`Gagal memuat gambar "${file.name}". Pastikan file tidak rusak.`));
        };

        img.src = e.target.result;
      };

      reader.onerror = () => {
        reject(new Error(`Gagal membaca berkas "${file.name}".`));
      };

      reader.readAsDataURL(file);
    });
  };

  /**
   * Process multiple files in parallel.
   *
   * @param {FileList|File[]} files
   * @returns {Promise<{ optimizedFiles: File[], previews: { name: string, url: string }[], errors: string[] }>}
   */
  const processFiles = async (files) => {
    const fileList = Array.from(files || []);
    const optimizedFiles = [];
    const previews = [];
    const errors = [];

    for (const f of fileList) {
      try {
        const result = await optimizeImage(f);
        optimizedFiles.push(result.file);
        previews.push({
          name: result.file.name,
          url: result.previewUrl,
        });
      } catch (err) {
        errors.push(err.message);
      }
    }

    return {
      optimizedFiles,
      previews,
      errors,
    };
  };

  return {
    MAX_FILE_SIZE,
    optimizeImage,
    processFiles,
  };
}

 