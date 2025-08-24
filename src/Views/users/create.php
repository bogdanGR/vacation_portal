<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">Create User</h1>
    <a href="/manager" class="btn btn-outline-secondary">← Back to Dashboard</a>
</div>

<form method="post" action="/manager/users" class="card card-narrow shadow-sm">
    <?php include __DIR__ . '/../partials/csrf.php'; ?>
    <div class="card-body vstack gap-3">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="m-0">
                    <?php foreach ($errors as $f => $msg): ?>
                        <li><strong><?= htmlspecialchars($f) ?>:</strong> <?= htmlspecialchars($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div>
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
                <option value="employee" <?= (!empty($old['role']) && $old['role']==='employee')?'selected':'' ?>>Employee</option>
                <option value="manager"  <?= (!empty($old['role']) && $old['role']==='manager')?'selected':''  ?>>Manager</option>
            </select>
        </div>

        <div>
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
        </div>

        <div>
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($old['username'] ?? '') ?>" required>
        </div>

        <div>
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
        </div>

        <div>
            <label class="form-label">Employee Code (7 digits, employees only)</label>
            <input type="text" name="employee_code" class="form-control" value="<?= htmlspecialchars($old['employee_code'] ?? ($prefill_employee_code ?? '')) ?>" placeholder="e.g. 1000002">
        </div>

        <div>
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" minlength="6" required>
        </div>

        <div class="d-grid">
            <button class="btn btn-primary">Create</button>
        </div>
    </div>
</form>
