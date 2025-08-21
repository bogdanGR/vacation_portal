<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">Manager Dashboard</h1>
    <a class="btn btn-dark" href="/manager/users/new">+ Create User</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped align-middle m-0">
                <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user->getName()) ?></td>
                        <td><?= htmlspecialchars($user->getEmail()) ?></td>
                        <td>Actions</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
