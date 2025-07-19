<?php

namespace App\Helpers;

use Dompdf\Dompdf;
use Dompdf\Options;

class PDFExporter
{
    public function exportAssets($assets)
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        
        $html = $this->generateAssetsHTML($assets);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        $dompdf->stream('assets_' . date('Y-m-d') . '.pdf');
    }
    
    private function generateAssetsHTML($assets)
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .header { text-align: center; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>Asset Management Report</h2>
                <p>Generated on ' . date('Y-m-d H:i:s') . '</p>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Asset Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Purchase Date</th>
                        <th>Purchase Price</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($assets as $asset) {
            $html .= '<tr>
                <td>' . htmlspecialchars($asset['asset_code']) . '</td>
                <td>' . htmlspecialchars($asset['name']) . '</td>
                <td>' . htmlspecialchars($asset['category_name']) . '</td>
                <td>' . htmlspecialchars($asset['status']) . '</td>
                <td>' . htmlspecialchars($asset['purchase_date']) . '</td>
                <td>' . number_format($asset['purchase_price']) . '</td>
                <td>' . htmlspecialchars($asset['location']) . '</td>
            </tr>';
        }
        
        $html .= '</tbody></table></body></html>';
        
        return $html;
    }
}