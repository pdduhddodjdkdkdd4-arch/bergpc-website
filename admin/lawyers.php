<?php
$pageTitle = 'Lawyers';
require_once __DIR__ . '/header.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request. Please try again.';
        $messageType = 'error';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $name = trim($_POST['name'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $bio = trim($_POST['bio'] ?? '');
            $imageUrl = trim($_POST['image_url'] ?? '');
            $imageType = 'url';
            $imagePath = $imageUrl;

            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            $slug = trim($slug, '-');

            if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image_file'];
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

                if (!in_array($file['type'], $allowedTypes)) {
                    $message = 'Invalid image type. Only JPG, PNG, WebP, GIF allowed.';
                    $messageType = 'error';
                } elseif ($file['size'] > MAX_UPLOAD_SIZE) {
                    $message = 'Image too large. Maximum size is 2MB.';
                    $messageType = 'error';
                } else {
                    if (!is_dir(UPLOAD_DIR)) {
                        mkdir(UPLOAD_DIR, 0755, true);
                    }
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = $slug . '.' . $ext;
                    $filepath = UPLOAD_DIR . '/' . $filename;

                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $imagePath = '../../wp-content/uploads/lawyers/' . $filename;
                        $imageType = 'local';
                    }
                }
            }

            if (empty($name) || empty($bio)) {
                $message = 'Name and bio are required.';
                $messageType = 'error';
            } elseif (empty($messageType)) {
                try {
                    $db = Database::getInstance()->getConnection();

                    $stmt = $db->prepare("SELECT COUNT(*) FROM lawyers WHERE slug = ?");
                    $stmt->execute([$slug]);
                    if ($stmt->fetchColumn() > 0) {
                        $slug = $slug . '-' . time();
                    }

                    $stmt = $db->prepare("INSERT INTO lawyers (name, slug, title, bio, image, image_type) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $slug, $title, $bio, $imagePath, $imageType]);

                    $lawyerId = $db->lastInsertId();
                    
                    $stmt = $db->prepare("SELECT * FROM lawyers WHERE id = ?");
                    $stmt->execute([$lawyerId]);
                    $newLawyer = $stmt->fetch(PDO::FETCH_ASSOC);

                    require_once __DIR__ . '/generate_lawyer_page.php';
                    generateLawyerPage($newLawyer);

                    $message = 'Lawyer added successfully!';
                    $messageType = 'success';
                } catch(PDOException $e) {
                    $message = 'Database error: ' . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }

        if ($action === 'edit') {
            $id = $_POST['id'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $bio = trim($_POST['bio'] ?? '');
            $imageUrl = trim($_POST['image_url'] ?? '');

            try {
                $db = Database::getInstance()->getConnection();

                $stmt = $db->prepare("SELECT * FROM lawyers WHERE id = ?");
                $stmt->execute([$id]);
                $lawyer = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($lawyer) {
                    $updateData = [
                        'name' => $name,
                        'title' => $title,
                        'bio' => $bio
                    ];

                    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                        $file = $_FILES['image_file'];
                        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                        if (in_array($file['type'], $allowedTypes) && $file['size'] <= MAX_UPLOAD_SIZE) {
                            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
                            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                            $filename = $lawyer['slug'] . '.' . $ext;
                            $filepath = UPLOAD_DIR . '/' . $filename;
                            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                                if ($lawyer['image_type'] === 'local' && !empty($lawyer['image'])) {
                                    $oldPath = __DIR__ . '/../' . $lawyer['image'];
                                    if (file_exists($oldPath)) unlink($oldPath);
                                }
                                $updateData['image'] = '../../wp-content/uploads/lawyers/' . $filename;
                                $updateData['image_type'] = 'local';
                            }
                        }
                    } elseif (!empty($imageUrl)) {
                        if ($lawyer['image_type'] === 'local' && !empty($lawyer['image'])) {
                            $oldPath = __DIR__ . '/../' . $lawyer['image'];
                            if (file_exists($oldPath)) unlink($oldPath);
                        }
                        $updateData['image'] = $imageUrl;
                        $updateData['image_type'] = 'url';
                    }

                    $setClause = implode(', ', array_map(function($k) { return "$k = ?"; }, array_keys($updateData)));
                    $updateData['id'] = $id;

                    $stmt = $db->prepare("UPDATE lawyers SET $setClause WHERE id = ?");
                    $stmt->execute(array_values($updateData));

                    $stmt = $db->prepare("SELECT * FROM lawyers WHERE id = ?");
                    $stmt->execute([$id]);
                    $updatedLawyer = $stmt->fetch(PDO::FETCH_ASSOC);

                    require_once __DIR__ . '/generate_lawyer_page.php';
                    generateLawyerPage($updatedLawyer);

                    $message = 'Lawyer updated successfully!';
                    $messageType = 'success';
                }
            } catch(PDOException $e) {
                $message = 'Database error: ' . $e->getMessage();
                $messageType = 'error';
            }
        }

        if ($action === 'delete') {
            $id = $_POST['id'] ?? '';

            try {
                $db = Database::getInstance()->getConnection();

                $stmt = $db->prepare("SELECT * FROM lawyers WHERE id = ?");
                $stmt->execute([$id]);
                $lawyer = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($lawyer) {
                    if ($lawyer['image_type'] === 'local' && !empty($lawyer['image'])) {
                        $imagePath = __DIR__ . '/../' . $lawyer['image'];
                        if (file_exists($imagePath)) unlink($imagePath);
                    }
                    $pageDir = __DIR__ . '/../lawyers/' . $lawyer['slug'];
                    if (is_dir($pageDir)) {
                        $files = glob($pageDir . '/*');
                        foreach ($files as $file) unlink($file);
                        rmdir($pageDir);
                    }

                    $stmt = $db->prepare("DELETE FROM lawyers WHERE id = ?");
                    $stmt->execute([$id]);

                    $message = 'Lawyer deleted successfully!';
                    $messageType = 'success';
                }
            } catch(PDOException $e) {
                $message = 'Database error: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT * FROM lawyers ORDER BY created_at DESC");
    $lawyers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $lawyers = [];
    $message = 'Failed to load lawyers: ' . $e->getMessage();
    $messageType = 'error';
}

$editLawyer = null;
if (isset($_GET['edit'])) {
    foreach ($lawyers as $l) {
        if ($l['id'] == $_GET['edit']) {
            $editLawyer = $l;
            break;
        }
    }
}
?>

<?php if ($message): ?>
<div style="background: <?php echo $messageType === 'success' ? '#064e3b' : '#7f1d1d'; ?>; color: <?php echo $messageType === 'success' ? '#34d399' : '#fca5a5'; ?>; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>Team Members (<?php echo count($lawyers); ?>)</h2>
        <button onclick="showAddForm()" class="btn btn-primary btn-sm">+ Add Lawyer</button>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;">
        <?php foreach ($lawyers as $lawyer): ?>
        <div class="card" style="margin-bottom: 0; display: flex; gap: 16px; align-items: center;">
            <?php if (!empty($lawyer['image'])): ?>
            <img src="<?php echo htmlspecialchars($lawyer['image']); ?>" alt="<?php echo htmlspecialchars($lawyer['name']); ?>" class="img-thumbnail">
            <?php else: ?>
            <div class="img-thumbnail" style="background: #2d3f5e; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 24px;"><?php echo strtoupper(substr($lawyer['name'], 0, 1)); ?></div>
            <?php endif; ?>
            <div style="flex: 1; min-width: 0;">
                <div style="font-weight: 600; margin-bottom: 4px;"><?php echo htmlspecialchars($lawyer['name']); ?></div>
                <div style="color: #94a3b8; font-size: 13px; margin-bottom: 8px;"><?php echo htmlspecialchars($lawyer['title']); ?></div>
                <div style="display: flex; gap: 8px;">
                    <a href="lawyers.php?edit=<?php echo urlencode($lawyer['id']); ?>" class="btn btn-secondary btn-sm">Edit</a>
                    <button onclick="confirmDelete('<?php echo htmlspecialchars($lawyer['id']); ?>', '<?php echo htmlspecialchars($lawyer['name']); ?>')" class="btn btn-danger btn-sm">Delete</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal-overlay" id="formModal" <?php if ($editLawyer) echo 'style="display: flex;"'; ?>>
    <div class="modal" style="max-width: 600px;">
        <h3><?php echo $editLawyer ? 'Edit Lawyer' : 'Add New Lawyer'; ?></h3>
        <form method="POST" enctype="multipart/form-data" id="lawyerForm">
            <input type="hidden" name="action" value="<?php echo $editLawyer ? 'edit' : 'add'; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <?php if ($editLawyer): ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($editLawyer['id']); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($editLawyer['name'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>Title / Position</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($editLawyer['title'] ?? ''); ?>" placeholder="e.g. Trial Lawyer, Partner">
            </div>

            <div class="form-group">
                <label>Image</label>
                <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 8px;">
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                        <input type="radio" name="image_source" value="upload" <?php echo (!$editLawyer || $editLawyer['image_type'] === 'local') ? 'checked' : ''; ?> onchange="toggleImageSource()"> Upload File
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                        <input type="radio" name="image_source" value="url" <?php echo ($editLawyer && $editLawyer['image_type'] === 'url') ? 'checked' : ''; ?> onchange="toggleImageSource()"> Image URL
                    </label>
                </div>
                <div id="uploadSection">
                    <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif" style="background: transparent; border: none; padding: 0;">
                    <div style="color: #64748b; font-size: 12px; margin-top: 4px;">Max 2MB. JPG, PNG, WebP, GIF.</div>
                </div>
                <div id="urlSection" style="display: none;">
                    <input type="text" name="image_url" placeholder="https://example.com/photo.jpg" value="<?php echo htmlspecialchars(($editLawyer && $editLawyer['image_type'] === 'url') ? $editLawyer['image'] : ''); ?>">
                </div>
                <?php if ($editLawyer && !empty($editLawyer['image'])): ?>
                <div style="margin-top: 8px;">
                    <img src="<?php echo htmlspecialchars($editLawyer['image']); ?>" style="max-width: 100px; max-height: 80px; border-radius: 6px;">
                </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Bio *</label>
                <textarea name="bio" required><?php echo htmlspecialchars($editLawyer['bio'] ?? ''); ?></textarea>
            </div>

            <div class="modal-actions">
                <button type="button" onclick="closeFormModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary" onclick="return confirmSubmit()"><?php echo $editLawyer ? 'Update Lawyer' : 'Add Lawyer'; ?></button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <h3>Confirm Delete</h3>
        <p>Are you sure you want to delete <strong id="deleteName"></strong>? This will also remove their page and uploaded image. This action cannot be undone.</p>
        <form method="POST" id="deleteForm">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="id" id="deleteId">
            <div class="modal-actions">
                <button type="button" onclick="closeModal('deleteModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddForm() {
    document.getElementById('formModal').style.display = 'flex';
    document.getElementById('lawyerForm').reset();
}

function closeFormModal() {
    <?php if ($editLawyer): ?>
    window.location.href = 'lawyers.php';
    <?php else: ?>
    document.getElementById('formModal').style.display = 'none';
    <?php endif; ?>
}

function toggleImageSource() {
    const source = document.querySelector('input[name="image_source"]:checked').value;
    document.getElementById('uploadSection').style.display = source === 'upload' ? 'block' : 'none';
    document.getElementById('urlSection').style.display = source === 'url' ? 'block' : 'none';
}

function confirmDelete(id, name) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteName').textContent = name;
    document.getElementById('deleteModal').classList.add('active');
}

function confirmSubmit() {
    return confirm('Are you sure you want to <?php echo $editLawyer ? "update" : "add"; ?> this lawyer?');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) {
            <?php if ($editLawyer): ?>
            if (this.id === 'formModal') {
                window.location.href = 'lawyers.php';
            } else {
                this.classList.remove('active');
            }
            <?php else: ?>
            this.classList.remove('active');
            <?php endif; ?>
        }
    });
});

toggleImageSource();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
