<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BusinessSettingController extends Controller
{
    public function index()
    {
        $settings = BusinessSetting::first();

        if (!$settings) {
            // Return default values if no settings exist yet
            return response()->json([
                'success' => true,
                'data' => [
                    'company_name' => '',
                    'currency' => 'LKR',
                    'country' => 'Sri Lanka',
                    'primary_color' => '#3B82F6',
                    'secondary_color' => '#1E40AF',
                    'template' => 'modern'
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    public function update(Request $request)
    {
        $settings = BusinessSetting::first();

        if (!$settings) {
            $settings = new BusinessSetting();
        }

        $data = $request->all();

        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($settings->logo_url) {
                $oldPath = str_replace(url('storage/'), '', $settings->logo_url);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('logo')->store('business', 'public');
            $data['logo_url'] = url('storage/' . $path);
        }

        $settings->fill($data);
        $settings->save();

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'data' => $settings
        ]);
    }
}
