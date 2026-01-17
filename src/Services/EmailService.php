<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Email Service
 * Handles sending emails for credentials, notifications, and receipts using PHPMailer
 */
class EmailService {
    
    private $config;
    
    public function __construct() {
        // Load configuration
        $this->config = require __DIR__ . '/../../config/email.php';
    }
    
    /**
     * Create and configure PHPMailer instance
     */
    private function createMailer() {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            if ($this->config['use_smtp']) {
                $mail->isSMTP();
                $mail->Host       = $this->config['smtp_host'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $this->config['smtp_username'];
                $mail->Password   = $this->config['smtp_password'];
                $mail->SMTPSecure = $this->config['smtp_encryption'];
                $mail->Port       = $this->config['smtp_port'];
                $mail->SMTPDebug  = $this->config['smtp_debug'] ?? 0;
            }
            
            // Recipients
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->CharSet = 'UTF-8';
            
            return $mail;
        } catch (Exception $e) {
            error_log("Mailer Configuration Error: {$mail->ErrorInfo}");
            return null;
        }
    }
    
    /**
     * Send credentials to new member
     */
    public function sendCredentials($email, $username, $password, $memberName) {
        $subject = "Vos identifiants de connexion - AfricAvenir";
        
        // Load email template
        $htmlBody = $this->loadTemplate('credentials', [
            'NAME' => $memberName,
            'USERNAME' => $username,
            'PASSWORD' => $password,
            'LOGIN_URL' => $this->config['login_url']
        ]);
        
        $textBody = $this->getTextVersion($memberName, $username, $password);
        
        return $this->sendEmail($email, $subject, $htmlBody, $textBody);
    }
    
    /**
     * Send payment receipt
     */
    public function sendPaymentReceipt($email, $memberName, $amount, $transactionId, $date, $paymentMethod = 'Carte bancaire') {
        $subject = "Reçu de paiement - AfricAvenir";
        
        $variables = [
            'NAME' => $memberName,
            'AMOUNT' => number_format($amount, 0, ',', ' '),
            'TRANSACTION_ID' => $transactionId,
            'DATE' => $date,
            'PAYMENT_METHOD' => $paymentMethod,
            'LOGIN_URL' => $this->config['login_url']
        ];
        
        $htmlBody = $this->loadTemplate('payment_receipt', $variables);
        
        // Fallback text body
        $textBody = "Bonjour $memberName,\n\nNous avons bien reçu votre paiement de {$variables['AMOUNT']} FCFA.\n\nRéférence: $transactionId\nDate: $date\nMoyen de paiement: $paymentMethod\n\nMerci pour votre contribution!\n\nAfricAvenir";
        
        return $this->sendEmail($email, $subject, $htmlBody, $textBody);
    }

    /**
     * Send contribution reminder
     */
    public function sendContributionReminder($email, $memberName, $amount, $dueDate) {
        $subject = "Rappel de contribution - AfricAvenir";
        
        $variables = [
            'NAME' => $memberName,
            'AMOUNT' => number_format($amount, 0, ',', ' '),
            'DUE_DATE' => $dueDate,
            'LOGIN_URL' => $this->config['login_url']
        ];
        
        $htmlBody = $this->loadTemplate('contribution_reminder', $variables);
        
        $textBody = "Bonjour $memberName,\n\nSauf erreur de notre part, nous n'avons pas encore reçu votre contribution de {$variables['AMOUNT']} FCFA attendue pour le $dueDate.\n\nMerci de régulariser votre situation en vous connectant à votre espace membre: {$this->config['login_url']}\n\nAfricAvenir";
        
        return $this->sendEmail($email, $subject, $htmlBody, $textBody);
    }
    
    /**
     * Send welcome email
     */
    public function sendWelcomeEmail($email, $memberName) {
        $subject = "Bienvenue à AfricAvenir!";
        
        $htmlBody = "<h1>Bienvenue!</h1><p>Bonjour $memberName,</p><p>Bienvenue à AfricAvenir!</p>";
        $textBody = "Bienvenue!\n\nBonjour $memberName,\n\nBienvenue à AfricAvenir!";
        
        return $this->sendEmail($email, $subject, $htmlBody, $textBody);
    }
    
    /**
     * Load email template and replace placeholders
     */
    private function loadTemplate($templateName, $variables) {
        $templatePath = __DIR__ . '/../../email-templates/' . $templateName . '.html';
        
        if (!file_exists($templatePath)) {
            // Return basic template if file doesn't exist
            return $this->getBasicTemplate($templateName, $variables);
        }
        
        $template = file_get_contents($templatePath);
        
        // Replace placeholders
        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . $key . '}}', htmlspecialchars($value), $template);
        }
        
        return $template;
    }
    
    /**
     * Get basic HTML template if template file doesn't exist
     */
    private function getBasicTemplate($type, $variables) {
        $content = '';
        $title = '';
        
        if ($type === 'credentials') {
            $title = 'Bienvenue à AfricAvenir!';
            $content = '
            <p>Bonjour ' . htmlspecialchars($variables['NAME']) . ',</p>
            <p>Votre compte a été créé avec succès. Voici vos identifiants de connexion:</p>
            
            <div class="box">
                <p><strong>Nom d\'utilisateur:</strong> ' . htmlspecialchars($variables['USERNAME']) . '</p>
                <p><strong>Mot de passe:</strong> ' . htmlspecialchars($variables['PASSWORD']) . '</p>
            </div>
            
            <p>Vous pouvez vous connecter en cliquant sur le bouton ci-dessous:</p>
            <a href="' . htmlspecialchars($variables['LOGIN_URL']) . '" class="button">Se connecter</a>
            
            <p><strong>Important:</strong> Pour votre sécurité, nous vous recommandons de changer votre mot de passe après votre première connexion.</p>';
        } elseif ($type === 'payment_receipt') {
            $title = 'Reçu de Paiement';
            $content = '
            <p>Bonjour ' . htmlspecialchars($variables['NAME']) . ',</p>
            <p>Nous avons bien reçu votre paiement. Voici les détails:</p>
            
            <div class="box">
                <p><strong>Montant:</strong> ' . htmlspecialchars($variables['AMOUNT']) . ' FCFA</p>
                <p><strong>Référence:</strong> ' . htmlspecialchars($variables['TRANSACTION_ID']) . '</p>
                <p><strong>Date:</strong> ' . htmlspecialchars($variables['DATE']) . '</p>
                <p><strong>Moyen de paiement:</strong> ' . htmlspecialchars($variables['PAYMENT_METHOD']) . '</p>
            </div>
            
            <p>Merci pour votre contribution!</p>
            <a href="' . htmlspecialchars($variables['LOGIN_URL']) . '" class="button">Accéder à mon compte</a>';
        } elseif ($type === 'contribution_reminder') {
            $title = 'Rappel de Contribution';
            $content = '
            <p>Bonjour ' . htmlspecialchars($variables['NAME']) . ',</p>
            <p>Sauf erreur de notre part, nous n\'avons pas encore reçu votre contribution.</p>
            
            <div class="box">
                <p><strong>Montant attendu:</strong> ' . htmlspecialchars($variables['AMOUNT']) . ' FCFA</p>
                <p><strong>Date d\'échéance:</strong> ' . htmlspecialchars($variables['DUE_DATE']) . '</p>
            </div>
            
            <p>Merci de régulariser votre situation dès que possible.</p>
            <a href="' . htmlspecialchars($variables['LOGIN_URL']) . '" class="button">Payer ma contribution</a>';
        }

        $html = '<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #FFC107; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .box { background-color: #fff; border: 2px solid #FFC107; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .box p { margin: 10px 0; font-size: 16px; }
        .button { display: inline-block; padding: 12px 24px; background-color: #FFC107; color: #000; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0; color: #000;">AfricAvenir</h1>
        </div>
        <div class="content">
            <h2>' . $title . '</h2>
            ' . $content . '
        </div>
        <div class="footer">
            <p>©' . date('Y') . ' AfricAvenir - Tous droits réservés</p>
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Get plain text version of credentials email
     */
    private function getTextVersion($memberName, $username, $password) {
        return "Bienvenue à AfricAvenir!
\nBonjour $memberName,
\nVotre compte a été créé avec succès. Voici vos identifiants de connexion:
\nNom d'utilisateur: $username
\nMot de passe: $password
\nVous pouvez vous connecter à: {$this->config['login_url']}
\nImportant: Pour votre sécurité, nous vous recommandons de changer votre mot de passe après votre première connexion.
\n©" . date('Y') . " AfricAvenir - Tous droits réservés";
    }
    
    /**
     * Send email using PHPMailer
     */
    private function sendEmail($to, $subject, $htmlBody, $textBody) {
        $mail = $this->createMailer();
        
        if (!$mail) {
            $this->logEmail($to, $subject, false, "Mailer initialization failed");
            return false;
        }
        
        try {
            $mail->addAddress($to);
            
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $textBody;
            
            $mail->send();
            
            $this->logEmail($to, $subject, true);
            return true;
        } catch (Exception $e) {
            $this->logEmail($to, $subject, false, $mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Log email sending attempt
     */
    private function logEmail($to, $subject, $success, $error = '') {
        $logFile = __DIR__ . '/../../logs/email.log';
        $logDir = dirname($logFile);
        
        // Create logs directory if it doesn't exist
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $status = $success ? 'SUCCESS' : 'FAILED';
        $errorMsg = $error ? " - Error: $error" : "";
        $logEntry = "[$timestamp] $status - To: $to, Subject: $subject$errorMsg\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}
