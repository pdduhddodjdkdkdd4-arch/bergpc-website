<?php
$pageTitle = 'Form Submissions';
require_once __DIR__ . '/header.php';

// 使用新表进行统计
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT COUNT(*) FROM form_submissions_new");
    $total = $stmt->fetchColumn();
} catch(Throwable $e) {
    $total = 0;
}

?>

<div class="card">
    <div class="card-header">
        <h2>Form Submissions (<?php echo $total; ?>)</h2>
        <div class="toolbar">
            <div class="toolbar-group">
                <button onclick="refreshPage()" class="btn btn-secondary btn-sm" title="Refresh page (Ctrl+R)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                    <span class="btn-text">Refresh</span>
                </button>
                <button onclick="toggleColumnsPanel()" class="btn btn-secondary btn-sm" id="columnsBtn" title="Toggle columns panel">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    <span class="btn-text">Columns</span>
                </button>
                <button onclick="resetColumns()" class="btn btn-secondary btn-sm" title="Reset to default columns">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path></svg>
                    <span class="btn-text">Reset</span>
                </button>
                <div style="position: relative; display: inline-block;">
                    <button onclick="toggleConfigMenu()" class="btn btn-secondary btn-sm" title="Configuration options">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        <span class="btn-text">Config</span>
                    </button>
                    <div id="configMenu" style="display: none; position: absolute; top: 100%; right: 0; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 6px; box-shadow: var(--shadow-lg); z-index: 1000; min-width: 200px; margin-top: 4px;">
                        <div style="padding: 12px 16px; border-bottom: 1px solid var(--border-color); font-weight: 600; color: var(--text-primary); font-size: 14px;">Column Configuration</div>
                        <a href="#" onclick="exportColumnConfig(); toggleConfigMenu(); return false;" style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; color: var(--text-primary); text-decoration: none; font-size: 14px; transition: background-color var(--transition-normal);">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            Export Config
                        </a>
                        <a href="#" onclick="importColumnConfig(); toggleConfigMenu(); return false;" style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; color: var(--text-primary); text-decoration: none; font-size: 14px; transition: background-color var(--transition-normal);">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            Import Config
                        </a>
                    </div>
                </div>
            </div>
            <div class="toolbar-divider"></div>
            <div class="toolbar-group">
                <button onclick="downloadAll()" class="btn btn-primary btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    <span class="btn-text">Download All CSV</span>
                </button>
                <button onclick="downloadSelected()" class="btn btn-secondary btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    <span class="btn-text">Download Selected</span>
                </button>
                <button onclick="deleteSelected()" class="btn btn-danger btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    <span class="btn-text">Delete Selected</span>
                </button>
            </div>
        </div>
    </div>

    <div class="toolbar" style="margin-bottom: 20px;">
        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; width: 100%;">
            <div style="flex: 1; min-width: 250px;">
                <input type="text" id="searchInput" placeholder="Search submissions..." style="width: 100%;">
            </div>
            <div style="min-width: 200px;">
                <select id="formFilterSelect" style="width: 100%;">
                    <option value="">All Forms</option>
                    <?php foreach (FORM_CONFIGS as $id => $config): ?>
                    <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($config['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button onclick="applyFilters()" class="btn btn-primary btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                Filter
            </button>
            <button onclick="clearFilters()" class="btn btn-secondary btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                Clear
            </button>
        </div>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <div class="success-message" role="alert">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        Submissions deleted successfully.
    </div>
    <?php endif; ?>

    <?php if (!empty($message)): ?>
    <div class="error-message" role="alert">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <!-- Always load AG Grid and use API to fetch data -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@31.0.0/styles/ag-grid.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@31.0.0/styles/ag-theme-alpine.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-enterprise@31.0.0/styles/ag-grid-enterprise.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@31.0.0/styles/ag-theme-alpine-dark.css" />
    
    <style>
    :root {
        --primary-color: #3b82f6;
        --primary-hover: #2563eb;
        --danger-color: #ef4444;
        --danger-hover: #dc2626;
        --secondary-color: #374151;
        --secondary-hover: #4b5563;
        --bg-primary: #1a2742;
        --bg-secondary: #0f1d35;
        --bg-tertiary: #0a1628;
        --border-color: #2d3f5e;
        --text-primary: #e2e8f0;
        --text-secondary: #94a3b8;
        --text-muted: #64748b;
        --success-bg: #064e3b;
        --success-color: #34d399;
        --error-bg: #7f1d1d;
        --error-color: #fca5a5;
        --warning-bg: #78350f;
        --warning-color: #fbbf24;
        --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.2);
        --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.25);
        --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.3);
        --shadow-xl: 0 20px 25px rgba(0, 0, 0, 0.4);
        --transition-fast: 0.15s ease;
        --transition-normal: 0.2s ease;
        --transition-slow: 0.3s ease;
    }

    .success-message,
    .error-message,
    .warning-message,
    .info-message {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 14px;
        font-weight: 500;
        animation: slideInFromTop 0.3s ease-out;
        border-left: 4px solid;
    }

    .success-message {
        background: var(--success-bg);
        color: var(--success-color);
        border-left-color: var(--success-color);
    }

    .error-message {
        background: var(--error-bg);
        color: var(--error-color);
        border-left-color: var(--danger-color);
    }

    .warning-message {
        background: var(--warning-bg);
        color: var(--warning-color);
        border-left-color: var(--warning-color);
    }

    .info-message {
        background: #1e3a5f;
        color: #60a5fa;
        border-left-color: #60a5fa;
    }

    @keyframes slideInFromTop {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .btn-sm svg {
        vertical-align: middle;
    }
    
    #columnsBtn.active {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    
    #configMenu a:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
    
    .toolbar button[title] {
        position: relative;
    }
    
    .toolbar button[title]:hover::after {
        content: attr(title);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background-color: var(--bg-tertiary);
        color: white;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 1001;
        margin-bottom: 6px;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-color);
    }

    .ag-theme-alpine {
        --ag-background-color: var(--bg-primary);
        --ag-header-background-color: var(--bg-secondary);
        --ag-odd-row-background-color: var(--bg-primary);
        --ag-row-hover-color: rgba(59, 130, 246, 0.1);
        --ag-selected-row-background-color: rgba(59, 130, 246, 0.15);
        --ag-border-color: var(--border-color);
        --ag-header-foreground-color: var(--text-secondary);
        --ag-foreground-color: var(--text-primary);
        --ag-secondary-foreground-color: var(--text-secondary);
        --ag-font-family: system-ui, -apple-system, sans-serif;
        --ag-font-size: 13px;
        --ag-row-border-color: var(--border-color);
        --ag-cell-horizontal-border: solid var(--border-color);
        --ag-input-focus-border-color: var(--primary-color);
        --ag-range-selection-border-color: var(--primary-color);
        --ag-checkbox-checked-color: var(--primary-color);
        --ag-input-border-color: var(--border-color);
        --ag-input-background-color: var(--bg-secondary);
        --ag-toggle-button-switch-background-color: var(--primary-color);
        --ag-value-change-value-highlight-background-color: rgba(59, 130, 246, 0.25);
        --ag-chip-background-color: var(--bg-secondary);
        --ag-subheader-background-color: var(--bg-tertiary);
        --ag-subheader-toolbar-background-color: var(--bg-secondary);
        --ag-control-panel-background-color: var(--bg-primary);
        --ag-side-button-selected-background-color: rgba(59, 130, 246, 0.2);
        --ag-modal-overlay-background-color: rgba(0, 0, 0, 0.7);
        --ag-tooltip-background-color: #ffffff;
        --ag-tooltip-foreground-color: #000000;
        --ag-tooltip-border: 1px solid #ccc;
        --ag-tooltip-border-radius: 4px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid var(--border-color);
    }

    .ag-theme-alpine .ag-cell {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .ag-theme-alpine .ag-header-cell-label {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Tooltip 样式 */
    .ag-theme-alpine .ag-tooltip {
        background-color: #ffffff !important;
        color: #000000 !important;
        border: 1px solid #cccccc !important;
        border-radius: 4px !important;
        padding: 8px 12px !important;
        font-size: 14px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15) !important;
    }
    
    .ag-theme-alpine .ag-root-wrapper {
        border: none;
    }

    .ag-theme-alpine .ag-header {
        border-bottom: 2px solid var(--primary-color);
    }

    .ag-theme-alpine .ag-header-cell {
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .ag-theme-alpine .ag-side-bar {
        border-left: 1px solid var(--border-color);
        background: var(--bg-primary);
    }
    
    .ag-theme-alpine .ag-tool-panel-wrapper {
        width: 250px;
    }

    .ag-theme-alpine .ag-row {
        border-bottom: 1px solid var(--border-color);
        transition: background-color var(--transition-fast);
    }

    .ag-theme-alpine .ag-cell {
        display: flex;
        align-items: center;
        padding: 12px 16px;
    }

    .cell-renderer-container {
        display: flex;
        align-items: center;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }
    
    .cell-text-truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        flex: 1;
        min-width: 0;
    }
    
    .badge-yes {
        background-color: var(--success-bg);
        color: var(--success-color);
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    
    .badge-no {
        background-color: var(--error-bg);
        color: var(--error-color);
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    
    .badge-yes::before,
    .badge-no::before {
        font-weight: bold;
    }
    
    .badge-yes::before {
        content: '✓';
    }
    
    .badge-no::before {
        content: '✗';
    }
    
    .cell-empty-value {
        color: var(--text-muted);
        font-style: italic;
    }

    .detail-modal {
        background: var(--bg-primary);
        border-radius: 12px;
        max-width: 900px;
        width: 95%;
        max-height: 85vh;
        display: flex;
        flex-direction: column;
        box-shadow: var(--shadow-xl);
        animation: modalSlideIn 0.3s ease-out;
        border: 1px solid var(--border-color);
    }
    
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-20px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    .detail-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        background: var(--bg-secondary);
        border-radius: 12px 12px 0 0;
    }
    
    .detail-modal-header h3 {
        margin: 0;
        color: var(--text-primary);
        font-size: 18px;
        font-weight: 600;
    }
    
    .detail-close-btn {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--border-color);
        cursor: pointer;
        padding: 8px;
        border-radius: 6px;
        color: var(--text-secondary);
        transition: all var(--transition-normal);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .detail-close-btn:hover {
        background: rgba(255, 255, 255, 0.1);
        color: var(--text-primary);
        border-color: var(--primary-color);
    }
    
    .detail-modal-content {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
    }
    
    .detail-modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border-color);
        background: var(--bg-secondary);
        border-radius: 0 0 12px 12px;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
    
    .detail-section {
        margin-bottom: 24px;
    }
    
    .detail-section:last-child {
        margin-bottom: 0;
    }
    
    .detail-section-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid var(--primary-color);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .detail-section-title svg {
        color: var(--primary-color);
    }
    
    .detail-grid {
        display: grid;
        grid-template-columns: 180px 1fr;
        gap: 12px 16px;
        align-items: start;
    }
    
    .detail-item {
        display: contents;
    }
    
    .detail-label {
        font-weight: 600;
        color: var(--text-secondary);
        font-size: 14px;
        padding: 10px 0;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .detail-value {
        color: var(--text-primary);
        font-size: 14px;
        padding: 10px 14px;
        background: var(--bg-secondary);
        border-radius: 6px;
        border: 1px solid var(--border-color);
        word-break: break-word;
        line-height: 1.6;
        min-height: 44px;
        display: flex;
        align-items: center;
    }
    
    .detail-value.empty {
        color: var(--text-muted);
        font-style: italic;
        background: var(--bg-tertiary);
    }
    
    .detail-value pre {
        margin: 0;
        white-space: pre-wrap;
        word-break: break-word;
        font-family: inherit;
        width: 100%;
    }
    
    .detail-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    
    .detail-meta-item {
        background: var(--bg-secondary);
        padding: 16px 20px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        transition: all var(--transition-normal);
    }

    .detail-meta-item:hover {
        border-color: var(--primary-color);
        box-shadow: var(--shadow-sm);
    }
    
    .detail-meta-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    
    .detail-meta-value {
        font-size: 15px;
        color: var(--text-primary);
        font-weight: 500;
    }

    .empty-state {
        text-align: center;
        padding: 60px 40px;
        color: var(--text-secondary);
    }

    .empty-state-icon {
        margin-bottom: 24px;
        opacity: 0.5;
    }

    .empty-state h3 {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--text-primary);
    }

    .empty-state p {
        font-size: 14px;
        margin-bottom: 24px;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }

    .empty-state-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .keyboard-hint {
        margin-top: 32px;
        font-size: 12px;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .keyboard-hint kbd {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 4px;
        padding: 3px 8px;
        font-size: 11px;
        font-weight: 600;
        font-family: system-ui, -apple-system, sans-serif;
        color: var(--text-secondary);
    }

    .btn {
        padding: 10px 18px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all var(--transition-normal);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: var(--shadow-sm);
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .btn:active {
        transform: translateY(0);
        box-shadow: var(--shadow-sm);
    }

    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    .btn-primary {
        background: var(--primary-color);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-hover);
    }

    .btn-secondary {
        background: var(--secondary-color);
        color: var(--text-primary);
    }

    .btn-secondary:hover {
        background: var(--secondary-hover);
    }

    .btn-danger {
        background: var(--danger-color);
        color: white;
    }

    .btn-danger:hover {
        background: var(--danger-hover);
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 13px;
    }

    .toolbar {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .toolbar .btn {
        white-space: nowrap;
    }

    .btn-text {
        display: inline;
    }

    @media (max-width: 768px) {
        .btn-text {
            display: none;
        }

        .toolbar .btn {
            justify-content: center;
        }

        .toolbar .toolbar-group {
            width: 100%;
            justify-content: center;
        }

        .toolbar .toolbar-group .btn {
            flex: 1;
            max-width: 150px;
        }
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(10, 22, 40, 0.9);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        backdrop-filter: blur(4px);
    }

    .loading-overlay.active {
        display: flex;
    }

    .spinner {
        width: 48px;
        height: 48px;
        border: 4px solid var(--border-color);
        border-top-color: var(--primary-color);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    .spinner-large {
        width: 64px;
        height: 64px;
        border-width: 5px;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 400px;
    }

    .toast {
        padding: 14px 18px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        font-weight: 500;
        box-shadow: var(--shadow-lg);
        animation: toastSlideIn 0.3s ease-out;
        border-left: 4px solid;
    }

    .toast.toast-success {
        background: var(--success-bg);
        color: var(--success-color);
        border-left-color: var(--success-color);
    }

    .toast.toast-error {
        background: var(--error-bg);
        color: var(--error-color);
        border-left-color: var(--danger-color);
    }

    .toast.toast-warning {
        background: var(--warning-bg);
        color: var(--warning-color);
        border-left-color: var(--warning-color);
    }

    .toast.toast-info {
        background: #1e3a5f;
        color: #60a5fa;
        border-left-color: #60a5fa;
    }

    .toast.fade-out {
        animation: toastSlideOut 0.3s ease-in forwards;
    }

    @keyframes toastSlideIn {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes toastSlideOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }

    .help-tooltip {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .help-tooltip .help-icon {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: var(--text-muted);
        color: var(--bg-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: bold;
        cursor: help;
        transition: all var(--transition-normal);
    }

    .help-tooltip .help-icon:hover {
        background: var(--primary-color);
        transform: scale(1.1);
    }

    .help-tooltip .help-text {
        position: absolute;
        bottom: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%);
        background: var(--bg-tertiary);
        color: var(--text-primary);
        padding: 10px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: normal;
        white-space: nowrap;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-color);
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all var(--transition-normal);
    }

    .help-tooltip:hover .help-text {
        opacity: 1;
        visibility: visible;
    }

    .keyboard-shortcuts-hint {
        position: fixed;
        bottom: 20px;
        left: 20px;
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 12px;
        color: var(--text-secondary);
        box-shadow: var(--shadow-md);
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        z-index: 100;
        opacity: 0;
        transition: opacity var(--transition-normal);
    }

    .keyboard-shortcuts-hint:hover {
        opacity: 1;
    }

    .keyboard-shortcuts-hint kbd {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 11px;
        font-weight: 600;
        font-family: system-ui, -apple-system, sans-serif;
        color: var(--text-primary);
    }

    .responsive-toolbar {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .responsive-toolbar .toolbar-group {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }

    .responsive-toolbar .toolbar-divider {
        width: 1px;
        height: 32px;
        background: var(--border-color);
        margin: 0 4px;
    }

    @media (max-width: 768px) {
        .detail-modal {
            width: 98%;
            max-height: 95vh;
        }
        
        .detail-modal-header {
            padding: 16px;
        }
        
        .detail-modal-content {
            padding: 16px;
        }
        
        .detail-grid {
            grid-template-columns: 1fr;
            gap: 8px;
        }
        
        .detail-label {
            font-size: 12px;
            color: var(--text-muted);
        }
        
        .detail-value {
            font-size: 13px;
        }
        
        .detail-meta {
            grid-template-columns: 1fr;
        }
        
        .detail-section-title {
            font-size: 13px;
        }

        .toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .toolbar button,
        .toolbar a,
        .toolbar .btn {
            width: 100%;
            justify-content: center;
            padding: 12px;
        }

        .toolbar > div,
        .toolbar > form {
            width: 100%;
        }

        .toolbar input,
        .toolbar select {
            width: 100%;
            min-width: unset;
        }

        .toolbar form {
            flex-direction: column;
        }

        .toolbar form > div {
            width: 100%;
            min-width: unset;
        }

        .responsive-toolbar {
            flex-direction: column;
        }

        .responsive-toolbar .toolbar-group {
            width: 100%;
            justify-content: center;
        }

        .responsive-toolbar .toolbar-divider {
            width: 100%;
            height: 1px;
            margin: 8px 0;
        }

        .keyboard-shortcuts-hint {
            display: none;
        }

        .toast-container {
            left: 10px;
            right: 10px;
            max-width: unset;
        }

        .ag-theme-alpine {
            font-size: 12px;
        }

        .ag-theme-alpine .ag-cell {
            padding: 8px 10px;
        }
    }

    @media (max-width: 480px) {
        .empty-state {
            padding: 40px 20px;
        }

        .empty-state h3 {
            font-size: 18px;
        }

        .empty-state p {
            font-size: 13px;
        }

        .btn {
            padding: 8px 14px;
            font-size: 13px;
        }

        .btn-sm {
            padding: 6px 10px;
            font-size: 12px;
        }
    }
    </style>
    
    <div id="myGrid" class="ag-theme-alpine" style="width: 100%; height: 600px; margin-bottom: 20px;"></div>
    
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community@31.0.0/dist/ag-grid-community.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-enterprise@31.0.0/dist/ag-grid-enterprise.min.js"></script>
    
    <script>
    let gridApi;
    
    agGrid.LicenseManager.setLicenseKey('');
    
    const fieldLabelMap = {
        'wpforms[fields][1]': 'Email',
        'wpforms[fields][2]': 'Case Details',
        'wpforms[fields][3]': 'Honeypot',
        'wpforms[fields][4]': 'Additional Field',
        'wpforms[fields][9]': 'Disclaimer',
        'wpforms[fields][13][first]': 'First Name',
        'wpforms[fields][13][last]': 'Last Name',
        'wpforms[fields][17]': 'Company',
        'wpforms[fields][18]': 'Cryptocurrency Type',
        'wpforms[fields][19][first]': 'Amount Lost - First',
        'wpforms[fields][19][last]': 'Amount Lost - Last',
        'wpforms[fields][20]': 'Date of Loss',
        'wpforms[fields][21]': 'Transaction ID',
        'wpforms[fields][22]': 'Additional Information',
        'wpforms[fields][25]': 'Phone',
        'wpforms[fields][26]': 'Address',
        'wpforms[fields][27]': 'Disclaimer Acceptance',
        'wpforms[fields][30]': 'How did you hear about us?'
    };
    
    const yesNoFields = [
        'wpforms[fields][27]',
        'wpforms[fields][9]',
        'disclaimer',
        'acceptance',
        'agree',
        'consent',
        'accept'
    ];
    
    const TEXT_TRUNCATE_LENGTH = 100;
    
    function isYesNoField(key) {
        const normalizedKey = key.toLowerCase().replace(/field_/g, '');
        return yesNoFields.some(field => normalizedKey.includes(field.toLowerCase()));
    }
    
    function formatValue(value) {
        if (value === null || value === undefined || value === '') {
            return '-';
        }
        if (typeof value === 'string' && value.trim() === '') {
            return '-';
        }
        return value;
    }
    
    function formatDate(value) {
        if (!value) return '-';
        try {
            const date = new Date(value);
            if (isNaN(date.getTime())) return value;
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            return value;
        }
    }
    
    function truncateText(text, maxLength) {
        if (!text || text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }
    
    function genericCellRenderer(params) {
        const value = params.value;
        const field = params.colDef.field || '';
        const formattedValue = formatValue(value);
        
        if (formattedValue === '-') {
            return '<span class="cell-empty-value">-</span>';
        }
        
        if (isYesNoField(field)) {
            const normalizedValue = String(value).toLowerCase().trim();
            if (normalizedValue === 'yes' || normalizedValue === '1' || normalizedValue === 'true') {
                return '<span class="badge-yes">Yes</span>';
            } else if (normalizedValue === 'no' || normalizedValue === '0' || normalizedValue === 'false') {
                return '<span class="badge-no">No</span>';
            }
        }
        
        const displayText = truncateText(String(formattedValue), TEXT_TRUNCATE_LENGTH);
        const tooltipText = String(formattedValue).replace(/"/g, '&quot;');
        
        if (String(formattedValue).length > TEXT_TRUNCATE_LENGTH) {
            return `<div class="cell-renderer-container" title="${tooltipText}">
                <span class="cell-text-truncate">${displayText}</span>
            </div>`;
        }
        
        return `<div class="cell-renderer-container">${displayText}</div>`;
    }
    
    function genericValueFormatter(params) {
        return formatValue(params.value);
    }
    
    function dateValueFormatter(params) {
        return formatDate(params.value);
    }
    
    function getFriendlyLabel(key) {
        if (fieldLabelMap[key]) return fieldLabelMap[key];
        if (key.startsWith('wpforms[fields][')) {
            const fieldNames = {
                '1': 'Email',
                '2': 'Case Details',
                '3': 'Honeypot',
                '4': 'Additional Field',
                '9': 'Disclaimer',
                '13': 'Name',
                '17': 'Company',
                '18': 'Cryptocurrency Type',
                '19': 'Amount Lost',
                '20': 'Date of Loss',
                '21': 'Transaction ID',
                '22': 'Additional Information',
                '25': 'Phone',
                '26': 'Address',
                '27': 'Disclaimer Acceptance',
                '30': 'How did you hear about us?'
            };
            const match = key.match(/wpforms\[fields\]\[(\d+)\]/);
            if (match && fieldNames[match[1]]) {
                let label = fieldNames[match[1]];
                const subMatch = key.match(/wpforms\[fields\]\[\d+\]\[(\w+)\]/);
                if (subMatch) {
                    label += ' (' + subMatch[1].charAt(0).toUpperCase() + subMatch[1].slice(1) + ')';
                }
                return label;
            }
        }
        return key.replace(/wpforms\[fields\]\[/g, 'Field ').replace(/\]/g, '');
    }
    
    function getDisplayName(data) {
        if (!data || typeof data !== 'object') return 'N/A';
        let first = '', last = '';
        if (data['wpforms[fields][13][first]']) first = data['wpforms[fields][13][first]'];
        else if (data['wpforms[fields][19][first]']) first = data['wpforms[fields][19][first]'];
        if (data['wpforms[fields][13][last]']) last = data['wpforms[fields][13][last]'];
        else if (data['wpforms[fields][19][last]']) last = data['wpforms[fields][19][last]'];
        let name = (first + ' ' + last).trim();
        if (!name) {
            for (let key in data) {
                if (key.toLowerCase().includes('name') && data[key]) {
                    name = data[key];
                    break;
                }
            }
        }
        return name || 'N/A';
    }
    
    function getDisplayEmail(data) {
        if (!data || typeof data !== 'object') return 'N/A';
        if (data['wpforms[fields][1]']) return data['wpforms[fields][1]'];
        for (let key in data) {
            if (key.toLowerCase().includes('email') && data[key]) return data[key];
        }
        return 'N/A';
    }
    
    function prepareGridData() {
        const submissions = <?php echo $submissionsJson; ?>;
        return submissions.map(sub => {
            const row = {
                submission_id: sub.submission_id,
                form_name: sub.form_name,
                submitted_at: sub.submitted_at,
                ip: sub.ip || '',
                user_agent: sub.user_agent || '',
                display_name: getDisplayName(sub.data),
                display_email: getDisplayEmail(sub.data)
            };
            if (sub.data && typeof sub.data === 'object') {
                for (let key in sub.data) {
                    const label = getFriendlyLabel(key);
                    let value = sub.data[key];
                    if (Array.isArray(value)) value = value.join(', ');
                    row['field_' + key] = value;
                }
            }
            return row;
        });
    }
    
    function getColumnDefs(sampleData) {
        const allKeys = new Set();
        sampleData.forEach(row => {
            Object.keys(row).forEach(key => {
                if (key.startsWith('field_') && !['field_wpforms[fields][3]'].includes(key)) {
                    allKeys.add(key);
                }
            });
        });
        
        const baseColumns = [
            {
                headerCheckboxSelection: true,
                checkboxSelection: true,
                width: 50,
                pinned: 'left',
                lockPosition: true,
                suppressMenu: true
            },
            {
                field: 'id',
                headerName: 'ID',
                width: 80,
                sortable: true,
                filter: 'agNumberColumnFilter'
            },
            {
                field: 'form_name',
                headerName: 'Form',
                width: 150,
                sortable: true,
                filter: 'agTextColumnFilter'
            },
            {
                field: 'submitted_at',
                headerName: 'Date',
                width: 180,
                sortable: true,
                filter: 'agDateColumnFilter',
                valueFormatter: dateValueFormatter,
                cellRenderer: genericCellRenderer
            },
            {
                field: 'display_name',
                headerName: 'Name',
                width: 150,
                sortable: true,
                filter: 'agTextColumnFilter',
                tooltipField: 'display_name',
                cellRenderer: genericCellRenderer
            },
            {
                field: 'display_email',
                headerName: 'Email',
                width: 200,
                sortable: true,
                filter: 'agTextColumnFilter',
                tooltipField: 'display_email',
                cellRenderer: genericCellRenderer
            }
        ];
        
        const fieldColumns = Array.from(allKeys).map(key => ({
            field: key,
            headerName: getFriendlyLabel(key.replace('field_', '')),
            width: 180,
            sortable: true,
            filter: 'agTextColumnFilter',
            tooltipField: key,
            cellRenderer: genericCellRenderer,
            valueFormatter: genericValueFormatter,
            autoHeight: false
        }));
        
        return [...baseColumns, ...fieldColumns, {
            headerName: 'Actions',
            width: 150,
            pinned: 'right',
            cellRenderer: function(params) {
                return '<button class="btn btn-secondary btn-sm" style="margin-right: 4px;" onclick="viewDetailFromGrid(' + params.data.id + ')">View</button>' +
                       '<button class="btn btn-danger btn-sm" onclick="deleteSingleFromGrid(\'' + params.data.id + '\')">Delete</button>';
            },
            suppressMenu: true,
            sortable: false,
            filter: false
        }];
    }
    
    function createGridOptions() {
        return {
            defaultColDef: {
                resizable: true,
                sortable: true,
                filter: true,
                floatingFilter: true,
                suppressMenu: false,
                cellRenderer: genericCellRenderer,
                valueFormatter: genericValueFormatter
            },
            rowSelection: 'multiple',
            suppressRowClickSelection: true,
            animateRows: true,
            pagination: true,
            paginationPageSize: 20,
            domLayout: 'normal',
            autoHeight: false,
            suppressCellFocus: true,
            enableCellTextSelection: true,
            tooltipShowDelay: 200,
            tooltipHideDelay: 10000,
            sideBar: {
                toolPanels: [
                    {
                        id: 'columns',
                        labelDefault: 'Columns',
                        labelKey: 'columns',
                        iconKey: 'columns',
                        toolPanel: 'agColumnsToolPanel',
                        toolPanelParams: {
                            suppressRowGroups: true,
                            suppressValues: true,
                            suppressPivots: true,
                            suppressPivotMode: true,
                            suppressSideButtons: true,
                            suppressColumnFilter: false,
                            suppressColumnSelectAll: false,
                            suppressColumnExpandAll: false
                        }
                    },
                    {
                        id: 'filters',
                        labelDefault: 'Filters',
                        labelKey: 'filters',
                        iconKey: 'filter',
                        toolPanel: 'agFiltersToolPanel'
                    }
                ],
                defaultToolPanel: ''
            },
            onGridReady: function(params) {
                gridApi = params.api;
                console.log('AG Grid is ready');
                loadColumnState();
                loadDataFromAPI();
            },
            onColumnMoved: function(params) {
                saveColumnState();
            },
            onColumnResized: function(params) {
                if (params.finished) {
                    saveColumnState();
                }
            },
            onColumnVisible: function(params) {
                saveColumnState();
            },
            onColumnPinned: function(params) {
                saveColumnState();
            },
            onSortChanged: function(params) {
                saveColumnState();
            },
            onFilterChanged: function(params) {
                saveColumnState();
            },
            onSelectionChanged: function(params) {
                updateSelectAllCheckbox();
            },
            onRowDataUpdated: function(params) {
                console.log('Row data updated');
            }
        };
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const gridDiv = document.querySelector('#myGrid');
        if (gridDiv) {
            const options = createGridOptions();
            gridApi = agGrid.createGrid(gridDiv, options);
        }
    });
    
    async function loadDataFromAPI() {
        try {
            showLoading('Loading data...');
            const response = await fetch('forms-api.php?action=list');
            const result = await response.json();
            
            if (result.success && result.data) {
                const columnDefs = getColumnDefs(result.data);
                gridApi.setGridOption('columnDefs', columnDefs);
                gridApi.setGridOption('rowData', result.data);
                loadColumnState();
            } else {
                showToast('Failed to load data', 'error');
            }
        } catch (error) {
            console.error('Error loading data:', error);
            showToast('Error loading data', 'error');
        } finally {
            hideLoading();
        }
    }
    
    function viewDetailFromGrid(id) {
        const allRows = [];
        gridApi.forEachNode(node => allRows.push(node.data));
        const sub = allRows.find(row => row.id == id);
        if (sub) {
            viewDetailFromAPI(id);
        }
    }
    
    async function viewDetailFromAPI(id) {
        try {
            showLoading('Loading details...');
            const response = await fetch(`forms-api.php?action=detail&id=${id}`);
            const result = await response.json();
            
            if (result.success && result.data) {
                const submission = result.data.submission;
                submission.data = result.data.fields;
                viewDetail(submission);
            } else {
                showToast('Failed to load details', 'error');
            }
        } catch (error) {
            console.error('Error loading details:', error);
            showToast('Error loading details', 'error');
        } finally {
            hideLoading();
        }
    }
    
    function deleteSingleFromGrid(id) {
        deleteSingle(id);
    }
    
    function loadGridData() {
        console.log('Loading grid data...');
        loadDataFromAPI();
    }
    
    function getSelectedIdsFromGrid() {
        const selectedRows = gridApi ? gridApi.getSelectedRows() : [];
        return selectedRows.map(row => row.id); // 使用新表的 id 字段
    }

    function applyFilters() {
        const searchText = document.getElementById('searchInput')?.value || '';
        const formId = document.getElementById('formFilterSelect')?.value || '';
        
        if (!gridApi) return;
        
        // 重置所有筛选
        gridApi.setFilterModel({});
        
        const filterModel = {};
        
        // 如果有搜索文本，应用到相关字段
        if (searchText) {
            filterModel['form_name'] = { type: 'contains', filter: searchText };
        }
        
        // 如果有表单 ID，应用筛选
        if (formId) {
            filterModel['form_id'] = { type: 'equals', filter: formId };
        }
        
        gridApi.setFilterModel(filterModel);
        showToast('Filters applied', 'success');
    }

    function clearFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('formFilterSelect').value = '';
        if (gridApi) {
            gridApi.setFilterModel({});
            showToast('Filters cleared', 'success');
        }
    }
    </script>
</div>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner spinner-large"></div>
</div>

<div class="toast-container" id="toastContainer"></div>

<div class="keyboard-shortcuts-hint" id="keyboardShortcutsHint">
    <span><kbd>Ctrl</kbd>+<kbd>R</kbd> Refresh</span>
    <span><kbd>Ctrl</kbd>+<kbd>F</kbd> Search</span>
    <span><kbd>Esc</kbd> Close modal</span>
</div>

<div class="modal-overlay" id="detailModal">
    <div class="detail-modal">
        <div class="detail-modal-header">
            <h3>Submission Details</h3>
            <button class="detail-close-btn" onclick="closeModal('detailModal')" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div id="detailContent" class="detail-modal-content"></div>
        <div class="detail-modal-footer">
            <button onclick="closeModal('detailModal')" class="btn btn-secondary">Close</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <h3>Confirm Delete</h3>
        <p id="deleteMessage">Are you sure you want to delete the selected submission(s)? This action cannot be undone.</p>
        <div class="modal-actions">
            <button type="button" onclick="closeModal('deleteModal')" class="btn btn-secondary">Cancel</button>
            <button type="button" onclick="confirmDelete()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>

<form method="POST" id="csvForm">
    <input type="hidden" name="action" value="download_csv">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="ids" id="csvIds">
    <input type="hidden" name="csv_form_id" id="csvFormId" value="">
</form>

<script>
function refreshPage() { 
    location.reload(); 
}

function toggleSelectAll(el) {
    if (gridApi) {
        if (el.checked) {
            gridApi.selectAll();
        } else {
            gridApi.deselectAll();
        }
    } else {
        document.querySelectorAll('.sub-checkbox').forEach(cb => cb.checked = el.checked);
    }
}

function getSelectedIds() {
    if (gridApi) {
        return getSelectedIdsFromGrid();
    } else {
        return Array.from(document.querySelectorAll('.sub-checkbox:checked')).map(cb => cb.value);
    }
}

function updateSelectAllCheckbox() {
    if (gridApi) {
        const selectAllCheckbox = document.getElementById('selectAll');
        if (selectAllCheckbox) {
            const selectedRows = gridApi.getSelectedRows();
            const totalRows = gridApi.getDisplayedRowCount();
            selectAllCheckbox.checked = selectedRows.length === totalRows && totalRows > 0;
            selectAllCheckbox.indeterminate = selectedRows.length > 0 && selectedRows.length < totalRows;
        }
    }
}

function viewDetail(sub) {
    let html = '<div class="detail-meta">';
    html += '<div class="detail-meta-item"><div class="detail-meta-label">ID</div><div class="detail-meta-value">#' + sub.submission_id + '</div></div>';
    html += '<div class="detail-meta-item"><div class="detail-meta-label">Form</div><div class="detail-meta-value">' + escapeHtml(sub.form_name || '-') + '</div></div>';
    html += '<div class="detail-meta-item"><div class="detail-meta-label">Submitted</div><div class="detail-meta-value">' + formatDate(sub.submitted_at) + '</div></div>';
    html += '<div class="detail-meta-item"><div class="detail-meta-label">IP Address</div><div class="detail-meta-value">' + escapeHtml(sub.ip || '-') + '</div></div>';
    html += '</div>';
    
    const basicInfo = [];
    const contactInfo = [];
    const formData = [];
    
    if (sub.data && typeof sub.data === 'object') {
        const sortedKeys = Object.keys(sub.data).sort();
        
        for (let key of sortedKeys) {
            // key 可能是 field_First Name 或者其他格式
            let label = key;
            let value = sub.data[key];
            
            if (Array.isArray(value)) {
                value = value.join(', ');
            }
            
            const fieldInfo = { key, label, value };
            
            const keyLower = key.toLowerCase();
            if (keyLower.includes('email') || keyLower.includes('phone') || keyLower.includes('name') || keyLower.includes('address') || keyLower.includes('company')) {
                contactInfo.push(fieldInfo);
            } else {
                formData.push(fieldInfo);
            }
        }
    }
    
    if (basicInfo.length > 0) {
        html += '<div class="detail-section">';
        html += '<div class="detail-section-title">';
        html += '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
        html += 'Basic Information</div>';
        html += '<div class="detail-grid">';
        for (let item of basicInfo) {
            html += renderDetailItem(item.label, item.value, item.key);
        }
        html += '</div></div>';
    }
    
    if (contactInfo.length > 0) {
        html += '<div class="detail-section">';
        html += '<div class="detail-section-title">';
        html += '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>';
        html += 'Contact Information</div>';
        html += '<div class="detail-grid">';
        for (let item of contactInfo) {
            html += renderDetailItem(item.label, item.value, item.key);
        }
        html += '</div></div>';
    }
    
    if (formData.length > 0) {
        html += '<div class="detail-section">';
        html += '<div class="detail-section-title">';
        html += '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';
        html += 'Form Data</div>';
        html += '<div class="detail-grid">';
        for (let item of formData) {
            html += renderDetailItem(item.label, item.value, item.key);
        }
        html += '</div></div>';
    }
    
    document.getElementById('detailContent').innerHTML = html;
    document.getElementById('detailModal').classList.add('active');
}

function renderDetailItem(label, value, key) {
    let formattedValue = formatValue(value);
    let valueClass = '';
    let valueHtml = '';
    
    if (formattedValue === '-') {
        valueClass = 'empty';
        valueHtml = formattedValue;
    } else if (isYesNoField(label)) { // 使用 label 而不是 key
        const normalizedValue = String(value).toLowerCase().trim();
        if (normalizedValue === 'yes' || normalizedValue === '1' || normalizedValue === 'true') {
            valueHtml = '<span class="badge-yes">Yes</span>';
        } else if (normalizedValue === 'no' || normalizedValue === '0' || normalizedValue === 'false') {
            valueHtml = '<span class="badge-no">No</span>';
        } else {
            valueHtml = escapeHtml(formattedValue);
        }
    } else if (String(formattedValue).length > 100) {
        valueHtml = '<pre>' + escapeHtml(formattedValue) + '</pre>';
    } else {
        valueHtml = escapeHtml(formattedValue);
    }
    
    return '<div class="detail-item"><div class="detail-label">' + escapeHtml(label) + '</div><div class="detail-value ' + valueClass + '">' + valueHtml + '</div></div>';
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
}



function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

const COLUMN_STATE_KEY = 'forms_grid_column_state';

function saveColumnState() {
    if (!gridApi) return;
    try {
        const columnState = gridApi.getColumnState();
        const sidebarState = gridApi.getSideBar();
        const stateToSave = {
            columnState: columnState,
            sidebarOpen: sidebarState && sidebarState.open
        };
        localStorage.setItem(COLUMN_STATE_KEY, JSON.stringify(stateToSave));
        console.log('Column state saved to localStorage');
    } catch (e) {
        console.error('Error saving column state:', e);
    }
}

function loadColumnState() {
    if (!gridApi) return;
    try {
        const savedState = localStorage.getItem(COLUMN_STATE_KEY);
        if (savedState) {
            const parsedState = JSON.parse(savedState);
            
            if (parsedState.columnState && parsedState.columnState.length > 0) {
                gridApi.applyColumnState({
                    state: parsedState.columnState,
                    applyOrder: true
                });
                console.log('Column state loaded from localStorage');
            }
            
            if (parsedState.sidebarOpen) {
                const columnsBtn = document.getElementById('columnsBtn');
                if (columnsBtn) {
                    columnsBtn.classList.add('active');
                }
            }
        }
    } catch (e) {
        console.error('Error loading column state:', e);
    }
}

function toggleColumnsPanel() {
    if (!gridApi) {
        console.error('Grid API not available');
        return;
    }
    
    const sideBar = gridApi.getSideBar();
    const columnsBtn = document.getElementById('columnsBtn');
    
    if (!sideBar) {
        console.error('Sidebar not configured');
        return;
    }
    
    if (sideBar.open) {
        gridApi.closeToolPanel();
        if (columnsBtn) {
            columnsBtn.classList.remove('active');
        }
    } else {
        gridApi.openToolPanel('columns');
        if (columnsBtn) {
            columnsBtn.classList.add('active');
        }
    }
    
    saveColumnState();
}

function resetColumns() {
    if (!gridApi) return;
    
    if (confirm('Reset all column settings to default?')) {
        localStorage.removeItem(COLUMN_STATE_KEY);
        gridApi.resetColumnState();
        gridApi.closeToolPanel();
        
        const columnsBtn = document.getElementById('columnsBtn');
        if (columnsBtn) {
            columnsBtn.classList.remove('active');
        }
        
        console.log('Column state reset to default');
    }
}

function exportColumnConfig() {
    if (!gridApi) return;
    
    try {
        const savedState = localStorage.getItem(COLUMN_STATE_KEY);
        if (savedState) {
            const config = JSON.parse(savedState);
            const blob = new Blob([JSON.stringify(config, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'ag-grid-column-config-' + new Date().toISOString().split('T')[0] + '.json';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        } else {
            alert('No column configuration to export. Please configure your columns first.');
        }
    } catch (e) {
        console.error('Error exporting column config:', e);
        alert('Error exporting configuration: ' + e.message);
    }
}

function importColumnConfig() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json';
    
    input.onchange = function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const config = JSON.parse(e.target.result);
                if (config.columnState && Array.isArray(config.columnState)) {
                    localStorage.setItem(COLUMN_STATE_KEY, JSON.stringify(config));
                    loadColumnState();
                    alert('Column configuration imported successfully!');
                } else {
                    alert('Invalid configuration file format.');
                }
            } catch (err) {
                alert('Error reading configuration file: ' + err.message);
            }
        };
        reader.readAsText(file);
    };
    
    input.click();
}

function toggleConfigMenu() {
    const menu = document.getElementById('configMenu');
    if (menu) {
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    }
}

document.addEventListener('click', function(e) {
    const menu = document.getElementById('configMenu');
    const button = e.target.closest('[onclick*="toggleConfigMenu"]');
    if (menu && !button && menu.style.display === 'block') {
        menu.style.display = 'none';
    }
});

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});

function showLoading(message = 'Loading...') {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.classList.add('active');
    }
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.classList.remove('active');
    }
}

function showToast(message, type = 'info', duration = 5000) {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.setAttribute('role', 'alert');
    
    const icons = {
        success: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
        error: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
        warning: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
        info: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
    };
    
    toast.innerHTML = (icons[type] || icons.info) + '<span>' + message + '</span>';
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('fade-out');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, duration);
}

function confirmAction(message, callback) {
    return new Promise((resolve) => {
        const confirmed = confirm(message);
        resolve(confirmed);
        if (callback) callback(confirmed);
    });
}

document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        if (e.key === 'r' || e.key === 'R') {
            e.preventDefault();
            showToast('Refreshing page...', 'info', 2000);
            setTimeout(() => {
                location.reload();
            }, 500);
        }
        
        if (e.key === 'f' || e.key === 'F') {
            e.preventDefault();
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
                showToast('Search focused', 'info', 2000);
            }
        }
    }
});



function generateCSVFromGridData(rows) {
    if (!rows || rows.length === 0) return '';
    
    // 收集所有列
    const allKeys = new Set();
    rows.forEach(row => {
        Object.keys(row).forEach(key => {
            if (key !== 'field_wpforms[fields][3]') { // 排除 honeypot
                allKeys.add(key);
            }
        });
    });
    
    // 生成表头 - 用友好的列名
    const headers = Array.from(allKeys).map(key => {
        if (key === 'submission_id') return 'ID';
        if (key === 'form_name') return 'Form';
        if (key === 'submitted_at') return 'Date';
        if (key === 'ip') return 'IP';
        if (key === 'user_agent') return 'User Agent';
        if (key === 'display_name') return 'Name';
        if (key === 'display_email') return 'Email';
        if (key.startsWith('field_')) {
            return getFriendlyLabel(key.substring(6));
        }
        return key;
    });
    
    // 生成 CSV 内容
    let csvContent = headers.join(',') + '\n';
    
    rows.forEach(row => {
        const values = Array.from(allKeys).map(key => {
            let value = row[key] ?? '';
            // 处理包含逗号或引号的值
            if (typeof value === 'string' && (value.includes(',') || value.includes('"') || value.includes('\n'))) {
                value = '"' + value.replace(/"/g, '""') + '"';
            }
            return value;
        });
        csvContent += values.join(',') + '\n';
    });
    
    return csvContent;
}

function downloadCSVFile(csvContent, filename) {
    const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

function downloadSelected() {
    if (!gridApi) {
        showToast('Grid not available.', 'error');
        return;
    }
    
    const selectedRows = gridApi.getSelectedRows();
    if (selectedRows.length === 0) {
        showToast('Please select submissions to download.', 'warning');
        return;
    }
    
    showLoading('Preparing download...');
    
    setTimeout(() => {
        const csvContent = generateCSVFromGridData(selectedRows);
        const filename = 'selected_submissions_' + new Date().toISOString().split('T')[0] + '.csv';
        downloadCSVFile(csvContent, filename);
        hideLoading();
        showToast('Download started!', 'success');
    }, 300);
}

function downloadAll() {
    if (!gridApi) {
        showToast('Grid not available.', 'error');
        return;
    }
    
    const allRows = [];
    gridApi.forEachNode(node => allRows.push(node.data));
    
    if (allRows.length === 0) {
        showToast('No data to download.', 'warning');
        return;
    }
    
    showLoading('Preparing download...');
    
    setTimeout(() => {
        const csvContent = generateCSVFromGridData(allRows);
        const filename = 'all_submissions_' + new Date().toISOString().split('T')[0] + '.csv';
        downloadCSVFile(csvContent, filename);
        hideLoading();
        showToast('Download started!', 'success');
    }, 300);
}

async function performDelete(ids) {
    if (!ids || ids.length === 0) {
        showToast('No items selected.', 'warning');
        return;
    }
    
    showLoading('Deleting...');
    
    try {
        const response = await fetch('forms-api.php?action=delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ ids: ids })
        });
        
        const result = await response.json();
        
        hideLoading();
        
        if (result.success) {
            showToast(result.message || 'Deleted successfully!', 'success');
            // 重新加载数据
            await loadGridData();
        } else {
            showToast(result.message || 'Delete failed.', 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('Error: ' + error.message, 'error');
    }
}

function deleteSingle(id) {
    // 确认删除单个
    document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete this submission? This action cannot be undone.';
    document.getElementById('deleteModal').classList.add('active');
    // 保存要删除的ID
    window.pendingDeleteIds = [id];
}

function deleteSelected() {
    const ids = getSelectedIds();
    if (ids.length === 0) { 
        showToast('Please select submissions to delete.', 'warning');
        return; 
    }
    document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete ' + ids.length + ' submission(s)? This action cannot be undone.';
    document.getElementById('deleteModal').classList.add('active');
    // 保存要删除的ID
    window.pendingDeleteIds = ids;
}

// 直接调用删除的函数
async function confirmDelete() {
    closeModal('deleteModal');
    if (window.pendingDeleteIds && window.pendingDeleteIds.length > 0) {
        await performDelete(window.pendingDeleteIds);
    }
}

const originalResetColumns = resetColumns;
resetColumns = function() {
    showToast('Resetting column settings...', 'info', 2000);
    setTimeout(() => {
        if (!gridApi) return;
        localStorage.removeItem(COLUMN_STATE_KEY);
        gridApi.resetColumnState();
        gridApi.closeToolPanel();
        
        const columnsBtn = document.getElementById('columnsBtn');
        if (columnsBtn) {
            columnsBtn.classList.remove('active');
        }
    }, 300);
};

function initAccessibility() {
    const focusableElements = 'button:not([disabled]), a[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-nav');
        }
    });
    
    document.addEventListener('mousedown', function() {
        document.body.classList.remove('keyboard-nav');
    });
    
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                const focusableContent = modal.querySelectorAll(focusableElements);
                const firstFocusable = focusableContent[0];
                const lastFocusable = focusableContent[focusableContent.length - 1];
                
                if (e.shiftKey && document.activeElement === firstFocusable) {
                    e.preventDefault();
                    lastFocusable.focus();
                } else if (!e.shiftKey && document.activeElement === lastFocusable) {
                    e.preventDefault();
                    firstFocusable.focus();
                }
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initAccessibility();
    
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('msg') === 'deleted') {
        showToast('Submissions deleted successfully!', 'success');
        urlParams.delete('msg');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, '', newUrl);
    }
    
    setTimeout(() => {
        const hints = document.getElementById('keyboardShortcutsHint');
        if (hints) {
            hints.style.opacity = '1';
            setTimeout(() => {
                hints.style.opacity = '0';
            }, 5000);
        }
    }, 2000);
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>