<?php
// ============================================
// GESTION UPLOAD PHOTOS
// ============================================

function uploadPhoto(array $file, string $prefix = 'bien'): string|false {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = MAX_FILE_SIZE;

    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > $maxSize) return false;

    // Vérification type MIME réel
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) return false;

    $extension = match($mimeType) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => false
    };
    if (!$extension) return false;

    $filename = $prefix . '_' . uniqid() . '_' . time() . '.' . $extension;
    $destination = UPLOAD_DIR . $filename;

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $filename;
    }
    return false;
}

function deletePhoto(string $filename): void {
    $path = UPLOAD_DIR . $filename;
    if (file_exists($path)) {
        unlink($path);
    }
}

function handlePhotosUpload(array $files, int $bienId, PDO $pdo, array $existingBien = []): array {
    $photoColumns = [];

    for ($i = 1; $i <= 10; $i++) {
        $key = 'photo' . $i;
        if (isset($files[$key]) && $files[$key]['error'] === UPLOAD_ERR_OK) {
            // Supprimer l'ancienne photo si elle existe
            if (!empty($existingBien[$key])) {
                deletePhoto($existingBien[$key]);
            }
            $uploaded = uploadPhoto($files[$key], 'bien' . $bienId);
            if ($uploaded) {
                $photoColumns[$key] = $uploaded;
            }
        } elseif (!empty($existingBien[$key])) {
            // Garder l'ancienne photo
            $photoColumns[$key] = $existingBien[$key];
        }
    }
    return $photoColumns;
}
?>
