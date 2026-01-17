<?php
namespace App\Controllers;

require_once __DIR__ . '/../Models/Member.php';
require_once __DIR__ . '/../Models/Contribution.php';
require_once __DIR__ . '/AuthController.php';

use App\Models\Member;
use App\Models\Contribution;

class StatsController {
    private $memberModel;
    private $contributionModel;
    private $auth;

    public function __construct() {
        $this->memberModel = new Member();
        $this->contributionModel = new Contribution();
        $this->auth = new AuthController();
        $this->auth->requireLogin();
    }

    public function getDashboardStats($year = null, $month = null) {
        $year = $year ?? date('Y');
        
        // 1. Get Member Counts by Category
        $rawMemberCounts = $this->memberModel->getMemberCountsByCategory();
        $memberCounts = [];
        foreach ($rawMemberCounts as $cat => $count) {
            $memberCounts[strtolower($cat)] = $count;
        }
        
        // 2. Get Contribution Totals by Category
        $rawContributionTotals = $this->contributionModel->getContributionsByCategory($year);
        $contributionTotals = [];
        foreach ($rawContributionTotals as $cat => $total) {
            $contributionTotals[strtolower($cat)] = $total;
        }
        
        // 3. Get Monthly Stats for Graph
        $monthlyStats = $this->contributionModel->getMonthlyStats($year);

        // Format data for dashboard
        $stats = [
            'member_ships' => [
                'count' => $memberCounts['member-ships'] ?? 0,
                'total' => $contributionTotals['member-ships'] ?? 0
            ],
            'alumni' => [
                'count' => $memberCounts['alumni'] ?? 0,
                'total' => $contributionTotals['alumni'] ?? 0
            ],
            'cercle_amis' => [
                'count' => $memberCounts['cercle-amis'] ?? 0,
                'total' => $contributionTotals['cercle-amis'] ?? 0
            ],
            'mecenes' => [
                'count' => $memberCounts['mecenes'] ?? 0,
                'total' => $contributionTotals['mecenes'] ?? 0
            ],
            'graph_data' => $monthlyStats
        ];

        return $stats;
    }
}
