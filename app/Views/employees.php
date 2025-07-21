<?php include "layout-header.php"; ?>

<div class="d-flex justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-users"></i> Employees Management</h1>
    <div>
        <a href="/employees/create" class="btn btn-primary"><i class="fas fa-plus"></i> Add Employee</a>
        <a href="/employees/export" class="btn btn-success"><i class="fas fa-download"></i> Export</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $employee): ?>
                    <tr>
                        <td><?= $employee["code"] ?></td>
                        <td><?= htmlspecialchars($employee["name"]) ?></td>
                        <td><?= $employee["department"] ?></td>
                        <td><?= $employee["email"] ?></td>
                        <td><?= $employee["phone"] ?></td>
                        <td>
                            <a href="/employees/edit/<?= $employee["id"] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                            <a href="/employees/delete/<?= $employee["id"] ?>" class="btn btn-sm btn-danger" onclick="return confirm(\"Are you sure?\")"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "layout-footer.php"; ?>