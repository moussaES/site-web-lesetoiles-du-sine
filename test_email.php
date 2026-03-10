<?php
require_once __DIR__ . '/includes/config.php';

echo "Test d'envoi d'email\n";
echo "==================\n\n";

// Test avec un email factice
$testEmail = "faye70286@gmail.com"; // Remplacez par votre email de test
$subject = "Test ImmoAgence";
$message = "<h1>Test réussi !</h1><p>Cette fonctionnalité d'email fonctionne correctement.</p>";

if (sendEmail($testEmail, $subject, $message)) {
    echo "✅ Email envoyé avec succès (simulation)\n";
} else {
    echo "❌ Erreur lors de l'envoi de l'email\n";
}

echo "\nConfiguration SMTP actuelle :\n";
echo "Host: " . SMTP_HOST . "\n";
echo "Port: " . SMTP_PORT . "\n";
echo "User: " . SMTP_USER . "\n";
echo "From: " . SMTP_FROM . "\n";
echo "From Name: " . SMTP_FROM_NAME . "\n";
?>