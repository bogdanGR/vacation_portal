<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">My Vacation Requests</h1>
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
                            <form method="post" action="/employee/requests/<?= (int)$request->getId() ?>/delete" onsubmit="return confirm('Delete this request?')">
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
