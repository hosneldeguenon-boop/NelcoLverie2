<?php
/**
 * ✅ CONFIGURATION EMAIL CORRIGÉE - Compatible PHPMailer manuel
 * Fonctionne avec PHPMailer téléchargé directement depuis GitHub
 */

// Chargement manuel de PHPMailer (sans Composer)
// Vérifie d'abord si les fichiers existent
$phpmailerPath = __DIR__ . '/PHPMailer/src/';

if (!file_exists($phpmailerPath . 'Exception.php') || 
    !file_exists($phpmailerPath . 'PHPMailer.php') || 
    !file_exists($phpmailerPath . 'SMTP.php')) {
    
    error_log("❌ ERREUR CRITIQUE: Les fichiers PHPMailer sont manquants dans: " . $phpmailerPath);
    error_log("   Assurez-vous que le dossier PHPMailer/src/ contient:");
    error_log("   - Exception.php");
    error_log("   - PHPMailer.php");
    error_log("   - SMTP.php");
    
    // En mode développement, afficher l'erreur
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
        die("❌ ERREUR: PHPMailer n'est pas correctement installé. Vérifiez le dossier PHPMailer/src/");
    }
}

// Charger les classes PHPMailer
require_once $phpmailerPath . 'Exception.php';
require_once $phpmailerPath . 'PHPMailer.php';
require_once $phpmailerPath . 'SMTP.php';

// Importer les classes dans le namespace global
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * ✅ Fonction d'envoi d'email de vérification d'inscription
 */
function sendVerificationEmail($toEmail, $toName, $verificationLink) {
    try {
        error_log("📧 Tentative d'envoi email de vérification à : $toEmail");
        
        // Créer une nouvelle instance PHPMailer
        $mail = new PHPMailer(true);
        
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        // Options SSL pour éviter les erreurs de certificat
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Expéditeur et destinataire
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo(FROM_EMAIL, FROM_NAME);
        
        // Contenu de l'email
        $mail->isHTML(true);
        $mail->Subject = 'Confirmez votre inscription - ' . FROM_NAME;
        
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { 
                    background: linear-gradient(90deg, #3b82f6, #60a5fa); 
                    color: white; 
                    padding: 30px; 
                    text-align: center; 
                    border-radius: 10px 10px 0 0; 
                }
                .content { 
                    background: #f9fafb; 
                    padding: 30px; 
                    border-radius: 0 0 10px 10px; 
                }
                .button-container { text-align: center; margin: 30px 0; }
                .confirm-button { 
                    display: inline-block;
                    background: linear-gradient(90deg, #3b82f6, #60a5fa);
                    color: white;
                    padding: 15px 40px;
                    text-decoration: none;
                    border-radius: 25px;
                    font-weight: bold;
                    font-size: 16px;
                }
                .info-box { 
                    background: #e0f2fe; 
                    border-left: 4px solid #0284c7; 
                    padding: 15px; 
                    margin: 20px 0; 
                    border-radius: 5px; 
                }
                .footer { 
                    text-align: center; 
                    padding: 20px; 
                    color: #6b7280; 
                    font-size: 14px; 
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🎉 Bienvenue chez ' . FROM_NAME . ' !</h1>
                </div>
                <div class="content">
                    <p>Bonjour <strong>' . htmlspecialchars($toName) . '</strong>,</p>
                    <p>Merci de vous être inscrit(e) chez ' . FROM_NAME . ' !</p>
                    <p>Pour activer votre compte et commencer à profiter de nos services, veuillez confirmer votre adresse email en cliquant sur le bouton ci-dessous :</p>
                    
                    <div class="button-container">
                        <a href="' . htmlspecialchars($verificationLink) . '" class="confirm-button">
                            ✅ Confirmer mon inscription
                        </a>
                    </div>
                    
                    <div class="info-box">
                        <strong>ℹ️ Note :</strong> Ce lien est valable pendant 24 heures.
                    </div>
                    
                    <p>Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :</p>
                    <p style="word-break: break-all; color: #3b82f6;">' . htmlspecialchars($verificationLink) . '</p>
                    
                    <p style="margin-top: 30px;">Si vous n\'avez pas créé de compte, ignorez simplement cet email.</p>
                    
                    <p>À bientôt,<br>L\'équipe ' . FROM_NAME . '</p>
                </div>
                <div class="footer">
                    <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        $mail->AltBody = "Bonjour $toName,\n\n"
                       . "Merci de vous être inscrit(e) chez " . FROM_NAME . " !\n\n"
                       . "Pour confirmer votre inscription, cliquez sur ce lien :\n"
                       . "$verificationLink\n\n"
                       . "Ce lien est valable pendant 24 heures.\n\n"
                       . "Cordialement,\nL'équipe " . FROM_NAME;
        
        // Envoyer l'email
        $mail->send();
        
        error_log("✅ Email de vérification envoyé avec succès à : $toEmail");
        return true;
        
    } catch (Exception $e) {
        error_log("❌ Erreur envoi email de vérification: " . $e->getMessage());
        if (isset($mail)) {
            error_log("❌ PHPMailer ErrorInfo: " . $mail->ErrorInfo);
        }
        return false;
    }
}

/**
 * ✅ Fonction d'envoi de code de réinitialisation de mot de passe
 */
function sendResetCode($toEmail, $toName, $code) {
    try {
        error_log("📧 Tentative d'envoi code de réinitialisation à : $toEmail");
        
        $mail = new PHPMailer(true);
        
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Expéditeur et destinataire
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo(FROM_EMAIL, FROM_NAME);
        
        // Contenu de l'email
        $mail->isHTML(true);
        $mail->Subject = 'Code de réinitialisation de mot de passe';
        
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { 
                    background: linear-gradient(90deg, #3b82f6, #60a5fa); 
                    color: white; 
                    padding: 30px; 
                    text-align: center; 
                    border-radius: 10px 10px 0 0; 
                }
                .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
                .code-box { 
                    background: white; 
                    border: 2px solid #3b82f6; 
                    border-radius: 10px; 
                    padding: 20px; 
                    text-align: center; 
                    margin: 20px 0; 
                }
                .code { 
                    font-size: 36px; 
                    font-weight: bold; 
                    color: #3b82f6; 
                    letter-spacing: 8px; 
                }
                .warning { 
                    background: #fef3c7; 
                    border-left: 4px solid #f59e0b; 
                    padding: 15px; 
                    margin: 20px 0; 
                    border-radius: 5px; 
                }
                .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🔐 Réinitialisation de mot de passe</h1>
                </div>
                <div class="content">
                    <p>Bonjour <strong>' . htmlspecialchars($toName) . '</strong>,</p>
                    <p>Vous avez demandé à réinitialiser votre mot de passe. Voici votre code de vérification :</p>
                    
                    <div class="code-box">
                        <div class="code">' . htmlspecialchars($code) . '</div>
                    </div>
                    
                    <p><strong>Ce code est valable pendant 30 minutes.</strong></p>
                    
                    <div class="warning">
                        <strong>⚠️ Important :</strong> Si vous n\'avez pas demandé cette réinitialisation, ignorez cet email.
                    </div>
                    
                    <p>Cordialement,<br>L\'équipe ' . FROM_NAME . '</p>
                </div>
                <div class="footer">
                    <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        $mail->AltBody = "Bonjour $toName,\n\n"
                       . "Votre code de réinitialisation : $code\n\n"
                       . "Ce code est valable pendant 30 minutes.\n\n"
                       . "Cordialement,\nL'équipe " . FROM_NAME;
        
        $mail->send();
        
        error_log("✅ Email de réinitialisation envoyé avec succès à : $toEmail");
        return true;
        
    } catch (Exception $e) {
        error_log("❌ Erreur envoi email de réinitialisation: " . $e->getMessage());
        if (isset($mail)) {
            error_log("❌ PHPMailer ErrorInfo: " . $mail->ErrorInfo);
        }
        return false;
    }
}

error_log("✅ email_config.php chargé avec succès");
?>
