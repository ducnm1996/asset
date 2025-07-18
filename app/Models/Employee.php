### app/Models/Employee.php
```php
<?php

namespace App\Models;

class Employee extends BaseModel
{
    protected $table = 'employees';

    public function getEmployeesWithDepartment()
    {
        $sql = "SELECT e.*, d.name as department_name 
                FROM employees e 
                LEFT JOIN departments d ON e.department_id = d.id 
                WHERE e.status = 'active'
                ORDER BY e.full_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
```