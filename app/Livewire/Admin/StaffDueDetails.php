<?php

namespace App\Livewire\Admin;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Staff Due Details')]
class StaffDueDetails extends Component
{
    use WithDynamicLayout;

    use WithPagination;
    
    public function render()
    {
        $staffDues = DB::table('sales')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->select(
                'users.id as user_id',
                'users.name',
                'users.email',
                'users.contact',
                DB::raw('SUM(sales.total_amount) as total_amount'),
                DB::raw('SUM(sales.due_amount) as due_amount'),
                DB::raw('SUM(sales.total_amount) - SUM(sales.due_amount) as collected_amount')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.contact')
            ->orderBy('total_amount', 'desc')
            ->paginate(10);

        return view('livewire.admin.staff-due-details', [
            'staffDues' => $staffDues
        ])->layout($this->layout);
    }
    
    // For CSV export
    public function exportToCSV()
    {
        $staffDues = DB::table('sales')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->select(
                'users.id as user_id',
                'users.name',
                'users.email',
                'users.contact',
                DB::raw('SUM(sales.total_amount) as total_amount'),
                DB::raw('SUM(sales.due_amount) as due_amount'),
                DB::raw('SUM(sales.total_amount) - SUM(sales.due_amount) as collected_amount')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.contact')
            ->orderBy('total_amount', 'desc')
            ->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="staff_dues_' . date('Y-m-d') . '.csv"',
        ];
        
        $callback = function() use ($staffDues) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, ['#', 'Staff Name', 'Email', 'Contact', 'Total Sales', 'Collected Amount', 'Due Amount', 'Collection Percentage']);
            
            // Add data rows
            foreach ($staffDues as $index => $staff) {
                $percentage = $staff->total_amount > 0 ? round(($staff->collected_amount / $staff->total_amount) * 100) : 100;
                
                fputcsv($file, [
                    $index + 1,
                    $staff->name,
                    $staff->email,
                    $staff->contact,
                    'Rs.' . number_format($staff->total_amount, 2),
                    'Rs.' . number_format($staff->collected_amount, 2),
                    'Rs.' . number_format($staff->due_amount, 2),
                    $percentage . '%'
                ]);
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }
    
    // For print functionality (we'll handle this with JavaScript in the view)
    public function printData()
    {
        // Trigger JavaScript print function from the frontend
        $this->dispatch('print-table');
    }
}
