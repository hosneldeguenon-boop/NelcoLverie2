<?php
/**
 * Page de publication des annonces - VERSION PHP SÉCURISÉE
 * Réservée aux administrateurs uniquement
 */

session_start();

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Récupérer les infos de l'admin
$admin_firstname = $_SESSION['admin_firstname'] ?? 'Admin';
$admin_id = $_SESSION['admin_id'] ?? 0;

error_log("Admin connecté : ID=" . $admin_id . ", Nom=" . $admin_firstname);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publier une annonce - Nelco Laverie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-content h1 {
            color: #667eea;
            margin-bottom: 10px;
        }

        .header-content p {
            color: #666;
        }

        .admin-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .publish-section, .announcements-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .section-title {
            color: #667eea;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }

        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px;
            background: #f8f9fa;
            border: 2px dashed #667eea;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-input-label:hover {
            background: #e8eaf6;
        }

        .file-preview {
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            display: none;
        }

        .file-preview img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary {
            background: #667eea;
            color: white;
            width: 100%;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            margin-bottom: 20px;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            font-size: 14px;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .announcement-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }

        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 10px;
        }

        .announcement-title {
            font-weight: 600;
            color: #333;
            font-size: 18px;
        }

        .announcement-date {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }

        .announcement-content {
            color: #555;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .announcement-media {
            margin-top: 15px;
        }

        .announcement-media img {
            max-width: 100%;
            border-radius: 8px;
        }

        .announcement-media audio {
            width: 100%;
        }

        .announcement-media iframe {
            width: 100%;
            height: 315px;
            border-radius: 8px;
        }

        .media-type-selector {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }

        .media-type-btn {
            padding: 10px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .media-type-btn.active {
            border-color: #667eea;
            background: #e8eaf6;
            color: #667eea;
        }

        .media-type-btn:hover {
            border-color: #667eea;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .no-announcements {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        @media (max-width: 968px) {
            .main-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <h1><i class="fas fa-bullhorn"></i> Gestion des annonces</h1>
                <p>Publiez et gérez les annonces de votre laverie</p>
            </div>
            <div class="admin-badge">
                <i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($admin_firstname); ?>
            </div>
        </div>

        <a href="admin_dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour au tableau de bord
        </a>

        <div class="alert alert-success" id="successAlert"></div>
        <div class="alert alert-error" id="errorAlert"></div>

        <div class="main-content">
            <!-- Section Publication -->
            <div class="publish-section">
                <h2 class="section-title">
                    <i class="fas fa-plus-circle"></i> Publier une annonce
                </h2>

                <form id="announcementForm">
                    <div class="form-group">
                        <label for="title">Titre de l'annonce *</label>
                        <input type="text" id="title" name="title" required maxlength="255" 
                               placeholder="Ex: Promotion spéciale ce week-end !">
                    </div>

                    <div class="form-group">
                        <label for="content">Contenu de l'annonce *</label>
                        <textarea id="content" name="content" required 
                                  placeholder="Décrivez votre annonce en détail..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Type de média (optionnel)</label>
                        <div class="media-type-selector">
                            <div class="media-type-btn active" data-type="none">
                                <i class="fas fa-ban"></i><br>Aucun
                            </div>
                            <div class="media-type-btn" data-type="image">
                                <i class="fas fa-image"></i><br>Image
                            </div>
                            <div class="media-type-btn" data-type="audio">
                                <i class="fas fa-music"></i><br>Audio
                            </div>
                            <div class="media-type-btn" data-type="video">
                                <i class="fas fa-video"></i><br>Vidéo
                            </div>
                        </div>
                    </div>

                    <!-- Upload Image -->
                    <div class="form-group media-input" id="imageInput" style="display: none;">
                        <label>Image (max 10 MB - JPG, PNG, GIF)</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="imageFile" name="imageFile" accept="image/*">
                            <label for="imageFile" class="file-input-label">
                                <i class="fas fa-cloud-upload-alt"></i> Choisir une image
                            </label>
                        </div>
                        <div class="file-preview" id="imagePreview"></div>
                    </div>

                    <!-- Upload Audio -->
                    <div class="form-group media-input" id="audioInput" style="display: none;">
                        <label>Audio (max 10 MB - MP3, WAV)</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="audioFile" name="audioFile" accept="audio/*">
                            <label for="audioFile" class="file-input-label">
                                <i class="fas fa-cloud-upload-alt"></i> Choisir un fichier audio
                            </label>
                        </div>
                        <div class="file-preview" id="audioPreview"></div>
                    </div>

                    <!-- Lien Vidéo -->
                    <div class="form-group media-input" id="videoInput" style="display: none;">
                        <label>Lien vidéo YouTube ou Vimeo</label>
                        <input type="text" id="videoLink" name="videoLink" 
                               placeholder="Ex: https://www.youtube.com/watch?v=...">
                        <small style="color: #666; display: block; margin-top: 5px;">
                            <i class="fas fa-info-circle"></i> Collez le lien de votre vidéo YouTube ou Vimeo
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Publier l'annonce
                    </button>
                </form>
            </div>

            <!-- Section Liste des annonces -->
            <div class="announcements-section">
                <h2 class="section-title">
                    <i class="fas fa-list"></i> Annonces publiées
                </h2>
                <div id="announcementsList">
                    <div class="no-announcements">
                        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                        Aucune annonce pour le moment
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Debug : Afficher l'état de connexion
        console.log("Admin connecté : <?php echo $admin_firstname; ?> (ID: <?php echo $admin_id; ?>)");

        let currentMediaType = 'none';

        // Gestion du sélecteur de type de média
        document.querySelectorAll('.media-type-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.media-type-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                document.querySelectorAll('.media-input').forEach(input => input.style.display = 'none');
                
                currentMediaType = this.dataset.type;
                if (currentMediaType === 'image') {
                    document.getElementById('imageInput').style.display = 'block';
                } else if (currentMediaType === 'audio') {
                    document.getElementById('audioInput').style.display = 'block';
                } else if (currentMediaType === 'video') {
                    document.getElementById('videoInput').style.display = 'block';
                }
            });
        });

        // Prévisualisation image
        document.getElementById('imageFile')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Prévisualisation audio
        document.getElementById('audioFile')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('audioPreview');
                    preview.innerHTML = `<audio controls src="${e.target.result}"></audio>`;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Soumission du formulaire
        document.getElementById('announcementForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();

            if (!title || !content) {
                showAlert('Veuillez remplir tous les champs obligatoires', 'error');
                return;
            }

            if (!confirm('Êtes-vous sûr de vouloir publier cette annonce ?')) {
                return;
            }

            const formData = new FormData();
            formData.append('title', title);
            formData.append('content', content);
            formData.append('media_type', currentMediaType);

            if (currentMediaType === 'image') {
                const imageFile = document.getElementById('imageFile').files[0];
                if (imageFile) {
                    if (imageFile.size > 10 * 1024 * 1024) {
                        showAlert('L\'image ne doit pas dépasser 10 MB', 'error');
                        return;
                    }
                    formData.append('media_file', imageFile);
                }
            } else if (currentMediaType === 'audio') {
                const audioFile = document.getElementById('audioFile').files[0];
                if (audioFile) {
                    if (audioFile.size > 10 * 1024 * 1024) {
                        showAlert('L\'audio ne doit pas dépasser 10 MB', 'error');
                        return;
                    }
                    formData.append('media_file', audioFile);
                }
            } else if (currentMediaType === 'video') {
                const videoLink = document.getElementById('videoLink').value.trim();
                if (videoLink) {
                    formData.append('video_link', videoLink);
                }
            }

            // Désactiver le bouton
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publication...';

            try {
                const response = await fetch('submit_announcement.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin' // Important : envoyer les cookies de session
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('Annonce publiée avec succès !', 'success');
                    document.getElementById('announcementForm').reset();
                    document.querySelectorAll('.media-type-btn').forEach(b => b.classList.remove('active'));
                    document.querySelector('.media-type-btn[data-type="none"]').classList.add('active');
                    currentMediaType = 'none';
                    document.querySelectorAll('.media-input').forEach(input => input.style.display = 'none');
                    document.querySelectorAll('.file-preview').forEach(preview => {
                        preview.style.display = 'none';
                        preview.innerHTML = '';
                    });
                    loadAnnouncements();
                } else {
                    showAlert(data.message || 'Erreur lors de la publication', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showAlert('Une erreur est survenue lors de la publication', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Publier l\'annonce';
            }
        });

        // Charger les annonces
        async function loadAnnouncements() {
            try {
                const response = await fetch('get_announcements.php');
                const data = await response.json();

                const container = document.getElementById('announcementsList');

                if (data.success && data.announcements.length > 0) {
                    container.innerHTML = data.announcements.map(announcement => `
                        <div class="announcement-card">
                            <div class="announcement-header">
                                <div>
                                    <div class="announcement-title">${escapeHtml(announcement.title)}</div>
                                    <div class="announcement-date">
                                        <i class="fas fa-clock"></i> 
                                        ${formatDate(announcement.created_at)}
                                    </div>
                                </div>
                                <button class="btn btn-danger" onclick="deleteAnnouncement(${announcement.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="announcement-content">${escapeHtml(announcement.content)}</div>
                            ${renderMedia(announcement)}
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = `
                        <div class="no-announcements">
                            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                            Aucune annonce pour le moment
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        }

        // Supprimer une annonce
        async function deleteAnnouncement(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?')) {
                return;
            }

            try {
                const response = await fetch('delete_announcement.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ announcement_id: id }),
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('Annonce supprimée avec succès', 'success');
                    loadAnnouncements();
                } else {
                    showAlert(data.message || 'Erreur lors de la suppression', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showAlert('Une erreur est survenue', 'error');
            }
        }

        // Afficher les alertes
        function showAlert(message, type) {
            const alertId = type === 'success' ? 'successAlert' : 'errorAlert';
            const alert = document.getElementById(alertId);
            alert.textContent = message;
            alert.style.display = 'block';

            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000);
        }

        // Formatter la date
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Échapper le HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Rendu du média
        function renderMedia(announcement) {
            if (announcement.media_type === 'none' || !announcement.media_path) {
                return '';
            }

            if (announcement.media_type === 'image') {
                return `<div class="announcement-media"><img src="${announcement.media_path}" alt="Image"></div>`;
            }

            if (announcement.media_type === 'audio') {
                return `<div class="announcement-media"><audio controls src="${announcement.media_path}"></audio></div>`;
            }

            if (announcement.media_type === 'video_link') {
                const embedUrl = getVideoEmbedUrl(announcement.media_path);
                if (embedUrl) {
                    return `<div class="announcement-media"><iframe src="${embedUrl}" frameborder="0" allowfullscreen></iframe></div>`;
                }
            }

            return '';
        }

        // Convertir les liens YouTube/Vimeo en embed
        function getVideoEmbedUrl(url) {
            const youtubeRegex = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
            const youtubeMatch = url.match(youtubeRegex);
            if (youtubeMatch) {
                return `https://www.youtube.com/embed/${youtubeMatch[1]}`;
            }

            const vimeoRegex = /vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/;
            const vimeoMatch = url.match(vimeoRegex);
            if (vimeoMatch) {
                return `https://player.vimeo.com/video/${vimeoMatch[3]}`;
            }

            return null;
        }

        // Charger les annonces au chargement de la page
        loadAnnouncements();
    </script>
</body>
</html>
