<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">Vacation Requests</h1>
    <div>
        <a href="/manager/requests?status=all"
           class="btn btn-sm <?= $status==='all' ? 'btn-primary' : 'btn-outline-primary' ?>">All</a>
        <a href="/manager/requests?status=pending"
           class="btn btn-sm <?= $status==='pending' ? 'btn-primary' : 'btn-outline-primary' ?>">Pending</a>
        <a href="/manager/requests?status=approved"
           class="btn btn-sm <?= $status==='approved' ? 'btn-primary' : 'btn-outline-primary' ?>">Approved</a>
        <a href="/manager/requests?status=rejected"
           class="btn btn-sm <?= $status==='rejected' ? 'btn-primary' : 'btn-outline-primary' ?>">Rejected</a>
    </div>
    <a href="/manager" class="btn btn-outline-secondary">← Back to Dashboard</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped align-middle m-0">
                <thead class="table-light">
                <tr>
                    <th>Employee</th>
                    <th>Submitted</th>
                    <th>Period</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td>
                            <div class="fw-medium"><?= htmlspecialchars($request['employee_name']) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($request['employee_email']) ?></div>
                        </td>
                        <td><?= date_format(date_create($request['submitted_at']),"d/m/Y") ?></td>
                        <td><?= date_format(date_create($request['start_date']),"d/m/Y") ?> → <?= date_format(date_create($request['end_date']),"d/m/Y") ?></td>
                        <td><?= nl2br(htmlspecialchars($request['reason'] ?? '')) ?></td>
                        <td>
                        <span class="badge bg-<?= $request['status']==='approved'?'success':($request['status']==='rejected'?'danger':'secondary') ?>">
                            <?= htmlspecialchars($request['status']) ?>
                        </span>
                        </td>
                        <td class="text-end">
                            <?php if ($request['status'] === 'pending'): ?>
                                <form method="post" action="/manager/requests/<?= (int)$request['id'] ?>/approve" class="d-inline">
                                    <?php include __DIR__ . '/../partials/csrf.php'; ?>
                                    <button class="btn btn-sm btn-success"
                                            onclick="return confirm('Approve this request?')">Approve</button>
                                </form>
                                <form method="post" action="/manager/requests/<?= (int)$request['id'] ?>/reject" class="d-inline">
                                    <?php include __DIR__ . '/../partials/csrf.php'; ?>
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Reject this request?')">Reject</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small">Processed <?= !empty($request['processed_at']) ? date_format(date_create($request['processed_at']),"d/m/Y H:i:s") : '' ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
