### app/Controllers/AssetController.php
```php
<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Helpers\ExcelExporter;
use App\Helpers\PDFExporter;

class AssetController extends BaseController
{
    private $assetModel;
    private $categoryModel;

    public function __construct()
    {
        $this->assetModel = new Asset();
        $this->categoryModel = new AssetCategory();
    }

    public function index()
    {
        Auth::requireAuth();
        
        $search = $_GET['search'] ?? '';
        $categoryId = $_GET['category_id'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $assets = $this->assetModel->getAssetsWithCategory($search, $categoryId, $status);
        $categories = $this->categoryModel->findAll('name');
        
        $this->view('assets/index', [
            'assets' => $assets,
            'categories' => $categories,
            'search' => $search,
            'categoryId' => $categoryId,
            'status' => $status
        ]);
    }

    public function create()
    {
        Auth::requireRole('manager');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'asset_code' => $_POST['asset_code'],
                'name' => $_POST['name'],
                'category_id' => $_POST['category_id'],
                'description' => $_POST['description'],
                'purchase_date' => $_POST['purchase_date'],
                'purchase_price' => $_POST['purchase_price'],
                'warranty_end_date' => $_POST['warranty_end_date'],
                'location' => $_POST['location'],
                'serial_number' => $_POST['serial_number'],
                'model' => $_POST['model'],
                'manufacturer' => $_POST['manufacturer'],
                'status' => 'available'
            ];
            
            if ($this->assetModel->create($data)) {
                $this->setFlash('success', 'Asset created successfully');
                $this->redirect('/assets');
            } else {
                $this->setFlash('error', 'Failed to create asset');
            }
        }
        
        $categories = $this->categoryModel->findAll('name');
        $this->view('assets/create', ['categories' => $categories]);
    }

    public function edit($id)
    {
        Auth::requireRole('manager');
        
        $asset = $this->assetModel->find($id);
        if (!$asset) {
            $this->setFlash('error', 'Asset not found');
            $this->redirect('/assets');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'asset_code' => $_POST['asset_code'],
                'name' => $_POST['name'],
                'category_id' => $_POST['category_id'],
                'description' => $_POST['description'],
                'purchase_date' => $_POST['purchase_date'],
                'purchase_price' => $_POST['purchase_price'],
                'warranty_end_date' => $_POST['warranty_end_date'],
                'location' => $_POST['location'],
                'serial_number' => $_POST['serial_number'],
                'model' => $_POST['model'],
                'manufacturer' => $_POST['manufacturer']
            ];
            
            if ($this->assetModel->update($id, $data)) {
                $this->setFlash('success', 'Asset updated successfully');
                $this->redirect('/assets');
            } else {
                $this->setFlash('error', 'Failed to update asset');
            }
        }
        
        $categories = $this->categoryModel->findAll('name');
        $this->view('assets/edit', [
            'asset' => $asset,
            'categories' => $categories
        ]);
    }

    public function delete($id)
    {
        Auth::requireRole('admin');
        
        if ($this->assetModel->delete($id)) {
            $this->setFlash('success', 'Asset deleted successfully');
        } else {
            $this->setFlash('error', 'Failed to delete asset');
        }
        
        $this->redirect('/assets');
    }

    public function export()
    {
        Auth::requireAuth();
        
        $format = $_GET['format'] ?? 'excel';
        $assets = $this->assetModel->getAssetsWithCategory();
        
        if ($format === 'pdf') {
            $pdfExporter = new PDFExporter();
            $pdfExporter->exportAssets($assets);
        } else {
            $excelExporter = new ExcelExporter();
            $excelExporter->exportAssets($assets);
        }
    }
}
```