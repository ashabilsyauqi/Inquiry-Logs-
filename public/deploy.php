<?php
/**
 * Instant Deployment Helper Script for cPanel
 * Usage: Visit https://crm.difitech.id/deploy.php?secret=difitech2026
 */

$secretKey = 'difitech2026';
$providedSecret = $_GET['secret'] ?? '';

if ($providedSecret !== $secretKey) {
    http_response_code(403);
    die('Unauthorized: Invalid secret key.');
}

header('Content-Type: text/plain');
echo "=== CRM INSTANT DEPLOYMENT STARTED ===\n\n";

$possibleRepoPaths = [
    '/home/sryyuqht/repositories/Inquiry-Logs-',
    '/home/sryyuqht/repositories/inquiry-Logs-',
    '/home/sryyuqht/repositories/inquiry-logs'
];

$repoPath = null;
foreach ($possibleRepoPaths as $path) {
    if (is_dir($path)) {
        $repoPath = $path;
        break;
    }
}

if (!$repoPath) {
    die("Error: Could not find repository folder in /home/sryyuqht/repositories/\n");
}

$targetPath = '/home/sryyuqht/crm.difitech.id';

echo "Using repository path: {$repoPath}\n";

// Step 1: Git Pull from GitHub repository
echo "[1/4] Pulling latest code from GitHub (admin-panel branch)...\n";
$gitOutput = shell_exec("cd {$repoPath} && git pull origin admin-panel 2>&1");
echo $gitOutput . "\n";

// Step 2: Copy files to domain root
echo "[2/4] Syncing files to website directory ({$targetPath})...\n";
$copyOutput = shell_exec("cp -ru {$repoPath}/* {$targetPath}/ 2>&1");
echo "Files synced successfully.\n\n";

// Step 3: Run Database Migrations
echo "[3/4] Running Artisan Migrations...\n";
$migrateOutput = shell_exec("cd {$targetPath} && /usr/local/bin/ea-php83 artisan migrate --force 2>&1");
echo $migrateOutput . "\n";

// Step 4: Clear Laravel Cache
echo "[4/4] Clearing Laravel Caches...\n";
$cacheOutput = shell_exec("cd {$targetPath} && /usr/local/bin/ea-php83 artisan config:clear && /usr/local/bin/ea-php83 artisan view:clear 2>&1");
echo $cacheOutput . "\n";

echo "=== DEPLOYMENT COMPLETED SUCCESSFULLY IN SECONDS! ===";
