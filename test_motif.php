<?php
/**
 * Test Script: Verify Motif Field Implementation
 * Run this from command line: php test_motif.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Models/Contribution.php';

use App\Models\Contribution;

echo "=== Test du champ Motif ===\n\n";

$contributionModel = new Contribution();

// Test 1: Création avec motif "Adhésion"
echo "Test 1: Création avec motif 'Adhésion'\n";
$result1 = $contributionModel->create(
    34, // user_id
    10000, // amount
    'TEST-ADHESION-' . time(), // transaction_id
    'pending',
    'Test',
    null,
    'Adhésion' // motif
);
echo "Résultat: " . ($result1 ? "✅ Succès" : "❌ Échec") . "\n\n";

// Test 2: Création avec motif "Don"
echo "Test 2: Création avec motif 'Don'\n";
$result2 = $contributionModel->create(
    34,
    5000,
    'TEST-DON-' . time(),
    'pending',
    'Test',
    null,
    'Don'
);
echo "Résultat: " . ($result2 ? "✅ Succès" : "❌ Échec") . "\n\n";

// Test 3: Création avec motif par défaut
echo "Test 3: Création avec motif par défaut (Contribution)\n";
$result3 = $contributionModel->create(
    34,
    3000,
    'TEST-DEFAULT-' . time(),
    'pending',
    'Test'
    // motif non spécifié, devrait être "Contribution"
);
echo "Résultat: " . ($result3 ? "✅ Succès" : "❌ Échec") . "\n\n";

// Vérifier les données créées
echo "=== Vérification des données ===\n";
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->query("SELECT transaction_id, motif, amount FROM app_contributions WHERE transaction_id LIKE 'TEST-%' ORDER BY id DESC LIMIT 3");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $row) {
    echo "Transaction: {$row['transaction_id']}\n";
    echo "Motif: {$row['motif']}\n";
    echo "Montant: {$row['amount']} FCFA\n";
    echo "---\n";
}

// Nettoyage
echo "\n=== Nettoyage ===\n";
$conn->exec("DELETE FROM app_contributions WHERE transaction_id LIKE 'TEST-%'");
echo "✅ Données de test supprimées\n";

echo "\n=== Tests terminés ===\n";
