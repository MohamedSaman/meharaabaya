<?php

namespace App\Exports;

use App\Models\ProductDetail;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProductsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{
    protected $search;
    private $rowNumber = 0;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function query()
    {
        $query = ProductDetail::join('product_prices', 'product_details.id', '=', 'product_prices.product_id')
            ->join('product_stocks', 'product_details.id', '=', 'product_stocks.product_id')
            ->select(
                'product_details.id',
                'product_details.code',
                'product_details.name',
                'product_stocks.available_stock',
                'product_stocks.damage_stock',
                'product_prices.supplier_price',
                'product_prices.selling_price'
            );

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('product_details.name', 'like', '%' . $this->search . '%')
                    ->orWhere('product_details.code', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderByRaw("CASE WHEN product_details.code LIKE 'G-%' THEN 1 ELSE 0 END ASC")
            ->orderBy('product_details.code', 'asc');
    }

    public function chunkSize(): int
    {
        return 100; // Process 100 rows at a time
    }

    public function headings(): array
    {
        return [
            'No.',
            'Product Code',
            'Product Name',
            'Available Stock',
            'Damage Stock',
            'Cost Price',
            'Selling Price'
        ];
    }

    public function map($product): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $product->code,
            $product->name,
            $product->available_stock,
            $product->damage_stock,
            number_format($product->supplier_price, 2),
            number_format($product->selling_price, 2)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as header
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => '3B5B0C'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ],
            // Style for all cells
            'A:G' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'CCCCCC'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Center align for numbers
            'A:A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // No.
            'D:G' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Stock and prices
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,  // No.
            'B' => 15, // Product Code
            'C' => 30, // Product Name
            'D' => 15, // Available Stock
            'E' => 15, // Damage Stock
            'F' => 18, // Cost
            'G' => 18, // Selling Price
        ];
    }
}
