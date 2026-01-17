<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_level'] = 3; // Admin level

require_once 'src/Controllers/StatsController.php';

$statsController = new \App\Controllers\StatsController();
$stats = $statsController->getDashboardStats(2025);

echo "=== DASHBOARD STATS ===\n\n";

echo "Member-ships:\n";
echo "  Count: " . $stats['member_ships']['count'] . "\n";
echo "  Total: " . number_format($stats['member_ships']['total'], 0, ',', ' ') . " FCFA\n\n";

echo "Alumni:\n";
echo "  Count: " . $stats['alumni']['count'] . "\n";
echo "  Total: " . number_format($stats['alumni']['total'], 0, ',', ' ') . " FCFA\n\n";

echo "Cercle d'amis:\n";
echo "  Count: " . $stats['cercle_amis']['count'] . "\n";
echo "  Total: " . number_format($stats['cercle_amis']['total'], 0, ',', ' ') . " FCFA\n\n";

echo "Mécènes:\n";
echo "  Count: " . $stats['mecenes']['count'] . "\n";
echo "  Total: " . number_format($stats['mecenes']['total'], 0, ',', ' ') . " FCFA\n\n";

echo "Graph Data (Monthly):\n";
print_r($stats['graph_data']);
