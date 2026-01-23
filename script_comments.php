<?php
/**
 * ============================================
 * FILE: add_comment.php
 * Ajouter un commentaire
 * ============================================
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Vous devez être connecté');
    }

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('Aucune donnée reçue');
    }

    $comment_text = trim($data['comment_text'] ?? '');
    $rating = isset($data['rating']) && $data['rating'] ? intval($data['rating']) : null;

    if (empty($comment_text)) {
        throw new Exception('Le commentaire ne peut pas être vide');
    }

    if (strlen($comment_text) < 10) {
        throw new Exception('Le commentaire doit contenir au moins 10 caractères');
    }

    if (strlen($comment_text) > 500) {
        throw new Exception('Le commentaire ne peut pas dépasser 500 caractères');
    }

    if ($rating && ($rating < 1 || $rating > 5)) {
        throw new Exception('La note doit être entre 1 et 5');
    }

    $conn = getDBConnection();

    $stmt = $conn->prepare("
        INSERT INTO comments (user_id, comment_text, rating, created_at, updated_at)
        VALUES (?, ?, ?, NOW(), NOW())
    ");

    $stmt->execute([$_SESSION['user_id'], $comment_text, $rating]);

    echo json_encode([
        'success' => true,
        'message' => 'Commentaire ajouté avec succès'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

---SEPARATOR---

<?php
/**
 * ============================================
 * FILE: get_comments.php
 * Récupérer tous les commentaires
 * ============================================
 */

header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

try {
    $conn = getDBConnection();

    $stmt = $conn->prepare("
        SELECT c.id, c.user_id, c.rating, c.comment_text, c.created_at,
               u.firstname, u.lastname
        FROM comments c
        JOIN users u ON c.user_id = u.id
        ORDER BY c.created_at DESC
    ");
    $stmt->execute();
    $comments = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'comments' => $comments
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

---SEPARATOR---

<?php
/**
 * ============================================
 * FILE: get_all_comments.php
 * Pour l'admin - récupérer tous les commentaires avec stats
 * ============================================
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

try {
    if (!isset($_SESSION['admin_logged_in'])) {
        throw new Exception('Non authentifié');
    }

    $conn = getDBConnection();

    $stmt = $conn->prepare("
        SELECT c.id, c.user_id, c.rating, c.comment_text, c.created_at,
               u.firstname, u.lastname, u.email
        FROM comments c
        JOIN users u ON c.user_id = u.id
        ORDER BY c.created_at DESC
    ");
    $stmt->execute();
    $comments = $stmt->fetchAll();

    $statsStmt = $conn->prepare("
        SELECT COUNT(*) as total,
               AVG(rating) as average_rating
        FROM comments
        WHERE rating IS NOT NULL
    ");
    $statsStmt->execute();
    $stats = $statsStmt->fetch();

    echo json_encode([
        'success' => true,
        'comments' => $comments,
        'stats' => $stats
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

---SEPARATOR---

<?php
/**
 * ============================================
 * FILE: update_comment.php
 * Modifier un commentaire
 * ============================================
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Non authentifié');
    }

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('Aucune donnée reçue');
    }

    $comment_id = intval($data['comment_id'] ?? 0);
    $comment_text = trim($data['comment_text'] ?? '');
    $rating = isset($data['rating']) && $data['rating'] ? intval($data['rating']) : null;

    if (!$comment_id) {
        throw new Exception('ID commentaire invalide');
    }

    if (empty($comment_text) || strlen($comment_text) < 10 || strlen($comment_text) > 500) {
        throw new Exception('Le commentaire doit contenir entre 10 et 500 caractères');
    }

    if ($rating && ($rating < 1 || $rating > 5)) {
        throw new Exception('La note doit être entre 1 et 5');
    }

    $conn = getDBConnection();

    $verifyStmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
    $verifyStmt->execute([$comment_id]);
    $comment = $verifyStmt->fetch();

    if (!$comment || $comment['user_id'] != $_SESSION['user_id']) {
        throw new Exception('Vous ne pouvez pas modifier ce commentaire');
    }

    $updateStmt = $conn->prepare("
        UPDATE comments 
        SET comment_text = ?, rating = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $updateStmt->execute([$comment_text, $rating, $comment_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Commentaire mis à jour'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

---SEPARATOR---

<?php
/**
 * ============================================
 * FILE: delete_user_comment.php
 * Supprimer son propre commentaire
 * ============================================
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Non authentifié');
    }

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $comment_id = intval($data['comment_id'] ?? 0);

    if (!$comment_id) {
        throw new Exception('ID invalide');
    }

    $conn = getDBConnection();

    $verifyStmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
    $verifyStmt->execute([$comment_id]);
    $comment = $verifyStmt->fetch();

    if (!$comment || $comment['user_id'] != $_SESSION['user_id']) {
        throw new Exception('Vous ne pouvez pas supprimer ce commentaire');
    }

    $deleteStmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
    $deleteStmt->execute([$comment_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Commentaire supprimé'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

---SEPARATOR---

<?php
/**
 * ============================================
 * FILE: delete_comment.php
 * Admin - Supprimer un commentaire
 * ============================================
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    if (!isset($_SESSION['admin_logged_in'])) {
        throw new Exception('Non authentifié');
    }

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $comment_id = intval($data['comment_id'] ?? 0);

    if (!$comment_id) {
        throw new Exception('ID invalide');
    }

    $conn = getDBConnection();

    $deleteStmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
    $deleteStmt->execute([$comment_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Commentaire supprimé par l\'admin'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

---SEPARATOR---

<?php
/**
 * ============================================
 * FILE: check_user_auth.php
 * Vérifier l'authentification utilisateur
 * ============================================
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

$user_id = $_SESSION['user_id'] ?? null;

echo json_encode([
    'authenticated' => $user_id ? true : false,
    'user_id' => $user_id
]);
?>

---SEPARATOR---

<?php
/**
 * ============================================
 * FILE: check_admin_auth.php
 * Vérifier l'authentification admin
 * ============================================
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    echo json_encode([
        'authenticated' => true,
        'firstname' => $_SESSION['admin_firstname'],
        'lastname' => $_SESSION['admin_lastname']
    ]);
} else {
    echo json_encode([
        'authenticated' => false
    ]);
}
?>

---SEPARATOR---

<?php
/**
 * ============================================
 * FILE: admin_logout.php
 * Déconnexion admin
 * ============================================
 */

session_start();

$_SESSION = [];
session_destroy();

header('Location: admin_login.php');
?>