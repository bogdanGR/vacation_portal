<h1 class="h4 mb-3">Edit User</h1>

<?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
<?php endif; ?>

<form method="post" action="/manager/users/<?= (int)$user->getId(); ?>/edit" class="card card-body">
    <div class="row g-3">
        <div class="col-md-12">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" value="<?= htmlspecialchars($old['name'] ?? $user->getName()) ?>">
            <?php if (!empty($errors['name'])): ?><div class="text-danger small"><?= htmlspecialchars($errors['name']) ?></div><?php endif; ?>
        </div>

        <div class="col-md-12">
            <label class="form-label">Email</label>
            <input name="email" class="form-control" value="<?= htmlspecialchars($old['email'] ?? $user->getEmail()) ?>">
            <?php if (!empty($errors['email'])): ?><div class="text-danger small"><?= htmlspecialchars($errors['email']) ?></div><?php endif; ?>
        </div>

        <div class="col-md-12">
            <label class="form-label">New Password (optional)</label>
            <input type="password" name="password" class="form-control" autocomplete="new-password">
            <div class="form-text">Leave blank to keep the current password.</div>
            <?php if (!empty($errors['password'])): ?><div class="text-danger small"><?= htmlspecialchars($errors['password']) ?></div><?php endif; ?>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <a href="/manager" class="btn btn-outline-secondary">Cancel</a>
        <button class="btn btn-primary">Save Changes</button>
    </div>
</form>
