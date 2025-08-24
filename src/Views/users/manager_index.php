<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">
        Manager Dashboard
        <?php if (!empty($pendingCount)): ?>
            <span class="badge bg-danger"><?= $pendingCount ?> pending</span>
        <?php endif; ?>
    </h1>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="/manager/requests">View Requests</a>
        <a class="btn btn-dark" href="/manager/users/new">+ Create User</a>
    </div>
</div>


<h2>Users</h2>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped align-middle m-0">
                <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user->getName()) ?></td>
                        <td><?= htmlspecialchars($user->getUsername()) ?></td>
                        <td><?= htmlspecialchars($user->getEmail()) ?></td>
                        <td class="text-nowrap">
                            <a href="/manager/users/<?= (int)$user->getId(); ?>/edit" class="btn btn-sm btn-primary">
                                Edit
                            </a>

                            <form method="post"
                                  action="/manager/users/<?= (int)$user->getId(); ?>/delete"
                                  class="d-inline"
                                  onsubmit="return confirm('Delete user <?= htmlspecialchars($user->getName()) ?>? This cannot be undone.');">
                                <?php include __DIR__ . '/../partials/csrf.php'; ?>
                                <button class="btn btn-sm btn-outline-danger">
                                    Delete
                                </button>
                            </form>
                        </td>

                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
