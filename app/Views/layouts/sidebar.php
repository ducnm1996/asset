### app/Views/layouts/sidebar.php
```php
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="/dashboard">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/assets">
                    <i class="fas fa-boxes"></i> Assets
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/categories">
                    <i class="fas fa-tags"></i> Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/employees">
                    <i class="fas fa-users"></i> Employees
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/departments">
                    <i class="fas fa-building"></i> Departments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/contracts">
                    <i class="fas fa-file-contract"></i> Contracts
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/allocations">
                    <i class="fas fa-exchange-alt"></i> Allocations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/maintenance">
                    <i class="fas fa-tools"></i> Maintenance
                </a>
            </li>
            <?php if (App\Core\Auth::hasRole('admin')): ?>
            <li class="nav-item">
                <a class="nav-link" href="/users">
                    <i class="fas fa-user-cog"></i> Users
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
```