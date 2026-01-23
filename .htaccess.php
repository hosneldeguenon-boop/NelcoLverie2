# ========================================
# FICHIER .htaccess POUR INFINITYFREE
# À placer à la racine de /htdocs/
# ========================================

# Activer le moteur de réécriture
RewriteEngine On

# ========================================
# SÉCURITÉ
# ========================================

# Forcer HTTPS (InfinityFree le supporte)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Bloquer l'accès aux fichiers sensibles
<FilesMatch "(^\.htaccess|^\.htpasswd|config\.php|configs\.php|\.log|\.sql|\.md|composer\.(json|lock)|package\.(json|lock))$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protéger le dossier vendor/ (PHPMailer)
<IfModule mod_rewrite.c>
    RewriteRule ^vendor/.*$ - [F,L]
</IfModule>

# Désactiver l'affichage du répertoire
Options -Indexes

# Protection XSS
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# ========================================
# CONFIGURATION PHP
# ========================================

# Limites d'upload
php_value upload_max_filesize 2M
php_value post_max_size 3M
php_value max_execution_time 300
php_value max_input_time 300

# Masquer les erreurs PHP en production
php_flag display_errors Off
php_flag display_startup_errors Off
php_value error_reporting -1
php_flag log_errors On
php_value error_log /home/your_username/htdocs/errors.log

# Sessions
php_value session.cookie_lifetime 0
php_flag session.cookie_httponly On
php_flag session.use_strict_mode On

# Timezone
php_value date.timezone "Africa/Porto-Novo"

# ========================================
# PAGES PAR DÉFAUT
# ========================================

DirectoryIndex acceuil.php index.php index.html

# ========================================
# GESTION DES ERREURS
# ========================================

# Page d'erreur 404 personnalisée (optionnel)
# ErrorDocument 404 /404.php

# Page d'erreur 500 personnalisée (optionnel)
# ErrorDocument 500 /500.php

# ========================================
# COMPRESSION ET CACHE
# ========================================

# Activer la compression Gzip
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>

# Cache navigateur pour les fichiers statiques
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType application/pdf "access plus 1 month"
</IfModule>

# ========================================
# RÉÉCRITURE D'URL (OPTIONNEL - URL PROPRES)
# ========================================

# Exemple : /page au lieu de /page.php
# Décommentez si vous voulez des URLs propres

# RewriteCond %{REQUEST_FILENAME} !-d
# RewriteCond %{REQUEST_FILENAME} !-f
# RewriteCond %{REQUEST_FILENAME}.php -f
# RewriteRule ^(.+)$ $1.php [L,QSA]

# ========================================
# PROTECTION HOTLINKING IMAGES (OPTIONNEL)
# ========================================

# Empêcher d'autres sites d'utiliser vos images
# Remplacez "yourdomain.com" par votre domaine

# RewriteCond %{HTTP_REFERER} !^$
# RewriteCond %{HTTP_REFERER} !^https://(www\.)?yourdomain\.com [NC]
# RewriteRule \.(jpg|jpeg|png|gif|svg)$ - [F,L]

# ========================================
# CORS (SI NÉCESSAIRE POUR API)
# ========================================

# Si vous avez des appels AJAX cross-domain
# Décommentez uniquement si nécessaire

# <IfModule mod_headers.c>
#     Header set Access-Control-Allow-Origin "*"
#     Header set Access-Control-Allow-Methods "GET, POST, OPTIONS"
#     Header set Access-Control-Allow-Headers "Content-Type"
# </IfModule>

# ========================================
# FIN DU FICHIER
# ========================================