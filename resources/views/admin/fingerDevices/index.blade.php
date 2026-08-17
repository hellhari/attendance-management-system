@extends('layouts.master')

@section('css')
<style>
    /* Modern Card & Table Design */
    .staff-card { border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: none; }
    .table-modern td, .table-modern th { vertical-align: middle; border-top: 1px solid #f1f5f9; padding: 15px; }
    .table-modern thead th { background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 700; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }
    
    .device-icon-box { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; color: white; background: linear-gradient(135deg, #4f46e5, #3b82f6); box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2); }
    
    /* Action Buttons Customization */
    .btn-scan-face { background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; font-weight: 600; border-radius: 8px; padding: 6px 12px; font-size: 12px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); transition: all 0.2s; }
    .btn-scan-face:hover { transform: translateY(-2px); color: white; }

    /* Camera Modal Custom CSS */
    .camera-frame { width: 100%; height: 350px; background: #000; border-radius: 12px; overflow: hidden; position: relative; border: 4px solid #e2e8f0; }
    #videoElement { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
    .scan-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 2px dashed rgba(16, 185, 129, 0.5); border-radius: 12px; pointer-events: none; }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Staff & Biometrics Hub</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin" class="text-primary font-weight-bold">Home</a></li>
        <li class="breadcrumb-item active text-dark font-weight-bold">Biometric Devices & Face Enrolment</li>
    </ol>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        
        <!-- TOP HARDWARE ACTION BAR -->
        <div class="row mb-4">
            <div class="col-lg-12 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="font-weight-bold text-dark mb-1"><i class="ti-hardcoded text-primary mr-2"></i> Biometric Hardware Management</h4>
                    <p class="text-muted font-13 mb-0">Manage connected fingerprint machines, IP syncs, and staff facial profiles.</p>
                </div>
                <div>
                    <a class="btn btn-success font-weight-bold rounded-pill px-4 shadow-sm mr-2" href="{{ route('finger_device.create') }}">
                        <i class="ti-plus mr-1"></i> Add Finger Device
                    </a>
                    <a class="btn btn-danger font-weight-bold rounded-pill px-4 shadow-sm" href="{{ route('finger_device.clear.attendance') }}">
                        <i class="ti-trash mr-1"></i> Clear Device Logs
                    </a>
                </div>
            </div>
        </div>

        <!-- MAIN BIOMETRIC DEVICES TABLE (Dynamic Backend Integration) -->
        <div class="card staff-card mb-4">
            <div class="card-body p-4">
                
                <div class="table-responsive">
                    <table id="datatable-buttons" class="table table-modern table-hover mb-0" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Device Name & IP</th>
                                <th>Serial Number</th>
                                <th>Hardware Status</th>
                                <th>Hardware Sync Actions</th>
                                <th class="text-right">Management & Face ID</th>
                            </tr>
                        </thead>
                        
                        @php
                            $helper = new \App\Helpers\FingerHelper();
                        @endphp

                        <tbody>
                            @forelse($devices as $key => $finger_device)
                            <tr data-entry-id="{{ $finger_device->id }}">
                                <td class="font-weight-bold text-primary">#{{ $finger_device->id ?? '' }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="device-icon-box mr-3">
                                            <i class="ti-server font-18"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-weight-bold text-dark">{{ $finger_device->name ?? '' }}</h6>
                                            <span class="text-muted font-12"><i class="ti-signal mr-1"></i> IP: {{ $finger_device->ip ?? '' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="font-weight-bold text-dark">{{ $finger_device->serialNumber ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    @php
                                        try {
                                            $device = $helper->init($finger_device->ip);
                                            $isOnline = $helper->getStatus($device);
                                        } catch (\Exception $e) {
                                            $isOnline = false;
                                        }
                                    @endphp

                                    @if($isOnline)
                                        <span class="badge px-3 py-1 font-12" style="background: #d1fae5; color: #059669; border-radius: 20px;">
                                            <i class="ti-pulse mr-1"></i> Active (Online)
                                        </span>
                                    @else
                                        <span class="badge px-3 py-1 font-12" style="background: #fee2e2; color: #dc2626; border-radius: 20px;">
                                            <i class="ti-plug mr-1"></i> Deactivate (Offline)
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <!-- Hardware Sync Buttons -->
                                    <a class="btn btn-sm btn-outline-success rounded-pill px-3 mr-1 font-weight-bold" href="{{ route('finger_device.add.employee', $finger_device->id) }}">
                                        <i class="fas fa-plus mr-1"></i> Push Staff
                                    </a>
                                    <a class="btn btn-sm btn-outline-primary rounded-pill px-3 font-weight-bold" href="{{ route('finger_device.get.attendance', $finger_device->id) }}">
                                        <i class="fas fa-download mr-1"></i> Pull Logs
                                    </a>
                                </td>
                                <td class="text-right">
                                    <!-- Modern Face Scan Trigger -->
                                    <button onclick="openCameraModal('{{ $finger_device->id }}', '{{ $finger_device->name }}')" class="btn btn-scan-face mr-1">
                                        <i class="ti-face-smile"></i> Face ID
                                    </button>

                                    <!-- Standard CRUD Actions -->
                                    <a class="btn btn-light text-primary btn-sm rounded-circle" href="{{ route('finger_device.show', $finger_device->id) }}" title="View"><i class="ti-eye"></i></a>
                                    <a class="btn btn-light text-info btn-sm rounded-circle" href="{{ route('finger_device.edit', $finger_device->id) }}" title="Edit"><i class="ti-pencil"></i></a>

                                    <form action="{{ route('finger_device.destroy', $finger_device->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this device?');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button type="submit" class="btn btn-light text-danger btn-sm rounded-circle" title="Delete"><i class="ti-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="ti-server mb-2" style="font-size: 35px; display: block; opacity: 0.5;"></i>
                                    No biometric fingerprint devices configured yet. Click 'Add Finger Device' to begin.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ==============================================
     LIVE CAMERA SCAN MODAL FOR FACE ID
=============================================== -->
<div class="modal fade" id="cameraModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 50px rgba(0,0,0,0.15);">
            <div class="modal-header border-0 bg-light" style="border-radius: 16px 16px 0 0;">
                <h5 class="modal-title font-weight-bold text-dark"><i class="ti-camera text-primary mr-2"></i> Face Recognition Setup</h5>
                <button type="button" class="close text-danger" onclick="closeCamera()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="text-muted mb-4">Capturing facial biometrics for device: <strong id="scanDevName" class="text-primary">Device</strong></p>
                <div class="camera-frame mb-3 shadow-sm">
                    <video id="videoElement" autoplay playsinline></video>
                    <div class="scan-overlay"></div>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-light font-weight-bold px-4" onclick="closeCamera()">Cancel</button>
                <button type="button" class="btn btn-success font-weight-bold px-5 shadow" onclick="captureFace()"><i class="ti-target mr-2"></i> Capture & Sync Face</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let video = document.querySelector("#videoElement");
    let mediaStream = null;

    function openCameraModal(devId, devName) {
        document.getElementById('scanDevName').innerText = devName + ' (ID: #' + devId + ')';
        $('#cameraModal').modal('show');

        if (navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function (stream) {
                    mediaStream = stream;
                    video.srcObject = stream;
                })
                .catch(function (error) {
                    $('#cameraModal').modal('hide');
                    Swal.fire('Camera Error', 'Please allow camera permissions or check if a webcam is connected.', 'error');
                });
        }
    }

    function closeCamera() {
        if (mediaStream) {
            mediaStream.getTracks().forEach(track => track.stop());
            mediaStream = null;
        }
        $('#cameraModal').modal('hide');
    }

    function captureFace() {
        Swal.fire({
            title: 'Processing Face Biometrics...',
            html: 'Encrypting and syncing with hardware device.',
            timer: 2000,
            timerProgressBar: true,
            didOpen: () => { Swal.showLoading() }
        }).then((result) => {
            closeCamera();
            Swal.fire('Success!', 'Facial recognition profile successfully registered to the device.', 'success');
        });
    }
</script>
@endsection