<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$pageTitle = 'Dashboard';
require_once __DIR__ . '/header.php';

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->query("SELECT COUNT(*) FROM form_submissions");
    $submissionsCount = $stmt->fetchColumn();
    
    $stmt = $db->query("SELECT COUNT(DISTINCT form_id) FROM form_submissions");
    $formTypes = $stmt->fetchColumn();
    
    $stmt = $db->query("SELECT COUNT(*) FROM lawyers");
    $lawyersCount = $stmt->fetchColumn();
    
    $stmt = $db->query("SELECT * FROM form_submissions ORDER BY submitted_at DESC LIMIT 5");
    $recentSubmissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($recentSubmissions as &$sub) {
        $sub['data'] = json_decode($sub['data'], true);
    }
} catch(Throwable $e) {
    $submissionsCount = 0;
    $formTypes = 0;
    $lawyersCount = 0;
    $recentSubmissions = [];
    $message = 'Database error: ' . $e->getMessage();
    $messageType = 'error';
}
?>

<?php if (isset($message)): ?>
<div style="background: <?php echo $messageType === 'success' ? '#064e3b' : '#7f1d1d'; ?>; color: <?php echo $messageType === 'success' ? '#34d399' : '#fca5a5'; ?>; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php echo $submissionsCount; ?></div>
        <div class="stat-label">Total Submissions</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $formTypes; ?></div>
        <div class="stat-label">Active Forms</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $lawyersCount; ?></div>
        <div class="stat-label">Team Members</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Recent Submissions</h2>
        <a href="forms.php" class="btn btn-primary btn-sm">View All</a>
    </div>
    <?php if (empty($recentSubmissions)): ?>
        <div class="empty-state">
            <p>No submissions yet</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Form</th>
                    <th>Name</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentSubmissions as $sub): ?>
                <tr>
                    <td><?php echo date('M j, Y g:i A', strtotime($sub['submitted_at'])); ?></td>
                    <td><span class="badge badge-blue"><?php echo htmlspecialchars($sub['form_name']); ?></span></td>
                    <td><?php echo htmlspecialchars($sub['data']['wpforms[fields][13][first]'] ?? $sub['data']['wpforms[fields][19][first]'] ?? 'N/A'); ?> <?php echo htmlspecialchars($sub['data']['wpforms[fields][13][last]'] ?? $sub['data']['wpforms[fields][19][last]'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($sub['data']['wpforms[fields][1]'] ?? 'N/A'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
