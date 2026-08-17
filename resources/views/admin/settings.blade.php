@extends('layouts.master')

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">System Settings</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin" class="text-primary font-weight-bold">Home</a></li>
        <li class="breadcrumb-item active text-dark font-weight-bold">Settings</li>
    </ol>
</div>
@endsection

@section('content')
<style>
    .setting-card { border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; }
    .setting-card .card-header { background: #f8fafc; border-bottom: 1px solid #e2e8f0; border-radius: 12px 12px 0 0 !important; }
    .setting-title { font-size: 16px; font-weight: 700; color: #1e293b; margin: 0; }
    .form-label { font-weight: 600; color: #475569; }
</style>

<div class="row">
    <!-- 1. Company Timings & Grace Period -->
    <div class="col-lg-6">
        <div class="card setting-card mb-4">
            <div class="card-header">
                <h4 class="setting-title"><i class="ti-time text-primary mr-2"></i> Shift Timings & Grace Period</h4>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Standard Shift Start</label>
                        <input type="time" class="form-control" value="09:30">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Standard Shift End</label>
                        <input type="time" class="form-control" value="18:30">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label text-danger">Arrival Grace Period (Minutes)</label>
                    <p class="text-muted font-13 mb-2">Buffer time before an employee is marked as 'Late'.</p>
                    <input type="number" class="form-control w-50" value="10">
                </div>
                <button class="btn btn-primary font-weight-bold mt-4" onclick="showToast()">Save Timings</button>
            </div>
        </div>
    </div>

    <!-- 2. Weekend & Working Days -->
    <div class="col-lg-6">
        <div class="card setting-card mb-4">
            <div class="card-header">
                <h4 class="setting-title"><i class="ti-calendar text-success mr-2"></i> Weekend Configuration</h4>
            </div>
            <div class="card-body p-4">
                <p class="text-muted font-14 mb-3">Select the default weekly off days for the company.</p>
                <div class="custom-control custom-checkbox mb-2">
                    <input type="checkbox" class="custom-control-input" id="checkSat">
                    <label class="custom-control-label font-weight-bold" for="checkSat">Saturday (Holiday)</label>
                </div>
                <div class="custom-control custom-checkbox mb-4">
                    <input type="checkbox" class="custom-control-input" id="checkSun" checked>
                    <label class="custom-control-label font-weight-bold" for="checkSun">Sunday (Holiday)</label>
                </div>
                <button class="btn btn-success font-weight-bold mt-2" onclick="showToast()">Update Calendar</button>
            </div>
        </div>
    </div>

    <!-- 3. Leave Policies -->
    <div class="col-lg-12">
        <div class="card setting-card mb-4">
            <div class="card-header">
                <h4 class="setting-title"><i class="ti-medall text-warning mr-2"></i> Annual Leave Policy</h4>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Casual Leaves (CL) per year</label>
                        <input type="number" class="form-control" value="12">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Medical Leaves (ML) per year</label>
                        <input type="number" class="form-control" value="6">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Minimum Hours for Full-Day</label>
                        <input type="number" class="form-control" value="8" placeholder="e.g., 8 Hours">
                    </div>
                </div>
                <button class="btn btn-warning text-dark font-weight-bold mt-3" onclick="showToast()">Save Leave Rules</button>
            </div>
        </div>
    </div>
</div>

<!-- Simple Toast Notification -->
<div id="success-toast" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none; position: fixed; bottom: 20px; right: 20px; z-index: 9999; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
    <strong><i class="ti-check mr-1"></i> SUCCESS!</strong> Settings saved successfully.
    <button type="button" class="close" onclick="document.getElementById('success-toast').style.display='none'">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<script>
    function showToast() {
        const toast = document.getElementById('success-toast');
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 3000);
    }
</script>
@endsection