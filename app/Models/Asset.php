### app/Models/Asset.php
```php
<?php

namespace App\Models;

class Asset extends BaseModel
{
    protected $table = 'assets';

    public function getAssetsWithCategory($search = null, $categoryId = null, $status = null)
    {
        $sql = "SELECT a.*, c.name as category_name 
                FROM assets a 
                LEFT JOIN asset_categories c ON a.category_id = c.id 
                WHERE 1=1";
        
        $params = [];
        
        if ($search) {
            $sql .= " AND (a.name ILIKE ? OR a.asset_code ILIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($categoryId) {
            $sql .= " AND a.category_id = ?";
            $params[] = $categoryId;
        }
        
        if ($status) {
            $sql .= " AND a.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY a.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getAssetsByStatus()
    {
        $sql = "SELECT status, COUNT(*) as count FROM assets GROUP BY status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAssetsExpiringWarranty($days = 30)
    {
        $sql = "SELECT * FROM assets 
                WHERE warranty_end_date BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL '$days days'
                ORDER BY warranty_end_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
```