<?php
$pageTitle = 'Form Submissions';
require_once __DIR__ . '/header.php';

$search = $_GET['search'] ?? '';
$formFilter = $_GET['form_id'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request. Please try again.';
        $messageType = 'error';
    } else {
        if ($_POST['action'] === 'delete' && isset($_POST['ids'])) {
            $idsToDelete = json_decode($_POST['ids'], true);
            if (is_array($idsToDelete)) {
                try {
                    $db = Database::getInstance()->getConnection();
                    $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));
                    $stmt = $db->prepare("DELETE FROM form_submissions WHERE submission_id IN ($placeholders)");
                    $stmt->execute($idsToDelete);
                } catch(PDOException $e) {
                    $message = 'Database error: ' . $e->getMessage();
                    $messageType = 'error';
                }
            }
            header('Location: forms.php?msg=deleted');
            exit;
        }
        if ($_POST['action'] === 'download_csv') {
            $ids = isset($_POST['ids']) ? json_decode($_POST['ids'], true) : null;
            $selectedFormId = $_POST['csv_form_id'] ?? '';

            try {
                $db = Database::getInstance()->getConnection();
                $sql = "SELECT * FROM form_submissions";
                $params = [];
                
                if ($selectedFormId) {
                    $sql .= " WHERE form_id = ?";
                    $params[] = $selectedFormId;
                }
                
                if ($ids !== null && !empty($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $whereClause = $selectedFormId ? ' AND' : ' WHERE';
                    $sql .= "$whereClause submission_id IN ($placeholders)";
                    $params = array_merge($params, $ids);
                }
                
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $allSubmissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($allSubmissions as &$sub) {
                    $sub['data'] = json_decode($sub['data'], true);
                }
                
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="submissions_' . date('Y-m-d') . '.csv"');
                $output = fopen('php://output', 'w');
                fputcsv($output, ['ID', 'Form', 'Submitted At', 'Name', 'Email', 'Phone', 'Data']);
                foreach ($allSubmissions as $sub) {
                    $name = ($sub['data']['wpforms[fields][13][first]'] ?? $sub['data']['wpforms[fields][19][first]'] ?? '') . ' ' . ($sub['data']['wpforms[fields][13][last]'] ?? $sub['data']['wpforms[fields][19][last]'] ?? '');
                    fputcsv($output, [
                        $sub['submission_id'],
                        $sub['form_name'],
                        $sub['submitted_at'],
                        trim($name),
                        $sub['data']['wpforms[fields][1]'] ?? '',
                        $sub['data']['wpforms[fields][25]'] ?? '',
                        json_encode($sub['data'])
                    ]);
                }
                fclose($output);
                exit;
            } catch(PDOException $e) {
                $message = 'Database error: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

try {
    $db = Database::getInstance()->getConnection();
    
    $countSql = "SELECT COUNT(*) FROM form_submissions";
    $countParams = [];
    $whereParts = [];
    
    if ($formFilter) {
        $whereParts[] = "form_id = ?";
        $countParams[] = $formFilter;
    }
    if ($search) {
        $whereParts[] = "(form_name LIKE ? OR data LIKE ?)";
        $countParams[] = "%$search%";
        $countParams[] = "%$search%";
    }
    
    if (!empty($whereParts)) {
        $countSql .= " WHERE " . implode(' AND ', $whereParts);
    }
    
    $stmt = $db->prepare($countSql);
    $stmt->execute($countParams);
    $total = $stmt->fetchColumn();
    
    $totalPages = max(1, ceil($total / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;
    
    $sql = "SELECT * FROM form_submissions";
    $params = [];
    
    if (!empty($whereParts)) {
        $sql .= " WHERE " . implode(' AND ', $whereParts);
        $params = $countParams;
    }
    
    $sql .= " ORDER BY submitted_at DESC LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($submissions as &$sub) {
        $sub['data'] = json_decode($sub['data'], true);
    }
} catch(PDOException $e) {
    $submissions = [];
    $total = 0;
    $totalPages = 1;
    $message = 'Database error: ' . $e->getMessage();
    $messageType = 'error';
}
?>

<div class="card">
    <div class="card-header">
        <h2>Form Submissions (<?php echo $total; ?>)</h2>
        <div class="toolbar">
            <button onclick="refreshPage()" class="btn btn-secondary btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                Refresh
            </button>
            <button onclick="downloadAll()" class="btn btn-primary btn-sm">Download All CSV</button>
            <button onclick="downloadSelected()" class="btn btn-secondary btn-sm">Download Selected</button>
            <button onclick="deleteSelected()" class="btn btn-danger btn-sm">Delete Selected</button>
        </div>
    </div>

    <div class="toolbar" style="margin-bottom: 20px;">
        <form method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <input type="text" name="search" placeholder="Search submissions..." value="<?php echo htmlspecialchars($search); ?>" style="min-width: 250px;">
            <select name="form_id" style="min-width: 200px;">
                <option value="">All Forms</option>
                <?php foreach (FORM_CONFIGS as $id => $config): ?>
                <option value="<?php echo $id; ?>" <?php echo $formFilter === $id ? 'selected' : ''; ?>><?php echo htmlspecialchars($config['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="forms.php" class="btn btn-secondary btn-sm">Clear</a>
        </form>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <div style="background: #064e3b; color: #34d399; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;">Submissions deleted successfully.</div>
    <?php endif; ?>

    <?php if (empty($submissions)): ?>
        <div class="empty-state">
            <p>No submissions found</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th class="checkbox-cell"><input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"></th>
                    <th>Date</th>
                    <th>Form</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $sub): ?>
                <tr>
                    <td class="checkbox-cell"><input type="checkbox" class="sub-checkbox" value="<?php echo htmlspecialchars($sub['submission_id']); ?>"></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($sub['submitted_at'])); ?></td>
                    <td><span class="badge badge-blue"><?php echo htmlspecialchars($sub['form_name']); ?></span></td>
                    <td><?php echo htmlspecialchars(($sub['data']['wpforms[fields][13][first]'] ?? $sub['data']['wpforms[fields][19][first]'] ?? 'N/A') . ' ' . ($sub['data']['wpforms[fields][13][last]'] ?? $sub['data']['wpforms[fields][19][last]'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars($sub['data']['wpforms[fields][1]'] ?? 'N/A'); ?></td>
                    <td>
                        <button onclick='viewDetail(<?php echo json_encode($sub, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="btn btn-secondary btn-sm">View</button>
                        <button onclick="deleteSingle('<?php echo htmlspecialchars($sub['submission_id']); ?>')" class="btn btn-danger btn-sm">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&form_id=<?php echo urlencode($formFilter); ?>">&laquo; Prev</a>
            <?php else: ?>
            <span class="disabled">&laquo; Prev</span>
            <?php endif; ?>

            <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
            <?php if ($i === $page): ?>
            <span class="active"><?php echo $i; ?></span>
            <?php else: ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&form_id=<?php echo urlencode($formFilter); ?>"><?php echo $i; ?></a>
            <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&form_id=<?php echo urlencode($formFilter); ?>">Next &raquo;</a>
            <?php else: ?>
            <span class="disabled">Next &raquo;</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="detailModal">
    <div class="modal" style="max-width: 700px;">
        <h3>Submission Details</h3>
        <div id="detailContent"></div>
        <div class="modal-actions">
            <button onclick="closeModal('detailModal')" class="btn btn-secondary">Close</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <h3>Confirm Delete</h3>
        <p id="deleteMessage">Are you sure you want to delete the selected submission(s)? This action cannot be undone.</p>
        <form method="POST" id="deleteForm">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="ids" id="deleteIds">
            <div class="modal-actions">
                <button type="button" onclick="closeModal('deleteModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>

<form method="POST" id="csvForm">
    <input type="hidden" name="action" value="download_csv">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="ids" id="csvIds">
    <input type="hidden" name="csv_form_id" id="csvFormId" value="">
</form>

<script>
function refreshPage() { location.reload(); }

function toggleSelectAll(el) {
    document.querySelectorAll('.sub-checkbox').forEach(cb => cb.checked = el.checked);
}

function getSelectedIds() {
    return Array.from(document.querySelectorAll('.sub-checkbox:checked')).map(cb => cb.value);
}

function viewDetail(sub) {
    let html = '<div style="max-height: 400px; overflow-y: auto;">';
    html += '<div class="detail-row"><div class="detail-label">ID</div><div class="detail-value">' + sub.submission_id + '</div></div>';
    html += '<div class="detail-row"><div class="detail-label">Form</div><div class="detail-value">' + sub.form_name + '</div></div>';
    html += '<div class="detail-row"><div class="detail-label">Submitted</div><div class="detail-value">' + sub.submitted_at + '</div></div>';
    html += '<div class="detail-row"><div class="detail-label">IP</div><div class="detail-value">' + sub.ip + '</div></div>';
    for (let key in sub.data) {
        let label = key.replace('wpforms[fields][', '').replace(']', '').replace('[', ' - ');
        html += '<div class="detail-row"><div class="detail-label">' + label + '</div><div class="detail-value">' + sub.data[key] + '</div></div>';
    }
    html += '</div>';
    document.getElementById('detailContent').innerHTML = html;
    document.getElementById('detailModal').classList.add('active');
}

function deleteSingle(id) {
    document.getElementById('deleteIds').value = JSON.stringify([id]);
    document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete this submission? This action cannot be undone.';
    document.getElementById('deleteModal').classList.add('active');
}

function deleteSelected() {
    const ids = getSelectedIds();
    if (ids.length === 0) { alert('Please select submissions to delete.'); return; }
    document.getElementById('deleteIds').value = JSON.stringify(ids);
    document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete ' + ids.length + ' submission(s)? This action cannot be undone.';
    document.getElementById('deleteModal').classList.add('active');
}

function downloadAll() {
    document.getElementById('csvIds').value = '';
    document.getElementById('csvFormId').value = '<?php echo htmlspecialchars($formFilter); ?>';
    document.getElementById('csvForm').submit();
}

function downloadSelected() {
    const ids = getSelectedIds();
    if (ids.length === 0) { alert('Please select submissions to download.'); return; }
    document.getElementById('csvIds').value = JSON.stringify(ids);
    document.getElementById('csvFormId').value = '<?php echo htmlspecialchars($formFilter); ?>';
    document.getElementById('csvForm').submit();
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
