<?php
/**
 * Test Index.php Login and Redirect
 */

echo "=== Testing Index.php Login and Redirect ===\n\n";

echo "Manual Test Instructions:\n";
echo "1. Open browser: http://localhost/OG-afrcavenir/\n";
echo "2. You should see the login page (index.php)\n";
echo "3. Login with:\n";
echo "   - Username: testuser\n";
echo "   - Password: password\n";
echo "4. Verify redirect to dashboard-admin.php (admin user)\n";
echo "5. Logout (should return to index.php)\n\n";

echo "To test member redirect:\n";
echo "1. Create a member user with user_level='member'\n";
echo "2. Login as that user\n";
echo "3. Verify redirect to member-contribution.php\n\n";

echo "Current setup:\n";
echo "- index.php = Login page (entry point)\n";
echo "- Admins → dashboard-admin.php\n";
echo "- Members → member-contribution.php\n";
