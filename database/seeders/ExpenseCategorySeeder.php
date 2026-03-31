<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ExpenseCategory;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $expenseCategories = [
            // Daily Expenses
            ['expense_category' => 'Daily Expenses', 'type' => 'Snacks'],
            ['expense_category' => 'Daily Expenses', 'type' => 'Transportation'],
            ['expense_category' => 'Daily Expenses', 'type' => 'Meals'],
            ['expense_category' => 'Daily Expenses', 'type' => 'Supplies'],
            ['expense_category' => 'Daily Expenses', 'type' => 'Miscellaneous'],
            
            // Monthly Expenses
            ['expense_category' => 'Monthly Expenses', 'type' => 'Electricity Bill'],
            ['expense_category' => 'Monthly Expenses', 'type' => 'Water Bill'],
            ['expense_category' => 'Monthly Expenses', 'type' => 'Rent'],
            ['expense_category' => 'Monthly Expenses', 'type' => 'Internet Bill'],
            ['expense_category' => 'Monthly Expenses', 'type' => 'Salaries'],
            ['expense_category' => 'Monthly Expenses', 'type' => 'Maintenance'],
        ];

        foreach ($expenseCategories as $category) {
            ExpenseCategory::create($category);
        }
    }
}
