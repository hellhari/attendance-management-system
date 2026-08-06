<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Admin | Resolution & Settings</title>
    <!-- Laravel CSRF Token for Secure API Requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Animation for custom toast notifications */
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        .toast-enter { animation: slideIn 0.3s forwards; }
        .toast-exit { animation: slideOut 0.3s forwards; }

        /* Loader animation */
        .loader {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            width: 14px;
            height: 14px;
            animation: spin 1s linear infinite;
            display: inline-block;
            vertical-align: middle;
            margin-right: 8px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 h-screen overflow-hidden flex">

    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col hidden md:flex">
        <div class="h-16 flex items-center px-6 border-b border-slate-200">
            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center mr-3 shadow-md border border-indigo-700">
                <i class="fa-solid fa-clock text-white text-sm"></i>
            </div>
            <span class="font-bold text-lg tracking-tight text-slate-900">AttenSync<span class="text-indigo-600">.</span></span>
        </div>
        <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition-colors">
                <i class="fa-solid fa-chart-pie w-6 text-center mr-2"></i> Dashboard
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg bg-indigo-50 text-indigo-700 transition-colors">
                <i class="fa-solid fa-triangle-exclamation w-6 text-center mr-2"></i> Resolutions
                <span class="ml-auto bg-indigo-600 text-white py-0.5 px-2 rounded-full text-xs" id="nav-badge">3</span>
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition-colors">
                <i class="fa-solid fa-file-invoice w-6 text-center mr-2"></i> Weekly Reports
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition-colors">
                <i class="fa-solid fa-gear w-6 text-center mr-2"></i> Settings
            </a>
        </nav>
        <div class="p-4 border-t border-slate-200">
            <div class="flex items-center">
                <img src="https://placehold.co/40x40/4f46e5/ffffff?text=HR" alt="HR Admin" class="w-9 h-9 rounded-full shadow-sm">
                <div class="ml-3">
                    <p class="text-sm font-medium text-slate-700">Hariram (Admin)</p>
                    <p class="text-xs text-slate-500">Cloud Engineer</p>
                </div>
            </div>
        </div>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        <!-- Topbar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 lg:px-10 z-10">
            <h1 class="text-xl font-semibold text-slate-800">System Configurations & Resolutions</h1>
            <button class="w-10 h-10 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 transition-colors relative">
                <i class="fa-regular fa-bell"></i>
                <span class="absolute top-2 right-2.5 w-2 h-2 bg-rose-500 rounded-full border-2 border-white"></span>
            </button>
        </header>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-6 lg:p-10">
            <div class="max-w-6xl mx-auto space-y-8">
                
                <section>
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="p-6 md:p-8 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-bold text-slate-800 flex items-center">
                                    <i class="fa-solid fa-stopwatch text-indigo-500 mr-2"></i> Arrival Grace Period
                                </h2>
                                <p class="text-sm text-slate-500 mt-1">Configure the buffer time before an employee is flagged as 'Late' past 9:30 AM.</p>
                            </div>
                            <div class="flex items-center space-x-3 bg-slate-50 p-2 rounded-xl border border-slate-100">
                                <label for="grace-period" class="text-sm font-medium text-slate-700 ml-2">Buffer Time:</label>
                                <div class="relative">
                                    <input type="number" id="grace-period-input" value="10" min="0" max="60" 
                                        class="w-20 pl-3 pr-8 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow">
                                    <span class="absolute right-3 top-2 text-sm text-slate-400 font-medium pointer-events-none">m</span>
                                </div>
                                <button id="save-grace-btn" onclick="saveGracePeriod()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center">
                                    <span>Save</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-slate-800">Pending Resolutions (16-Hour Cap)</h2>
                        <span class="text-sm font-medium text-slate-500 bg-slate-200 px-3 py-1 rounded-full"><i class="fa-solid fa-filter mr-1"></i> Needs Attention</span>
                    </div>

                    <!-- Dynamic Container for Resolution Cards -->
                    <div id="resolutions-container" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Cards injected by JS -->
                    </div>
                    
                    <!-- Empty State (Hidden by default) -->
                    <div id="empty-state" class="hidden flex flex-col items-center justify-center py-16 bg-white rounded-2xl border border-slate-200 border-dashed">
                        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-2xl mb-4">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">All caught up!</h3>
                        <p class="text-slate-500 mt-1">No missed checkouts require resolution right now.</p>
                    </div>
                </section>

            </div>
        </div>

        <!-- Custom Toast Notification Container -->
        <div id="toast-container" class="absolute bottom-6 right-6 flex flex-col gap-3 z-50"></div>
    </main>

    <script>
        // --- 1. APPLICATION STATE ---
        // We now start with an empty array. Data will be fetched from your actual Laravel Database.
        let currentScans = [];

        // Helper function to grab the Laravel CSRF security token
        const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // Fetch real data from Laravel Backend on load
        async function loadPendingResolutions() {
            try {
                // This calls your Laravel route (e.g., Route::get('/api/resolutions/pending', ...))
                const response = await fetch('/api/resolutions/pending');
                if (!response.ok) throw new Error('Failed to load data from database');
                
                const data = await response.json();
                // Assuming your backend returns an array of records
                currentScans = data.missedScans || data; 
                renderResolutions();
            } catch (error) {
                console.error("Database connection error:", error);
                // Optional: showToast("Could not load pending resolutions. Is the backend running?", "error");
            }
        }

        // --- 2. CORE FUNCTIONS ---

        // Render the Resolution Cards to the UI
        function renderResolutions() {
            const container = document.getElementById('resolutions-container');
            const emptyState = document.getElementById('empty-state');
            const navBadge = document.getElementById('nav-badge');
            
            container.innerHTML = '';
            
            const pendingScans = currentScans.filter(scan => scan.status === 'pending');
            
            navBadge.textContent = pendingScans.length;
            if(pendingScans.length === 0) navBadge.classList.add('hidden');

            if (pendingScans.length === 0) {
                container.classList.add('hidden');
                emptyState.classList.remove('hidden');
                emptyState.classList.add('flex');
                return;
            }

            pendingScans.forEach(scan => {
                const card = document.createElement('div');
                card.className = "bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition-shadow flex flex-col";
                card.innerHTML = `
                    <div class="p-5 border-b border-slate-100 flex justify-between items-start">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">
                                ${scan.employeeName.charAt(0)}
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800">${scan.employeeName} <span class="text-xs text-slate-500 font-normal ml-1">#${scan.empId}</span></h3>
                                <p class="text-xs font-medium text-rose-500"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Missed Checkout Flag</p>
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">${scan.date}</span>
                    </div>
                    
                    <div class="p-5 flex-1 bg-slate-50/50">
                        <div class="mb-4">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">System Log</p>
                            <p class="text-sm text-slate-700 bg-white p-3 rounded-lg border border-slate-200 font-mono text-xs">${scan.systemLog}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-1">Employee Explanation & Request</p>
                            <div class="bg-indigo-50 p-3 rounded-lg border border-indigo-100">
                                <p class="text-sm text-indigo-900 italic mb-2">"${scan.employeeReason}"</p>
                                <p class="text-sm font-semibold text-indigo-700 flex items-center">
                                    <i class="fa-solid fa-arrow-right-to-bracket mr-2"></i> Requested Out Time: <span class="ml-1 bg-white px-2 py-0.5 rounded border border-indigo-200">${scan.requestedOutTime}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border-t border-slate-100 bg-white flex gap-3">
                        <button onclick="handleResolution('${scan.id}', 'approved', this)" class="flex-1 bg-emerald-50 hover:bg-emerald-500 text-emerald-700 hover:text-white border border-emerald-200 hover:border-emerald-500 px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center justify-center">
                            <i class="fa-solid fa-check mr-2"></i> Approve
                        </button>
                        <button onclick="handleResolution('${scan.id}', 'denied', this)" class="flex-1 bg-white hover:bg-rose-50 text-rose-600 border border-slate-200 hover:border-rose-200 px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center justify-center">
                            <i class="fa-solid fa-xmark mr-2"></i> Deny
                        </button>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        // --- 3. REAL DATABASE CONNECTIONS (Laravel Backend) ---
        
        // Saving the Grace Period to Laravel via POST Request
        async function saveGracePeriod() {
            const btn = document.getElementById('save-grace-btn');
            const inputVal = document.getElementById('grace-period-input').value;
            
            // Set Loading state on the button
            const originalContent = btn.innerHTML;
            btn.innerHTML = `<span class="loader"></span> Saving...`;
            btn.disabled = true;

            try {
                // Real network request to your Laravel API
                const response = await fetch('/api/settings/grace-period', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken() // Secure the request
                    },
                    body: JSON.stringify({ grace_period: parseInt(inputVal) })
                });

                if (!response.ok) throw new Error('Failed to save settings to database');
                
                showToast(`Grace period updated to ${inputVal} minutes successfully.`, 'success');
            } catch (error) {
                console.error(error);
                showToast(`Database error: Failed to update settings.`, 'error');
            } finally {
                // Restore button visual
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }
        }

        // Approving/Denying a missed scan via POST Request to Laravel
        async function handleResolution(id, action, btnElement) {
            const originalContent = btnElement.innerHTML;
            btnElement.innerHTML = `<span class="loader border-t-current"></span> Processing`;
            btnElement.disabled = true;
            
            // Disable sibling button so user doesn't accidentally click both
            const siblingBtn = action === 'approved' ? btnElement.nextElementSibling : btnElement.previousElementSibling;
            if(siblingBtn) siblingBtn.disabled = true;

            try {
                // Real network request to update the record in MySQL
                const response = await fetch(`/api/resolutions/resolve/${id}`, {
                    method: 'POST', // Make sure you match this method in your web.php/api.php
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({ status: action })
                });

                if (!response.ok) throw new Error('Database update failed');

                // Update the local screen state to remove the card instantly
                const scanIndex = currentScans.findIndex(s => s.id === id);
                if(scanIndex > -1) {
                    currentScans[scanIndex].status = action;
                }

                if(action === 'approved') {
                    showToast(`Request ${id} approved. Shift hours recalculated.`, 'success');
                } else {
                    showToast(`Request ${id} denied. 16-hour cap remains.`, 'warning');
                }

                // Refresh the screen
                renderResolutions();

            } catch (error) {
                console.error(error);
                showToast(`Connection failed. Please check your backend.`, 'error');
                btnElement.innerHTML = originalContent;
                btnElement.disabled = false;
                if(siblingBtn) siblingBtn.disabled = false;
            }
        }

        // --- 4. CUSTOM TOAST NOTIFICATIONS (No more alert()!) ---
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            // Styling based on type
            let icon, bgColor, borderColor, textColor;
            if (type === 'success') {
                icon = '<i class="fa-solid fa-circle-check text-emerald-500"></i>';
                bgColor = 'bg-white';
                borderColor = 'border-emerald-200';
                textColor = 'text-slate-800';
            } else if (type === 'error') {
                icon = '<i class="fa-solid fa-circle-exclamation text-rose-500"></i>';
                bgColor = 'bg-rose-50';
                borderColor = 'border-rose-200';
                textColor = 'text-rose-900';
            } else if (type === 'warning') {
                icon = '<i class="fa-solid fa-triangle-exclamation text-amber-500"></i>';
                bgColor = 'bg-white';
                borderColor = 'border-amber-200';
                textColor = 'text-slate-800';
            }

            toast.className = `flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border ${borderColor} ${bgColor} ${textColor} toast-enter max-w-sm`;
            toast.innerHTML = `
                <div class="text-xl">${icon}</div>
                <p class="text-sm font-medium leading-tight">${message}</p>
            `;

            container.appendChild(toast);

            // Auto remove after 4 seconds
            setTimeout(() => {
                toast.classList.replace('toast-enter', 'toast-exit');
                setTimeout(() => toast.remove(), 300); // Wait for exit animation
            }, 4000);
        }

        // --- 5. INITIALIZATION ---
        document.addEventListener('DOMContentLoaded', () => {
            // Fetch real data on page load instead of using fake mock data
            loadPendingResolutions();
        });

    </script>
</body>
</html>