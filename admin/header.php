<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Admin Panel'); ?> - Berg PC Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #0a1628; color: #e2e8f0; min-height: 100vh; }
        .admin-layout { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #1a2742; border-right: 1px solid #2d3f5e; padding: 20px 0; flex-shrink: 0; }
        .sidebar-logo { padding: 20px 24px; border-bottom: 1px solid #2d3f5e; margin-bottom: 20px; }
        .sidebar-logo h2 { color: #3b82f6; font-size: 20px; font-weight: 700; }
        .sidebar-logo span { color: #64748b; font-size: 12px; }
        .sidebar-nav a { display: flex; align-items: center; gap: 12px; padding: 12px 24px; color: #94a3b8; text-decoration: none; transition: all 0.2s; font-size: 14px; }
        .sidebar-nav a:hover, .sidebar-nav a.active { color: #e2e8f0; background: #1e3a5f; }
        .sidebar-nav a.active { border-right: 3px solid #3b82f6; }
        .sidebar-nav a svg { width: 20px; height: 20px; }
        .main-content { flex: 1; display: flex; flex-direction: column; }
        .top-bar { background: #1a2742; border-bottom: 1px solid #2d3f5e; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; }
        .top-bar h1 { font-size: 20px; font-weight: 600; }
        .top-bar-right { display: flex; align-items: center; gap: 16px; }
        .top-bar-user { color: #94a3b8; font-size: 14px; }
        .btn-logout { background: transparent; border: 1px solid #3b82f6; color: #3b82f6; padding: 6px 16px; border-radius: 6px; cursor: pointer; font-size: 13px; text-decoration: none; transition: all 0.2s; }
        .btn-logout:hover { background: #3b82f6; color: white; }
        .content-area { padding: 24px; flex: 1; }
        .card { background: #1a2742; border: 1px solid #2d3f5e; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .card-header h2 { font-size: 18px; font-weight: 600; }
        .btn { padding: 8px 16px; border-radius: 8px; border: none; cursor: pointer; font-size: 14px; transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-primary:hover { background: #2563eb; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
        .btn-secondary { background: #374151; color: #e2e8f0; }
        .btn-secondary:hover { background: #4b5563; }
        .btn-sm { padding: 6px 12px; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 16px; text-align: left; border-bottom: 1px solid #2d3f5e; font-size: 14px; }
        th { color: #94a3b8; font-weight: 500; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; }
        tr:hover { background: #1e3a5f; }
        input[type="text"], input[type="email"], input[type="password"], input[type="search"],
        select, textarea { background: #0f1d35; border: 1px solid #2d3f5e; color: #e2e8f0; padding: 10px 14px; border-radius: 8px; font-size: 14px; width: 100%; transition: border-color 0.2s; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #3b82f6; }
        textarea { min-height: 120px; resize: vertical; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 6px; color: #94a3b8; font-size: 14px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; }
        .badge-blue { background: #1e3a5f; color: #60a5fa; }
        .badge-green { background: #064e3b; color: #34d399; }
        .badge-red { background: #7f1d1d; color: #fca5a5; }
        .pagination { display: flex; gap: 8px; margin-top: 20px; justify-content: center; }
        .pagination a, .pagination span { padding: 8px 14px; border-radius: 6px; font-size: 14px; text-decoration: none; }
        .pagination a { background: #1a2742; color: #94a3b8; border: 1px solid #2d3f5e; }
        .pagination a:hover { background: #1e3a5f; color: #e2e8f0; }
        .pagination .active { background: #3b82f6; color: white; border: 1px solid #3b82f6; }
        .pagination .disabled { background: #0f1d35; color: #4b5563; border: 1px solid #1e293b; cursor: not-allowed; }
        .checkbox-cell { width: 40px; }
        .checkbox-cell input[type="checkbox"] { width: 16px; height: 16px; accent-color: #3b82f6; }
        .toolbar { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        .toolbar input, .toolbar select { width: auto; min-width: 200px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: #1a2742; border: 1px solid #2d3f5e; border-radius: 12px; padding: 20px; }
        .stat-card .stat-value { font-size: 32px; font-weight: 700; color: #3b82f6; }
        .stat-card .stat-label { color: #94a3b8; font-size: 14px; margin-top: 4px; }
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 1000; justify-content: center; align-items: center; }
        .modal-overlay.active { display: flex; }
        .modal { background: #1a2742; border: 1px solid #2d3f5e; border-radius: 12px; padding: 24px; max-width: 500px; width: 90%; }
        .modal h3 { margin-bottom: 16px; font-size: 18px; }
        .modal-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px; }
        .detail-row { display: flex; border-bottom: 1px solid #2d3f5e; padding: 10px 0; }
        .detail-label { width: 180px; color: #94a3b8; font-size: 14px; flex-shrink: 0; }
        .detail-value { flex: 1; font-size: 14px; word-break: break-word; }
        .empty-state { text-align: center; padding: 40px; color: #64748b; }
        .empty-state svg { width: 48px; height: 48px; margin-bottom: 16px; }
        .img-thumbnail { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
        @media (max-width: 768px) {
            .admin-layout { flex-direction: column; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #2d3f5e; }
            .sidebar-nav { display: flex; overflow-x: auto; }
            .sidebar-nav a { white-space: nowrap; }
        }
    </style>
<?php if (!isset($_SESSION['csrf_token'])) { generateCsrfToken(); } ?>
<meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form[method="POST"], form[method="post"]').forEach(function(form) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'csrf_token';
        input.value = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
        form.appendChild(input);
    });
});
</script>
</head>
<body>
<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <h2>Berg PC</h2>
            <span>Admin Panel</span>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>
            <a href="forms.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'forms.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                Forms
            </a>
            <a href="lawyers.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'lawyers.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Lawyers
            </a>
        </nav>
    </aside>
    <div class="main-content">
        <div class="top-bar">
            <h1><?php echo htmlspecialchars($pageTitle ?? 'Dashboard'); ?></h1>
            <div class="top-bar-right">
                <span class="top-bar-user"><?php echo htmlspecialchars($_SESSION['admin_user'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
        <div class="content-area">
