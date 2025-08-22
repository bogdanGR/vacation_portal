<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-3"><?= htmlspecialchars($title) ?></h1>
    <a href="/employee" class="btn btn-outline-secondary">← Back to Dashboard</a>
</div>


<?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars($action) ?>" class="card card-body">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control"
                   value="<?= htmlspecialchars($old['start_date'] ?? ($request->getStartDate() ?? '')) ?>">
            <?php if (!empty($errors['start_date'])): ?>
                <div class="text-danger small"><?= htmlspecialchars($errors['start_date']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control"
                   value="<?= htmlspecialchars($old['end_date'] ?? ($request->getEndDate() ?? '')) ?>">
            <?php if (!empty($errors['end_date'])): ?>
                <div class="text-danger small"><?= htmlspecialchars($errors['end_date']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-12">
            <label class="form-label">Reason</label>
            <textarea name="reason" class="form-control"><?= htmlspecialchars($old['reason'] ?? $request->getReason() ?? '') ?></textarea>
            <?php if (!empty($errors['reason'])): ?>
                <div class="text-danger small"><?= htmlspecialchars($errors['reason']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <a href="/employee" class="btn btn-outline-secondary">Cancel</a>
        <button class="btn btn-primary"><?= $submitLabel ?? 'Submit Request' ?></button>
    </div>
</form>
