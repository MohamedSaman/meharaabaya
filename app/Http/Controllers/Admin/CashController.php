<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashInHand;
use App\Models\POSSession;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CashController extends Controller
{
    public function updateCashInHand(Request $request)
    {
        $request->validate([
            'newCashInHand' => 'required|numeric|min:0',
        ]);

        $cashRecord = CashInHand::firstOrCreate(
            ['key' => 'cash in hand'],
            ['value' => 0]
        );

        $cashRecord->update(['value' => $request->newCashInHand]);

        // Create or get today's POS session
        $session = POSSession::getTodaySession(Auth::id());

        if (!$session) {
            $session = POSSession::openSession(Auth::id(), $request->newCashInHand);
        }

        // Check if request is AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cash-in-Hand updated successfully',
                'newValue' => $request->newCashInHand,
                'sessionId' => $session->id
            ]);
        }

        return redirect()->back();
    }

    public function checkPOSSession(Request $request)
    {
        // Check if there's a closed session for today
        $todayClosedSession = POSSession::where('user_id', Auth::id())
            ->whereDate('session_date', now()->toDateString())
            ->where('status', 'closed')
            ->first();

        // Check if there's an open session for today
        $openSession = POSSession::getTodaySession(Auth::id());

        // Only return closed = true if there's actually a closed session
        // If no session exists at all, return closed = false to allow access
        return response()->json([
            'closed' => $todayClosedSession ? true : false,
            'hasSession' => ($openSession || $todayClosedSession) ? true : false,
            'message' => $todayClosedSession ? 'POS register is already closed for today' : 'POS register is available'
        ]);
    }
}
