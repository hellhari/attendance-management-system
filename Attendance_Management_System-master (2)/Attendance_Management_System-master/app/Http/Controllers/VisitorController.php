<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visitor; 
use Carbon\Carbon;

class VisitorController extends Controller
{
    /**
     * Display the list of visitors with time-range filtering and dashboard stats.
     */
    public function index(Request $request)
    {
        $query = Visitor::query();

        // Time-range filtering
        if ($request->has('range') && $request->range !== 'all') {
            $now = now();
            switch ($request->range) {
                case '1h':  $query->where('created_at', '>=', $now->subHour()); break;
                case '24h': $query->where('created_at', '>=', $now->subHours(24)); break;
                case '7d':  $query->where('created_at', '>=', $now->subDays(7)); break;
                case '30d': $query->where('created_at', '>=', $now->subDays(30)); break;
                case '6m':  $query->where('created_at', '>=', $now->subMonths(6)); break;
                case '12m': $query->where('created_at', '>=', $now->subMonths(12)); break;
            }
        }

        // Search functionality
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('id_number', 'like', '%' . $request->search . '%');
            });
        }

        // Sort: 'Inside' visitors first, then newest records
        $visitors = $query->orderByRaw("status = 'Inside' DESC")
                          ->orderBy('created_at', 'desc')
                          ->get();

        // Dashboard Totals
        $totalVisitors = Visitor::count();
        $currentlyInside = Visitor::where('status', 'Inside')->count();
        $totalExits = Visitor::where('status', 'Checked Out')->count();

        return view('admin.visitor_index', compact('visitors', 'totalVisitors', 'currentlyInside', 'totalExits'));
    }

    /**
     * Store the visitor check-in data (Manual Entry Only).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'person_to_meet' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'purpose' => 'required|string|max:255',
            'id_type' => 'required',
            'id_number' => 'required',
        ]);

        Visitor::create([
            'name'           => $request->name,
            'company'        => $request->company,
            'person_to_meet' => $request->person_to_meet,
            'purpose'        => $request->purpose ?? 'General Visit', // <--- Just add this line!
            'phone'          => $request->phone,
            'id_type'        => $request->id_type,
            'id_number'      => $request->id_number, 
            'check_in_time'  => Carbon::now(),
            'status'         => 'Inside',
        ]);

        return redirect()->route('admin.visitor_index')->with('success', 'Visitor checked in successfully!');
    }

    /**
     * Handle visitor Check-Out.
     */
    public function checkout($id)
    {
        $visitor = Visitor::find($id);

        if ($visitor) {
            $visitor->update([
                'check_out_time' => Carbon::now(), 
                'status'         => 'Checked Out'
            ]);

            return redirect()->back()->with('success', 'Visitor checked out successfully!');
        }

        return redirect()->back()->with('error', 'Visitor not found.');
    }

    /**
     * Generate an Excel/XLS Report for export.
     */
    public function downloadReport()
    {
        $visitors = Visitor::orderBy('created_at', 'desc')->get();
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="Visitor_Report_'.date('Y-m-d').'.xls"');

        echo "Visitor Name\tPhone Number\tCompany\tPerson to Meet\tPurpose\tID Type\tID Number\tCheck In\tCheck Out\tStatus\n";
        
        foreach ($visitors as $v) {
            $checkOut = $v->check_out_time ?? '---';
            echo "{$v->name}\t{$v->phone}\t{$v->company}\t{$v->person_to_meet}\t{$v->purpose}\t{$v->id_type}\t{$v->id_number}\t{$v->check_in_time}\t{$checkOut}\t{$v->status}\n";
        }
        exit;
    }
}