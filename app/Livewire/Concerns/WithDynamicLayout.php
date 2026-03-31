<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;

trait WithDynamicLayout
{
    /**
     * Get the layout based on the authenticated user's role.
     *
     * @return string
     */
    public function getLayoutProperty()
    {
        if (!Auth::check()) {
            return 'components.layouts.app';
        }

        $user = Auth::user();

        return match ($user->role) {
            'admin' => 'components.layouts.admin',
            'staff' => 'components.layouts.staff',
            default => 'components.layouts.app',
        };
    }

    /**
     * Public method to get layout (for IDE support).
     *
     * @return string
     */
    public function layout()
    {
        return $this->layout;
    }

    /**
     * Boot the trait and set the layout dynamically.
     */
    public function bootWithDynamicLayout()
    {
        // This method is called automatically by Livewire when the trait is used
    }
}
