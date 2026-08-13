<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Attendance Kiosk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50 h-screen flex flex-col items-center justify-center">

    <div class="bg-white p-8 rounded-xl shadow-lg text-center max-w-lg w-full">
        <h1 class="text-3xl font-bold mb-2 text-gray-800">Daily Kiosk</h1>
        <p class="text-gray-500 mb-6 text-sm">Please look at the camera to Check In or Check Out.</p>

        <!-- Camera Wrapper -->
        <div class="relative w-full h-[280px] bg-black rounded-lg overflow-hidden mb-6 border border-gray-300">
            <video id="video" class="w-full h-full object-cover" autoplay playsinline></video>
            
            <!-- Status Overlay -->
            <div id="status-overlay" class="hidden absolute bottom-0 left-0 w-full bg-black/80 p-3 flex flex-col items-center justify-center">
                <p id="status-text" class="text-white font-bold text-[13px] mb-1.5"></p>
                <p id="office-status" class="text-xs font-semibold flex items-center"></p>
            </div>
        </div>

        <button id="scan-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 px-6 rounded-lg w-full transition duration-300">
            Scan Face
        </button>
    </div>

    <script>
        const video = document.getElementById('video');
        const scanBtn = document.getElementById('scan-btn');
        const statusOverlay = document.getElementById('status-overlay');
        const statusText = document.getElementById('status-text');
        const officeStatus = document.getElementById('office-status');

        let currentLat = null;
        let currentLng = null;
        let currentAddress = "Location fetched";
        let isInsideOffice = false;

        const officeLat = 12.9715987; 
        const officeLng = 77.5945627;
        const allowedRadius = 200; 

        function getDistance(lat1, lon1, lat2, lon2) {
            const R = 6371e3; 
            const p1 = lat1 * Math.PI/180;
            const p2 = lat2 * Math.PI/180;
            const dp = (lat2-lat1) * Math.PI/180;
            const dl = (lon2-lon1) * Math.PI/180;
            const a = Math.sin(dp/2) * Math.sin(dp/2) + Math.cos(p1) * Math.cos(p2) * Math.sin(dl/2) * Math.sin(dl/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(async (position) => {
                currentLat = position.coords.latitude;
                currentLng = position.coords.longitude;
                
                const distance = getDistance(currentLat, currentLng, officeLat, officeLng);
                isInsideOffice = distance <= allowedRadius;

                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${currentLat}&lon=${currentLng}`);
                    const data = await res.json();
                    currentAddress = data.display_name;
                } catch(e) {}
            }, (error) => {
                console.log("Location access denied.");
            });
        }

        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => { video.srcObject = stream; })
            .catch(err => { console.log("Camera error"); });

        scanBtn.addEventListener('click', async () => {
            scanBtn.disabled = true;
            scanBtn.textContent = 'Matching...';
            scanBtn.classList.add('opacity-70', 'cursor-not-allowed');
            statusOverlay.classList.add('hidden');

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            const imageData = canvas.toDataURL('image/jpeg'); 

            try {
                const response = await fetch('/scan-face', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ 
                        image: imageData,
                        latitude: currentLat,
                        longitude: currentLng,
                        address: currentAddress
                    })
                });

                const result = await response.json();
                statusOverlay.classList.remove('hidden');
                
                if (result.success) {
                    statusText.textContent = `Face Verified! Check-IN successful for Employee ID: ${result.emp_id || '112'}`;
                    
                    if (isInsideOffice) {
                        officeStatus.className = "text-green-400 text-xs font-semibold flex items-center tracking-wide";
                        officeStatus.innerHTML = '<i class="fa-solid fa-location-dot mr-1.5"></i> Inside Office';
                    } else {
                        officeStatus.className = "text-rose-400 text-xs font-semibold flex items-center tracking-wide";
                        officeStatus.innerHTML = '<i class="fa-solid fa-location-dot mr-1.5"></i> Outside Office';
                    }
                } else {
                    statusText.textContent = result.message || "Face not recognized.";
                    officeStatus.innerHTML = "";
                }
            } catch (error) {
                statusOverlay.classList.remove('hidden');
                statusText.textContent = 'Network error. Please try again.';
                officeStatus.innerHTML = "";
            }

            setTimeout(() => {
                scanBtn.disabled = false;
                scanBtn.textContent = 'Scan Face';
                scanBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                statusOverlay.classList.add('hidden');
            }, 4000);
        });
    </script>
</body>
</html>