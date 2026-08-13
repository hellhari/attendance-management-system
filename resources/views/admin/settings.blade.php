@extends('layouts.master')

@section('content')
<div class="row">
    <div class="col-12">
        
        <!-- Card 1: Arrival Grace Period -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body p-4">
                <h4 class="mt-0 header-title mb-2 text-primary">
                    <i class="ti-settings mr-2"></i> Arrival Grace Period
                </h4>
                <p class="text-muted mb-4 font-14">
                    Configure the buffer time before an employee is flagged as 'Late' past 9:30 AM.
                </p>
                
                <div class="d-flex align-items-center">
                    <label class="font-weight-bold mr-3 mb-0 text-dark">Buffer Time (minutes):</label>
                    <input type="number" id="grace-period-input" class="form-control text-center mr-3" value="10" style="width: 80px;">
                    <button class="btn btn-primary font-weight-bold" onclick="showToast()">Save Changes</button>
                </div>
            </div>
        </div>

        <!-- Card 2: Pending Resolutions -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h4 class="mt-0 header-title mb-4 text-warning">
                    <i class="ti-alert mr-2"></i> Pending Resolutions (16-Hour Cap)
                </h4>
                
                <div class="text-center py-5">
                    <i class="dripicons-checkmark text-success mb-3" style="font-size: 50px;"></i>
                    <h3 class="text-dark font-weight-bold">All caught up!</h3>
                    <p class="text-muted">No missed checkouts require resolution right now.</p>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Simple Toast Notification -->
<div id="success-toast" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none; position: fixed; bottom: 20px; right: 20px; z-index: 9999; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
    <strong>SUCCESS!</strong> Settings saved successfully.
    <button type="button" class="close" onclick="document.getElementById('success-toast').style.display='none'">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<script>
    function showToast() {
        const toast = document.getElementById('success-toast');
        toast.style.display = 'block';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 3000); // மறைவதற்கான நேரம் (3 நொடிகள்)
    }
</script>
@endsection