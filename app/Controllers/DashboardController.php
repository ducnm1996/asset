### app/Controllers/DashboardController.php
```php
<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Asset;
use App\Models\Contract;
use App\Models\Department;

class DashboardController extends BaseController
{
    public function index()
    {
        Auth::requireAuth();
        
        $assetModel = new Asset();
        $contractModel = new Contract();
        $departmentModel = new Department();
        
        // Statistics
        $totalAssets = $assetModel->count();
        $availableAssets = $assetModel->count("status = 'available'");
        $allocatedAssets = $assetModel->count("status = 'allocated'");
        $disposedAssets = $assetModel->count("status = 'disposed'");
        
        // Assets expiring warranty
        $expiringAssets = $assetModel->getAssetsExpiringWarranty(30);
        
        // Contracts expiring
        $expiringContracts = $contractModel->getContractsExpiring(30);
        
        // Assets by status
        $assetsByStatus = $assetModel->getAssetsByStatus();
        
        $this->view('dashboard/index', [
            'totalAssets' => $totalAssets,
            'availableAssets' => $availableAssets,
            'allocatedAssets' => $allocatedAssets,
            'disposedAssets' => $disposedAssets,
            'expiringAssets' => $expiringAssets,
            'expiringContracts' => $expiringContracts,
            'assetsByStatus' => $assetsByStatus
        ]);
    }
}
```