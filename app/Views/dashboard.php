<?php include "layout-header.php"; ?>

<div class="d-flex justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
    <div class="btn-toolbar">
        <button type="button" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-download"></i> Export
        </button>
    </div>
</div>

<!-- Stats cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-primary text-white shadow">
            <div class="card-body">
                <div class="text-white-50 small">Total Assets</div>
                <div class="h4 font-weight-bold">150</div>
                <i class="fas fa-boxes fa-2x position-absolute" style="right: 20px; top: 20px; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-success text-white shadow">
            <div class="card-body">
                <div class="text-white-50 small">Available</div>
                <div class="h4 font-weight-bold">45</div>
                <i class="fas fa-check-circle fa-2x position-absolute" style="right: 20px; top: 20px; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-info text-white shadow">
            <div class="card-body">
                <div class="text-white-50 small">Allocated</div>
                <div class="h4 font-weight-bold">85</div>
                <i class="fas fa-user-check fa-2x position-absolute" style="right: 20px; top: 20px; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-warning text-white shadow">
            <div class="card-body">
                <div class="text-white-50 small">Expiring Soon</div>
                <div class="h4 font-weight-bold">8</div>
                <i class="fas fa-exclamation-triangle fa-2x position-absolute" style="right: 20px; top: 20px; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bolt"></i> Quick Actions
            </div>
            <div class="card-body">
                <a href="/assets/create" class="btn btn-primary me-2 mb-2"><i class="fas fa-plus"></i> Add Asset</a>
                <a href="/employees/create" class="btn btn-success me-2 mb-2"><i class="fas fa-user-plus"></i> Add Employee</a>
                <a href="/allocations/create" class="btn btn-info me-2 mb-2"><i class="fas fa-exchange-alt"></i> Allocate Asset</a>
                <a href="/assets/export" class="btn btn-warning mb-2"><i class="fas fa-download"></i> Export Data</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-exclamation-triangle"></i> Alerts
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <strong>8 assets</strong> warranty expiring within 30 days
                </div>
                <div class="alert alert-danger">
                    <strong>3 contracts</strong> expiring soon
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-history"></i> Recent Activities
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>Asset</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2024-07-20</td>
                        <td><span class="badge bg-primary">Allocated</span></td>
                        <td>Dell Laptop AST001</td>
                        <td>Nguyen Van A</td>
                    </tr>
                    <tr>
                        <td>2024-07-19</td>
                        <td><span class="badge bg-success">Returned</span></td>
                        <td>HP Monitor AST004</td>
                        <td>Tran Thi B</td>
                    </tr>
                    <tr>
                        <td>2024-07-18</td>
                        <td><span class="badge bg-warning">Maintenance</span></td>
                        <td>Printer AST002</td>
                        <td>System</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "layout-footer.php"; ?>