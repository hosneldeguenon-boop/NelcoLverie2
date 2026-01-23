<?php
/**
 * Configuration pour l'envoi d'emails
 * Utilise PHPMailer pour l'envoi via SMTP
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once 'vendor/autoload.php';

/**
 * Configuration SMTP
 */
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'hosneldeguenon@gmail.com');
define('SMTP_PASSWORD', 'vmdg xivb sicm wjny');
define('SMTP_FROM_EMAIL', 'hosneldeguenon@gmail.com');
define('SMTP_FROM_NAME', 'NelcoLaverie');

/**
 * Envoie un code de réinitialisation par email
 */
function sendResetCode($toEmail, $toName, $code) {
    try {
        error_log("📧 Tentative d'envoi email à : $toEmail");
        
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
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        
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
                .header { background: linear-gradient(90deg, #3b82f6, #60a5fa); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
                .code-box { background: white; border: 2px solid #3b82f6; border-radius: 10px; padding: 20px; text-align: center; margin: 20px 0; }
                .code { font-size: 36px; font-weight: bold; color: #3b82f6; letter-spacing: 8px; }
                .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 5px; }
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
                        <div class="code">' . $code . '</div>
                    </div>
                    
                    <p><strong>Ce code est valable pendant 30 minutes.</strong></p>
                    
                    <div class="warning">
                        <strong>⚠️ Important :</strong> Si vous n\'avez pas demandé cette réinitialisation, ignorez cet email.
                    </div>
                    
                    <p>Cordialement,<br>L\'équipe ' . SMTP_FROM_NAME . '</p>
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
                       . "Cordialement,\nL'équipe " . SMTP_FROM_NAME;
        
        $mail->send();
        
        error_log("✅ Email envoyé avec succès à : $toEmail");
        return true;
        
    } catch (Exception $e) {
        error_log("❌ Erreur envoi email: " . $mail->ErrorInfo);
        error_log("❌ Exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Envoie un email de vérification d'inscription
 */
function sendVerificationEmail($toEmail, $toName, $verificationLink) {
    try {
        error_log("📧 Envoi email de vérification à : $toEmail");
        
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
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        
        // Contenu de l'email
        $mail->isHTML(true);
        $mail->Subject = 'Confirmez votre inscription - ' . SMTP_FROM_NAME;
        
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(90deg, #3b82f6, #60a5fa); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
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
                .info-box { background: #e0f2fe; border-left: 4px solid #0284c7; padding: 15px; margin: 20px 0; border-radius: 5px; }
                .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🎉 Bienvenue chez ' . SMTP_FROM_NAME . ' !</h1>
                </div>
                <div class="content">
                    <p>Bonjour <strong>' . htmlspecialchars($toName) . '</strong>,</p>
                    <p>Merci de vous être inscrit(e) chez ' . SMTP_FROM_NAME . ' !</p>
                    <p>Pour activer votre compte et commencer à profiter de nos services, veuillez confirmer votre adresse email en cliquant sur le bouton ci-dessous :</p>
                    
                    <div class="button-container">
                        <a href="' . $verificationLink . '" class="confirm-button">
                            ✅ Confirmer mon inscription
                        </a>
                    </div>
                    
                    <div class="info-box">
                        <strong>ℹ️ Note :</strong> Ce lien est valable pendant 24 heures.
                    </div>
                    
                    <p>Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :</p>
                    <p style="word-break: break-all; color: #3b82f6;">' . $verificationLink . '</p>
                    
                    <p style="margin-top: 30px;">Si vous n\'avez pas créé de compte, ignorez simplement cet email.</p>
                    
                    <p>À bientôt,<br>L\'équipe ' . SMTP_FROM_NAME . '</p>
                </div>
                <div class="footer">
                    <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        $mail->AltBody = "Bonjour $toName,\n\n"
                       . "Merci de vous être inscrit(e) chez " . SMTP_FROM_NAME . " !\n\n"
                       . "Pour confirmer votre inscription, cliquez sur ce lien :\n"
                       . "$verificationLink\n\n"
                       . "Ce lien est valable pendant 24 heures.\n\n"
                       . "Cordialement,\nL'équipe " . SMTP_FROM_NAME;
        
        $mail->send();
        
        error_log("✅ Email de vérification envoyé avec succès");
        return true;
        
    } catch (Exception $e) {
        error_log("❌ Erreur envoi email: " . $mail->ErrorInfo);
        error_log("❌ Exception: " . $e->getMessage());
        return false;
    }
}
?>