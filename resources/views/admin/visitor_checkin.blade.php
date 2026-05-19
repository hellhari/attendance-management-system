@extends('layouts.master')

@section('content')

<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert" style="background-color: #4EA749; color: white; border: none;">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true" style="color: white;">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card mt-3" style="border-top: 3px solid #1973B8;">
                <div class="card-header" style="background-color: #1973B8; color: white;">
                    <h3 class="card-title">New Visitor Check-In</h3>
                </div>

                <form action="{{ route('visitor.store') }}" method="POST" id="checkinForm">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Visitor Name *</label>
                                <input type="text" name="name" id="v_name" class="form-control" required placeholder="Enter name">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Company / Organization</label>
                                <input type="text" name="company" id="v_company" class="form-control" placeholder="Company name">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Person to Meet *</label>
                                <input type="text" name="person_to_meet" class="form-control" required placeholder="Who are they meeting?">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Purpose of Visit *</label>
                                <input type="text" name="purpose" class="form-control" required placeholder="e.g. Meeting, Delivery">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Phone Number *</label>
                                <input type="text" name="phone" id="v_phone" class="form-control" required placeholder="Contact number">
                            </div>
                            
                            <div class="col-md-6 form-group">
                                <label>Identity Verification</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <select name="id_type" id="v_id_type" class="form-control" style="border-top-right-radius:0; border-bottom-right-radius:0; background:#f8f9fa;">
                                            <option value="Aadhar">Aadhar</option>
                                            <option value="Driving License">Driving License</option>
                                            <option value="Voter ID">Voter ID</option>
                                            <option value="PAN Card">PAN Card</option>
                                        </select>
                                    </div>
                                    <input type="text" name="id_number" id="v_id_no" class="form-control" placeholder="ID Number">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-right">
                        <button type="submit" class="btn" style="background:#4EA749; color:white; padding:10px 40px; font-weight:bold;">
                            Confirm Check-In
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection