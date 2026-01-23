<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avis Clients - NELCO LAVERIE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background-color: #f8f9fa;
            color: #333;
        }

        .navbar {
            background: linear-gradient(90deg, #3b82f6, #60a5fa);
            padding: 15px 30px;
            box-shadow: 0 4px 12px rgba(59,130,246,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 {
            color: #fff;
            margin: 0;
            font-size: 22px;
        }

        .navbar a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 15px;
            border-radius: 15px;
            background-color: rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }

        .navbar a:hover {
            background-color: rgba(255,255,255,0.3);
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex !important;
        }

        .modal-content {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #333;
        }

        .modal-close {
            float: right;
            font-size: 24px;
            cursor: pointer;
            color: #999;
            transition: color 0.3s ease;
        }

        .modal-close:hover {
            color: #333;
        }

        .input-box {
            width: 100%;
            margin-bottom: 15px;
        }

        .input-box input,
        .input-box textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .input-box input:focus,
        .input-box textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }

        .input-box textarea {
            resize: vertical;
            min-height: 100px;
            max-height: 200px;
        }

        .star-rating {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            font-size: 30px;
            justify-content: center;
        }

        .star {
            cursor: pointer;
            color: #ddd;
            transition: all 0.2s ease;
        }

        .star:hover,
        .star.active {
            color: #ffc107;
            transform: scale(1.2);
        }

        .char-count {
            font-size: 11px;
            color: #999;
            text-align: right;
            margin-top: 5px;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            color: #fff;
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59,130,246,0.4);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-secondary {
            background-color: #f5f5f5;
            color: #666;
        }

        .btn-secondary:hover {
            background-color: #e0e0e0;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
            font-size: 14px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .alert.error {
            background-color: #fee;
            border: 1px solid #fcc;
            color: #c33;
        }

        .alert.success {
            background-color: #efe;
            border: 1px solid #cfc;
            color: #3c3;
        }

        .comments-section {
            margin-top: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 24px;
            font-weight: 700;
            color: #333;
        }

        .write-btn {
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .write-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59,130,246,0.3);
        }

        .comment-card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #3b82f6;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .comment-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            transform: translateX(4px);
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .comment-user {
            font-weight: 700;
            color: #333;
            font-size: 15px;
        }

        .comment-meta {
            display: flex;
            gap: 15px;
            align-items: center;
            font-size: 12px;
            margin-top: 4px;
        }

        .comment-date {
            color: #999;
        }

        .comment-rating {
            color: #ffc107;
        }

        .comment-text {
            color: #555;
            line-height: 1.7;
            font-size: 14px;
        }

        .comment-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }

        .action-btn {
            background: none;
            border: 1px solid #ddd;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .action-btn.edit:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            background: #f0f7ff;
        }

        .action-btn.delete:hover {
            border-color: #ff4444;
            color: #ff4444;
            background: #fff5f5;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.4;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .auth-notice {
            background: #f0f7ff;
            border: 2px solid #3b82f6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #2563eb;
        }

        .auth-notice i {
            font-size: 20px;
        }

        .info-box {
            background: #fffbea;
            border-left: 4px solid #ffc107;
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 6px;
            font-size: 13px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1><i class="fas fa-comments"></i> Avis Clients</h1>
        <a href="index.html"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>

    <div class="container">
        <div class="alert" id="alertBox"></div>

        <div class="comments-section">
            <div class="section-header">
                <h2 class="section-title">Avis des Clients</h2>
                <button class="write-btn" onclick="handleWriteClick()">
                    <i class="fas fa-pen"></i>
                    <span>Écrire un avis</span>
                </button>
            </div>

            <div id="commentsContainer" class="loading">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Chargement des avis...</p>
            </div>
        </div>
    </div>

    <!-- MODAL CONNEXION / COMMENTAIRE -->
    <div class="modal" id="commentModal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <h3 class="modal-header" id="modalTitle">Votre Avis</h3>
            
            <div class="alert" id="modalAlert"></div>

            <!-- FORMULAIRE CONNEXION -->
            <div id="loginForm" style="display:none;">
                <div class="auth-notice">
                    <i class="fas fa-lock"></i>
                    <span>Connectez-vous pour publier un avis</span>
                </div>
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    Seuls les clients ayant passé commande peuvent laisser un avis
                </div>
                <div class="input-box">
                    <input type="email" id="loginEmail" placeholder="Votre email" autocomplete="email">
                </div>
                <div class="input-box">
                    <input type="password" id="loginPassword" placeholder="Votre mot de passe" autocomplete="current-password">
                </div>
                <div class="modal-actions">
                    <button class="btn btn-secondary" onclick="closeModal()">Annuler</button>
                    <button class="btn btn-primary" id="loginBtn" onclick="loginUser()">
                        <i class="fas fa-sign-in-alt"></i> Connexion
                    </button>
                </div>
            </div>

            <!-- FORMULAIRE COMMENTAIRE -->
            <div id="commentForm" style="display:none;">
                <div class="input-box">
                    <label style="display:block;margin-bottom:8px;font-weight:600;font-size:13px;color:#666;">
                        Votre Note (optionnelle)
                    </label>
                    <div class="star-rating" id="stars">
                        <span class="star" onclick="setRating(1)">★</span>
                        <span class="star" onclick="setRating(2)">★</span>
                        <span class="star" onclick="setRating(3)">★</span>
                        <span class="star" onclick="setRating(4)">★</span>
                        <span class="star" onclick="setRating(5)">★</span>
                    </div>
                </div>
                <div class="input-box">
                    <label style="display:block;margin-bottom:8px;font-weight:600;font-size:13px;color:#666;">
                        Votre Commentaire *
                    </label>
                    <textarea id="commentText" placeholder="Partagez votre expérience avec NELCO LAVERIE... (10-500 caractères)" maxlength="500" oninput="updateCharCount()"></textarea>
                    <div class="char-count"><span id="charCount">0</span>/500 caractères</div>
                </div>
                <div class="info-box">
                    <i class="fas fa-shield-alt"></i>
                    Les commentaires contenant un langage vulgaire ou inapproprié seront automatiquement rejetés
                </div>
                <div class="modal-actions">
                    <button class="btn btn-secondary" onclick="closeModal()">Annuler</button>
                    <button class="btn btn-primary" id="submitBtn" onclick="submitComment()">
                        <i class="fas fa-paper-plane"></i> <span id="submitText">Publier</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // VARIABLES GLOBALES
        let userLoggedIn = false;
        let userId = null;
        let selectedRating = 0;
        let editingCommentId = null;

        // INITIALISATION
        document.addEventListener('DOMContentLoaded', () => {
            console.log('🚀 Initialisation page commentaires');
            checkAuth();
            loadComments();
        });

        // VÉRIFIER AUTHENTIFICATION
        async function checkAuth() {
            try {
                const res = await fetch('check_user_auth.php?t=' + Date.now());
                const data = await res.json();
                
                userLoggedIn = data.authenticated === true;
                userId = data.user_id || null;
                
                console.log(userLoggedIn ? '✅ Connecté - User #' + userId : '❌ Non connecté');
            } catch (err) {
                console.error('⚠️ Erreur auth:', err);
                userLoggedIn = false;
                userId = null;
            }
        }

        // GÉRER CLIC "ÉCRIRE UN AVIS"
        function handleWriteClick() {
            resetModal();
            
            if (userLoggedIn) {
                showCommentForm();
            } else {
                showLoginForm();
            }
            
            document.getElementById('commentModal').classList.add('show');
        }

        // AFFICHER FORMULAIRE CONNEXION
        function showLoginForm() {
            document.getElementById('loginForm').style.display = 'block';
            document.getElementById('commentForm').style.display = 'none';
            document.getElementById('modalTitle').textContent = 'Connexion Requise';
        }

        // AFFICHER FORMULAIRE COMMENTAIRE
        function showCommentForm() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('commentForm').style.display = 'block';
            document.getElementById('modalTitle').textContent = editingCommentId ? 'Modifier votre Avis' : 'Votre Avis';
            document.getElementById('submitText').textContent = editingCommentId ? 'Modifier' : 'Publier';
        }

        // CONNEXION UTILISATEUR
        async function loginUser() {
            const email = document.getElementById('loginEmail').value.trim();
            const password = document.getElementById('loginPassword').value;
            const btn = document.getElementById('loginBtn');

            if (!email || !password) {
                showAlert('Veuillez remplir tous les champs', 'error', true);
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Connexion...';

            try {
                const res = await fetch('login.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({email, password, remember: false})
                });

                const data = await res.json();

                if (data.success) {
                    userId = data.user.id;
                    userLoggedIn = true;
                    
                    showAlert('✓ Connexion réussie !', 'success', true);
                    
                    setTimeout(() => {
                        showCommentForm();
                        document.getElementById('modalAlert').style.display = 'none';
                    }, 1000);
                } else {
                    showAlert(data.message || 'Erreur de connexion', 'error', true);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Connexion';
                }
            } catch (err) {
                console.error('⚠️ Erreur:', err);
                showAlert('Erreur de connexion', 'error', true);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Connexion';
            }
        }

        // SOUMETTRE / MODIFIER COMMENTAIRE
        async function submitComment() {
            const text = document.getElementById('commentText').value.trim();
            const btn = document.getElementById('submitBtn');

            // Validations
            if (!text) {
                showAlert('Veuillez écrire un commentaire', 'error', true);
                return;
            }

            if (text.length < 10) {
                showAlert('Le commentaire doit contenir au moins 10 caractères', 'error', true);
                return;
            }

            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';

            try {
                const endpoint = editingCommentId ? 'update_comment.php' : 'add_comment.php';
                const payload = {
                    comment_text: text,
                    rating: selectedRating > 0 ? selectedRating : null
                };

                if (editingCommentId) {
                    payload.comment_id = editingCommentId;
                }

                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (data.success) {
                    showAlert('✓ ' + data.message, 'success', true);
                    setTimeout(() => {
                        closeModal();
                        loadComments();
                    }, 1500);
                } else {
                    showAlert(data.message, 'error', true);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (err) {
                console.error('⚠️ Erreur:', err);
                showAlert('Erreur: ' + err.message, 'error', true);
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        // CHARGER LES COMMENTAIRES
        async function loadComments() {
            const container = document.getElementById('commentsContainer');
            container.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i><p>Chargement...</p></div>';

            try {
                const res = await fetch('get_comments.php?t=' + Date.now());
                const data = await res.json();

                if (!data.success || !data.comments || data.comments.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-comments"></i>
                            <p>Aucun avis pour le moment.<br>Soyez le premier à partager votre expérience !</p>
                        </div>
                    `;
                    return;
                }

                const html = data.comments.map(c => {
                    const date = new Date(c.created_at).toLocaleDateString('fr-FR', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });

                    const stars = c.rating > 0 ? '★'.repeat(c.rating) : '';
                    const isOwner = userId && parseInt(userId) === parseInt(c.user_id);

                    return `
                        <div class="comment-card">
                            <div class="comment-header">
                                <div>
                                    <div class="comment-user">${escapeHtml(c.firstname)} ${escapeHtml(c.lastname)}</div>
                                    <div class="comment-meta">
                                        <span class="comment-date">${date}</span>
                                        ${stars ? `<span class="comment-rating">${stars}</span>` : ''}
                                    </div>
                                </div>
                            </div>
                            <div class="comment-text">${escapeHtml(c.comment_text)}</div>
                            ${isOwner ? `
                                <div class="comment-actions">
                                    <button class="action-btn edit" onclick='editComment(${c.id}, \`${c.comment_text.replace(/`/g, '\\`')}\`, ${c.rating || 0})'>
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                    <button class="action-btn delete" onclick="deleteComment(${c.id})">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </div>
                            ` : ''}
                        </div>
                    `;
                }).join('');

                container.innerHTML = html;
                console.log('✅ ' + data.comments.length + ' commentaires affichés');
            } catch (err) {
                console.error('⚠️ Erreur:', err);
                container.innerHTML = '<div class="empty-state"><p>Erreur de chargement des commentaires</p></div>';
            }
        }

        // ÉDITER UN COMMENTAIRE
        function editComment(id, text, rating) {
            editingCommentId = id;
            selectedRating = rating;
            document.getElementById('commentText').value = text;
            updateCharCount();
            updateStars();
            handleWriteClick();
        }

        // SUPPRIMER UN COMMENTAIRE
        async function deleteComment(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')) return;

            try {
                const res = await fetch('delete_user_comment.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({comment_id: id})
                });

                const data = await res.json();
                
                if (data.success) {
                    showAlert('✓ ' + data.message, 'success');
                    setTimeout(loadComments, 1000);
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (err) {
                showAlert('Erreur lors de la suppression', 'error');
            }
        }

        // SYSTÈME DE NOTATION (ÉTOILES)
        function setRating(rating) {
            selectedRating = rating;
            updateStars();
        }

        function updateStars() {
            document.querySelectorAll('.star').forEach((s, i) => {
                s.classList.toggle('active', i < selectedRating);
            });
        }

        // COMPTEUR DE CARACTÈRES
        function updateCharCount() {
            const text = document.getElementById('commentText').value;
            document.getElementById('charCount').textContent = text.length;
        }

        // GESTION MODAL
        function closeModal() {
            document.getElementById('commentModal').classList.remove('show');
            resetModal();
        }

        function resetModal() {
            document.getElementById('commentText').value = '';
            document.getElementById('loginEmail').value = '';
            document.getElementById('loginPassword').value = '';
            document.getElementById('charCount').textContent = '0';
            document.getElementById('modalAlert').style.display = 'none';
            document.getElementById('submitBtn').disabled = false;
            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-paper-plane"></i> <span id="submitText">Publier</span>';
            selectedRating = 0;
            editingCommentId = null;
            updateStars();
        }

        // AFFICHER ALERTE
        function showAlert(msg, type, isModal = false) {
            const alert = document.getElementById(isModal ? 'modalAlert' : 'alertBox');
            alert.className = 'alert ' + type;
            alert.textContent = msg;
            alert.style.display = 'block';
            
            if (type === 'success') {
                setTimeout(() => alert.style.display = 'none', 4000);
            }
        }

        // ÉCHAPPER HTML (SÉCURITÉ)
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // FERMER MODAL EN CLIQUANT À L'EXTÉRIEUR
        window.onclick = (e) => {
            if (e.target.id === 'commentModal') {
                closeModal();
            }
        };
    </script>
</body>
</html>