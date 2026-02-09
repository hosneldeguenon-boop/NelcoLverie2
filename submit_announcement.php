<?php
/**
 * ✅ PUBLICATION D'UNE ANNONCE - NELCO LAVERIE
 * Réservé aux administrateurs uniquement
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

error_log('=== submit_announcement.php appelé ===');

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    // ✅ VÉRIFICATION AUTHENTIFICATION ADMIN
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        http_response_code(401);
        throw new Exception('Accès réservé aux administrateurs');
    }

    $admin_id = (int)$_SESSION['admin_id'];

    // Récupérer les données du formulaire
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $media_type = $_POST['media_type'] ?? 'none';

    // VALIDATIONS
    if (empty($title)) {
        throw new Exception('Le titre est obligatoire');
    }

    if (strlen($title) > 255) {
        throw new Exception('Le titre ne peut pas dépasser 255 caractères');
    }

    if (empty($content)) {
        throw new Exception('Le contenu est obligatoire');
    }

    if (strlen($content) > 5000) {
        throw new Exception('Le contenu ne peut pas dépasser 5000 caractères');
    }

    if (!in_array($media_type, ['none', 'image', 'audio', 'video_link'])) {
        throw new Exception('Type de média invalide');
    }

    $media_path = null;

    // ✅ GESTION DES FICHIERS UPLOADÉS
    if ($media_type === 'image' || $media_type === 'audio') {
        if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['media_file'];
            
            // Vérifier la taille (max 10 MB)
            if ($file['size'] > 10 * 1024 * 1024) {
                throw new Exception('Le fichier ne doit pas dépasser 10 MB');
            }

            // Vérifier le type MIME
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowed_types = [];
            if ($media_type === 'image') {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            } else if ($media_type === 'audio') {
                $allowed_types = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp3'];
            }

            if (!in_array($mime_type, $allowed_types)) {
                throw new Exception('Type de fichier non autorisé');
            }

            // Créer le dossier uploads s'il n'existe pas
            $upload_dir = __DIR__ . '/uploads/announcements/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Générer un nom de fichier unique
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('announcement_', true) . '.' . $extension;
            $upload_path = $upload_dir . $filename;

            // Déplacer le fichier
            if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                throw new Exception('Erreur lors de l\'upload du fichier');
            }

            $media_path = 'uploads/announcements/' . $filename;
            error_log('✅ Fichier uploadé: ' . $media_path);
        }
    } 
    // ✅ GESTION DES LIENS VIDÉO
    else if ($media_type === 'video_link') {
        $video_link = trim($_POST['video_link'] ?? '');
        
        if (empty($video_link)) {
            throw new Exception('Le lien vidéo est obligatoire');
        }

        // Valider que c'est un lien YouTube ou Vimeo
        if (!preg_match('/(?:youtube\.com|youtu\.be|vimeo\.com)/', $video_link)) {
            throw new Exception('Seuls les liens YouTube et Vimeo sont acceptés');
        }

        $media_path = $video_link;
        error_log('✅ Lien vidéo: ' . $media_path);
    }

    // ✅ INSERTION EN BASE DE DONNÉES
    $conn = getDBConnection();

    $stmt = $conn->prepare("
        INSERT INTO announcements (admin_id, title, content, media_type, media_path, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");

    $result = $stmt->execute([
        $admin_id,
        $title,
        $content,
        $media_type,
        $media_path
    ]);

    if (!$result) {
        throw new Exception('Erreur lors de l\'enregistrement de l\'annonce');
    }

    $announcement_id = $conn->lastInsertId();

    error_log('✅ Annonce publiée: ID=' . $announcement_id . ', Admin=' . $admin_id);

    echo json_encode([
        'success' => true,
        'message' => 'Annonce publiée avec succès',
        'announcement_id' => $announcement_id
    ]);

} catch (Exception $e) {
    error_log('❌ Erreur: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
