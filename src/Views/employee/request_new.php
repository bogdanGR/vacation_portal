<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">New Vacation Request</h1>
    <a href="/employee" class="btn btn-outline-secondary">← Back to Dashboard</a>
</div>

<?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
<?php endif; ?>

<form method="post" action="/employee/requests/store" class="card card-body">
    <div class="mb-3">
        <label class="form-label">Start Date</label>
        <input type="date" name="start_date" class="form-control"
               value="<?= htmlspecialchars($old['start'] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">End Date</label>
        <input type="date" name="end_date" class="form-control"
               value="<?= htmlspecialchars($old['end'] ?? '') ?>">
        <?php if (!empty($errors['dates'])): ?>
            <div class="text-danger small"><?= htmlspecialchars($errors['dates']) ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label class="form-label">Reason</label>
        <textarea name="reason" class="form-control"><?= htmlspecialchars($old['reason'] ?? '') ?></textarea>
    </div>

    <div class="d-flex gap-2">
        <a href="/employee" class="btn btn-outline-secondary">Cancel</a>
        <button class="btn btn-primary">Submit Request</button>
    </div>
</form>
