<?php
require_once '../config.php';

requireAdmin();

$page_title = 'Manajemen Pengguna';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = trim($_POST['name']);
                $email = trim($_POST['email']);
                $password = trim($_POST['password']);
                $role_id = intval($_POST['role_id']);
                $phone = trim($_POST['phone']);
                $address = trim($_POST['address']);
                
                if (empty($name) || empty($email) || empty($password) || $role_id <= 0) {
                    $_SESSION['error'] = 'Semua field wajib harus diisi!';
                } else {
                    // Check if email already exists
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $_SESSION['error'] = 'Email sudah terdaftar!';
                    } else {
                        try {
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("
                                INSERT INTO users (name, email, password, role_id, phone, address, created_at) 
                                VALUES (?, ?, ?, ?, ?, ?, NOW())
                            ");
                            $stmt->execute([$name, $email, $hashed_password, $role_id, $phone, $address]);
                            
                            logActivity($_SESSION['user_id'], 'Menambahkan pengguna: ' . $name);
                            $_SESSION['success'] = 'Pengguna berhasil ditambahkan!';
                        } catch (Exception $e) {
                            $_SESSION['error'] = 'Gagal menambahkan pengguna: ' . $e->getMessage();
                        }
                    }
                }
                break;
                
            case 'edit':
                $id = intval($_POST['id']);
                $name = trim($_POST['name']);
                $email = trim($_POST['email']);
                $role_id = intval($_POST['role_id']);
                $phone = trim($_POST['phone']);
                $address = trim($_POST['address']);
                $password = trim($_POST['password']);
                
                if (empty($name) || empty($email) || $role_id <= 0) {
                    $_SESSION['error'] = 'Semua field wajib harus diisi!';
                } else {
                    // Check if email already exists for other users
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                    $stmt->execute([$email, $id]);
                    if ($stmt->fetch()) {
                        $_SESSION['error'] = 'Email sudah digunakan pengguna lain!';
                    } else {
                        try {
                            if (!empty($password)) {
                                // Update with new password
                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                $stmt = $pdo->prepare("
                                    UPDATE users 
                                    SET name = ?, email = ?, password = ?, role_id = ?, phone = ?, address = ?, updated_at = NOW()
                                    WHERE id = ?
                                ");
                                $stmt->execute([$name, $email, $hashed_password, $role_id, $phone, $address, $id]);
                            } else {
                                // Update without changing password
                                $stmt = $pdo->prepare("
                                    UPDATE users 
                                    SET name = ?, email = ?, role_id = ?, phone = ?, address = ?, updated_at = NOW()
                                    WHERE id = ?
                                ");
                                $stmt->execute([$name, $email, $role_id, $phone, $address, $id]);
                            }
                            
                            logActivity($_SESSION['user_id'], 'Mengubah pengguna: ' . $name);
                            $_SESSION['success'] = 'Pengguna berhasil diperbarui!';
                        } catch (Exception $e) {
                            $_SESSION['error'] = 'Gagal memperbarui pengguna: ' . $e->getMessage();
                        }
                    }
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                if ($id == $_SESSION['user_id']) {
                    $_SESSION['error'] = 'Tidak dapat menghapus akun sendiri!';
                } else {
                    try {
                        // Get user name for log
                        $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
                        $stmt->execute([$id]);
                        $user_name = $stmt->fetchColumn();
                        
                        // Delete user
                        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
                        
                        logActivity($_SESSION['user_id'], 'Menghapus pengguna: ' . $user_name);
                        $_SESSION['success'] = 'Pengguna berhasil dihapus!';
                    } catch (Exception $e) {
                        $_SESSION['error'] = 'Gagal menghapus pengguna: ' . $e->getMessage();
                    }
                }
                break;
                
            case 'toggle_status':
                $id = intval($_POST['id']);
                $status = intval($_POST['status']);
                
                if ($id == $_SESSION['user_id']) {
                    $_SESSION['error'] = 'Tidak dapat mengubah status akun sendiri!';
                } else {
                    try {
                        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
                        $stmt->execute([$status, $id]);
                        
                        $action = $status ? 'mengaktifkan' : 'menonaktifkan';
                        logActivity($_SESSION['user_id'], 'Berhasil ' . $action . ' pengguna ID: ' . $id);
                        $_SESSION['success'] = 'Status pengguna berhasil diubah!';
                    } catch (Exception $e) {
                        $_SESSION['error'] = 'Gagal mengubah status: ' . $e->getMessage();
                    }
                }
                break;
        }
    }
    
    header('Location: user.php');
    exit;
}

// Get filters
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Build query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($role_filter)) {
    $where_conditions[] = "u.role_id = ?";
    $params[] = $role_filter;
}

if ($status_filter !== '') {
    $where_conditions[] = "u.status = ?";
    $params[] = $status_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Sort options
$sort_options = [
    'newest' => 'u.created_at DESC',
    'oldest' => 'u.created_at ASC',
    'name_asc' => 'u.name ASC',
    'name_desc' => 'u.name DESC',
    'email_asc' => 'u.email ASC',
    'email_desc' => 'u.email DESC'
];

$order_clause = 'ORDER BY ' . ($sort_options[$sort] ?? $sort_options['newest']);

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count
$count_query = "SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id $where_clause";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_users = $stmt->fetchColumn();
$total_pages = ceil($total_users / $per_page);

// Get users
$query = "
    SELECT u.*, r.name as role_name
    FROM users u 
    JOIN roles r ON u.role_id = r.id 
    $where_clause 
    $order_clause 
    LIMIT $per_page OFFSET $offset
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get roles for form
$roles = $pdo->query("SELECT * FROM roles ORDER BY name")->fetchAll();

require_once 'includes/admin_header.php';
?>

<div class="container-fluid p-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Pengguna</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus me-2"></i>Tambah Pengguna
        </button>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengguna Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password *</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role *</label>
                                <select class="form-select" name="role_id" required>
                                    <option value="">Pilih Role</option>
                                    <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor Telepon</label>
                                <input type="text" class="form-control" name="phone">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Alamat</label>
                                <textarea class="form-control" name="address" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap *</label>
                                <input type="text" class="form-control" name="name" id="edit_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" id="edit_email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password Baru</label>
                                <input type="password" class="form-control" name="password" id="edit_password">
                                <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role *</label>
                                <select class="form-select" name="role_id" id="edit_role_id" required>
                                    <option value="">Pilih Role</option>
                                    <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor Telepon</label>
                                <input type="text" class="form-control" name="phone" id="edit_phone">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Alamat</label>
                                <textarea class="form-control" name="address" id="edit_address" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update Pengguna</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus pengguna <strong id="deleteUserName"></strong>?</p>
                    <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan!</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteUserId">
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Pencarian</label>
                    <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Cari nama atau email...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Role</label>
                    <select class="form-select" name="role">
                        <option value="">Semua Role</option>
                        <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['id']; ?>" <?php echo $role_filter == $role['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($role['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Urutkan</label>
                    <select class="form-select" name="sort">
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                        <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Terlama</option>
                        <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Nama A-Z</option>
                        <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Nama Z-A</option>
                        <option value="email_asc" <?php echo $sort == 'email_asc' ? 'selected' : ''; ?>>Email A-Z</option>
                        <option value="email_desc" <?php echo $sort == 'email_desc' ? 'selected' : ''; ?>>Email Z-A</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Pengguna (<?php echo number_format($total_users); ?> pengguna)</h5>
        </div>
        <div class="card-body p-0">
            <?php if ($users): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Telepon</th>
                            <th>Status</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($user['name']); ?></strong>
<?php if (!empty($user['address'])): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars(substr($user['address'], 0, 50)); ?>...</small>
<?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $user['role_id'] == 1 ? 'danger' : 'primary'; ?>">
                                    <?php echo htmlspecialchars($user['role_name']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo isset($user['status']) && $user['status'] ? 'success' : 'secondary'; ?>">
                                    <?php echo isset($user['status']) && $user['status'] ? 'Aktif' : 'Nonaktif'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <button type="button" class="btn btn-outline-<?php echo isset($user['status']) && $user['status'] ? 'warning' : 'success'; ?>" 
                                            onclick="toggleStatus(<?php echo $user['id']; ?>, <?php echo isset($user['status']) && $user['status'] ? 0 : 1; ?>)">
                                        <i class="fas fa-<?php echo isset($user['status']) && $user['status'] ? 'ban' : 'check'; ?>"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Tidak ada pengguna ditemukan</h5>
                <p class="text-muted">Coba ubah filter pencarian atau tambah pengguna baru</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function editUser(user) {
    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role_id').value = user.role_id;
    document.getElementById('edit_phone').value = user.phone || '';
    document.getElementById('edit_address').value = user.address || '';
    document.getElementById('edit_password').value = '';
    
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

function deleteUser(id, name) {
    document.getElementById('deleteUserId').value = id;
    document.getElementById('deleteUserName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function toggleStatus(id, status) {
    const action = status ? 'mengaktifkan' : 'menonaktifkan';
    if (confirm(`Apakah Anda yakin ingin ${action} pengguna ini?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="toggle_status">
            <input type="hidden" name="id" value="${id}">
            <input type="hidden" name="status" value="${status}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once 'includes/admin_footer.php'; ?>
