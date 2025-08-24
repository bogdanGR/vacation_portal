<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">My Vacation Requests</h1>
    <div>
        <a href="/employee?status=all"
           class="btn btn-sm <?= $status==='all' ? 'btn-primary' : 'btn-outline-primary' ?>">All</a>
        <a href="/employee?status=pending"
           class="btn btn-sm <?= $status==='pending' ? 'btn-primary' : 'btn-outline-primary' ?>">Pending</a>
        <a href="/employee?status=approved"
           class="btn btn-sm <?= $status==='approved' ? 'btn-primary' : 'btn-outline-primary' ?>">Approved</a>
        <a href="/employee?status=rejected"
           class="btn btn-sm <?= $status==='rejected' ? 'btn-primary' : 'btn-outline-primary' ?>">Rejected</a>
    </div>
    <a class="btn btn-dark" href="/employee/requests/new">+ New Request</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-striped m-0 align-middle">
            <thead class="table-light">
            <tr>
                <th>Submitted</th>
                <th>Period</th>
                <th>Reason</th>
                <th>Status</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($requests as $request): ?>
                <tr>
                    <td><?= htmlspecialchars($request->getSubmittedAt()) ?></td>
                    <td><?= htmlspecialchars($request->getStartDate()) ?> → <?= htmlspecialchars($request->getEndDate()) ?></td>
                    <td><?= htmlspecialchars($request->getReason()) ?></td>
                    <td>
                        <span class="badge bg-<?= $request->getStatus() === 'approved' ? 'success' : ($request->getStatus() === 'rejected' ? 'danger':'secondary') ?>">
                            <?= htmlspecialchars($request->getStatus()) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($request->getStatus() === 'pending'): ?>
                            <div class="d-flex gap-2">
                                <a href="/employee/requests/<?= $request->getId() ?>/edit" class="btn btn-sm btn-primary">Edit</a>
                                <form method="post" action="/employee/requests/<?= (int)$request->getId() ?>/delete" onsubmit="return confirm('Delete this request?')">
                                    <?php include __DIR__ . '/../partials/csrf.php'; ?>
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
