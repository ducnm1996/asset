<?php

namespace App\Helpers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExporter
{
    public function exportAssets($assets)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Headers
        $headers = ['Asset Code', 'Name', 'Category', 'Status', 'Purchase Date', 'Purchase Price', 'Location'];
        $sheet->fromArray($headers, null, 'A1');
        
        // Data
        $row = 2;
        foreach ($assets as $asset) {
            $data = [
                $asset['asset_code'],
                $asset['name'],
                $asset['category_name'],
                $asset['status'],
                $asset['purchase_date'],
                $asset['purchase_price'],
                $asset['location']
            ];
            $sheet->fromArray($data, null, "A$row");
            $row++;
        }
        
        // Style headers
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        
        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Output
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="assets_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}