<?php
/**
 * Classe Mailer - Gestion de l'envoi d'emails
 * Utilise PHPMailer pour l'envoi SMTP
 */

class Mailer
{
    /**
     * Envoyer un email simple
     * 
     * @param string $to Destinataire
     * @param string $subject Sujet
     * @param string $body Corps de l'email (HTML)
     * @return bool
     */
    public static function send($to, $subject, $body)
    {
        // Pour l'instant, utilisation de la fonction mail() de PHP
        // En production, utiliser PHPMailer avec SMTP
        
        $headers = [
            'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM . '>',
            'Reply-To: ' . SMTP_FROM,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8'
        ];

        return mail($to, $subject, $body, implode("\r\n", $headers));
    }

    /**
     * Envoyer un email de bienvenue
     * 
     * @param string $to Email du destinataire
     * @param string $name Nom du destinataire
     * @param string $verificationToken Token de vérification
     * @return bool
     */
    public static function sendWelcomeEmail($to, $name, $verificationToken)
    {
        $subject = "Bienvenue sur AlgoCodeBF - Vérifiez votre compte";
        $verificationLink = BASE_URL . "/auth/verify?token=" . $verificationToken;
        
        $body = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h2 style='color: #2c3e50;'>Bienvenue sur AlgoCodeBF, {$name}!</h2>
            <p>Merci de rejoindre la communauté des informaticiens du Burkina Faso 🇧🇫</p>
            <p>Pour activer votre compte, veuillez cliquer sur le lien ci-dessous :</p>
            <p>
                <a href='{$verificationLink}' style='background-color: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                    Vérifier mon compte
                </a>
            </p>
            <p>Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :</p>
            <p>{$verificationLink}</p>
            <p>À bientôt sur AlgoCodeBF!</p>
            <hr>
            <p style='color: #7f8c8d; font-size: 12px;'>
                Cet email a été envoyé automatiquement, merci de ne pas y répondre.
            </p>
        </body>
        </html>
        ";

        return self::send($to, $subject, $body);
    }

    /**
     * Envoyer un email de réinitialisation de mot de passe
     * 
     * @param string $to Email du destinataire
     * @param string $name Nom du destinataire
     * @param string $resetToken Token de réinitialisation
     * @return bool
     */
    public static function sendPasswordResetEmail($to, $name, $resetToken)
    {
        $subject = "Réinitialisation de votre mot de passe - AlgoCodeBF";
        $resetLink = BASE_URL . "/auth/resetPassword?token=" . $resetToken;
        
        $body = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h2 style='color: #2c3e50;'>Réinitialisation de mot de passe</h2>
            <p>Bonjour {$name},</p>
            <p>Vous avez demandé la réinitialisation de votre mot de passe sur AlgoCodeBF.</p>
            <p>Pour créer un nouveau mot de passe, cliquez sur le lien ci-dessous :</p>
            <p>
                <a href='{$resetLink}' style='background-color: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                    Réinitialiser mon mot de passe
                </a>
            </p>
            <p>Ce lien est valide pendant 1 heure.</p>
            <p>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
            <hr>
            <p style='color: #7f8c8d; font-size: 12px;'>
                Cet email a été envoyé automatiquement, merci de ne pas y répondre.
            </p>
        </body>
        </html>
        ";

        return self::send($to, $subject, $body);
    }
}

