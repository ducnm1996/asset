<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Asset Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #343a40; }
        .stats-card { transition: transform 0.2s; border-left: 4px solid; }
        .stats-card:hover { transform: translateY(-5px); }
        .stats-card.primary { border-left-color: #007bff; }
        .stats-card.success { border-left-color: #28a745; }
        .stats-card.info { border-left-color: #17a2b8; }
        .stats-card.warning { border-left-color: #ffc107; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/dashboard">
                <i class="fas fa-boxes"></i> Asset Management System
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> Admin
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user-edit"></i> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="/dashboard">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light" href="/assets">
                                <i class="fas fa-boxes"></i> Assets
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light" href="/categories">
                                <i class="fas fa-tags"></i> Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light" href="/employees">
                                <i class="fas fa-users"></i> Employees
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light" href="/departments">
                                <i class="fas fa-building"></i> Departments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light" href="/contracts">
                                <i class="fas fa-file-contract"></i> Contracts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light" href="/allocations">
                                <i class="fas fa-exchange-alt"></i> Allocations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light" href="/maintenance">
                                <i class="fas fa-tools"></i> Maintenance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light" href="/users">
                                <i class="fas fa-user-cog"></i> Users
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-0 shadow h-100 py-2 stats-card primary">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Assets</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_assets'] ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-boxes fa-2x text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-0 shadow h-100 py-2 stats-card success">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Available</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['available_assets'] ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-0 shadow h-100 py-2 stats-card info">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Allocated</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['allocated_assets'] ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-check fa-2x text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-0 shadow h-100 py-2 stats-card warning">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Expiring Soon</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['expiring_assets'] ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Assets Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Assets</h6>
                        <a href="/assets" class="btn btn-primary btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Asset Name</th>
                                        <th>Status</th>
                                        <th>Assigned To</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentAssets as $asset): ?>
                                    <tr>
                                        <td><?= $asset['id'] ?></td>
                                        <td><?= htmlspecialchars($asset['name']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $asset['status'] === 'available' ? 'success' : 'primary' ?>">
                                                <?= ucfirst($asset['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= $asset['employee'] ? htmlspecialchars($asset['employee']) : '-' ?></td>
                                        <td>
                                            <a href="/assets/edit/<?= $asset['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>