<!DOCTYPE html>
<html>
<head>
    <title>Face Capture</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            text-align: center;
            width: 450px;
        }

        h2 {
            margin-bottom: 15px;
            color: #333;
        }

        video {
            width: 100%;
            border-radius: 10px;
            border: 2px solid #ddd;
        }

        .btn {
            margin-top: 15px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn:hover {
            background: #0056b3;
        }

        .status {
            margin-top: 10px;
            font-size: 14px;
            color: green;
        }

        .pulse {
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>

<body>

<div class="card">
    <h2>📸 Employee Face Capture</h2>

    <video id="video" autoplay></video>

    <button class="btn pulse" onclick="capture()">Capture Face</button>

    <div id="status" class="status"></div>

    <canvas id="canvas" style="display:none;"></canvas>
</div>

<script>
let video = document.getElementById('video');
let statusBox = document.getElementById('status');

// Start camera
navigator.mediaDevices.getUserMedia({ video: true })
.then(stream => {
    video.srcObject = stream;
})
.catch(err => {
    statusBox.innerHTML = "Camera access denied!";
    statusBox.style.color = "red";
});

function capture() {

    let canvas = document.getElementById('canvas');
    let context = canvas.getContext('2d');

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    context.drawImage(video, 0, 0);

    let image = canvas.toDataURL('image/jpeg');

    statusBox.innerHTML = "Processing...";

    fetch("{{ route('employees.capture.face', $employee->id) }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ image: image })
    })
    .then(res => res.json())
    .then(data => {

        if(data.status) {
            statusBox.innerHTML = "✔ Face captured successfully!";
            statusBox.style.color = "green";
        } else {
            statusBox.innerHTML = "❌ " + data.message;
            statusBox.style.color = "red";
        }

    })
    .catch(() => {
        statusBox.innerHTML = "Server error!";
        statusBox.style.color = "red";
    });
}
</script>

</body>
</html>