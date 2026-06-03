<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Attendance Kiosk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 h-screen flex flex-col items-center justify-center">

    <div class="bg-white p-8 rounded-lg shadow-xl text-center max-w-lg w-full">
        <h1 class="text-3xl font-bold mb-2 text-gray-800">Daily Kiosk</h1>
        <p class="text-gray-500 mb-6">Please look at the camera to Check In or Check Out.</p>

        <div class="relative w-full h-64 bg-black rounded-lg overflow-hidden mb-6 border-4 border-gray-200">
            <video id="video" class="w-full h-full object-cover" autoplay playsinline></video>
        </div>

        <div id="status-message" class="hidden p-4 mb-4 rounded font-semibold text-lg"></div>

        <button id="scan-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg w-full transition duration-300">
            Scan Face
        </button>
    </div>

    <script>
        const video = document.getElementById('video');
        const scanBtn = document.getElementById('scan-btn');
        const statusMsg = document.getElementById('status-message');

        // 1. Turn on the Webcam
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => { video.srcObject = stream; })
            .catch(err => {
                statusMsg.textContent = "Camera access denied. Please allow camera permissions.";
                statusMsg.className = "p-4 mb-4 rounded font-semibold text-lg bg-red-100 text-red-700";
                statusMsg.classList.remove('hidden');
            });

        // 2. Capture Image & Send to AWS/Controller
        scanBtn.addEventListener('click', async () => {
            // Disable button while processing
            scanBtn.disabled = true;
            scanBtn.textContent = "Matching Face...";
            scanBtn.classList.add('opacity-50', 'cursor-not-allowed');
            statusMsg.classList.add('hidden');

            // Take a snapshot from the video feed
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            const imageData = canvas.toDataURL('image/jpeg'); // Convert to Base64 String

            try {
                // Send the image to our new Route (/scan-face)
                const response = await fetch('/scan-face', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ image: imageData })
                });

                const result = await response.json();

                // Display the Success/Error Message from FaceController.php
                statusMsg.classList.remove('hidden');
                
                if (result.success) {
                    statusMsg.textContent = result.message; 
                    
                    // Toggle the colors based on the state!
                    if (result.state === 1) {
                        // CHECK-IN: Make it Green
                        statusMsg.className = "p-4 mb-4 rounded font-semibold text-lg bg-green-100 text-green-800 border border-green-300";
                    } else {
                        // CHECK-OUT: Make it Blue
                        statusMsg.className = "p-4 mb-4 rounded font-semibold text-lg bg-blue-100 text-blue-800 border border-blue-300";
                    }
                } else {
                    // ERROR / NO FACE MATCH: Make it Red
                    statusMsg.textContent = result.message || "Face not recognized.";
                    statusMsg.className = "p-4 mb-4 rounded font-semibold text-lg bg-red-100 text-red-800 border border-red-300";
                }
            } catch (error) {
                statusMsg.classList.remove('hidden');
                statusMsg.textContent = "Network error. Please try again.";
                statusMsg.className = "p-4 mb-4 rounded font-semibold text-lg bg-red-100 text-red-800 border border-red-300";
            }

            // Reset the button after 3 seconds so the next person can scan
            setTimeout(() => {
                scanBtn.disabled = false;
                scanBtn.textContent = "Scan Face";
                scanBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }, 3000);
        });
    </script>
</body>
</html>