<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Generate Trainer Evaluation Form</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="h-full flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Trainer Evaluation Generator</h1>

        @if (!$hasToken)
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
            <h2 class="text-yellow-800 font-semibold text-sm mb-2">Google Authentication Required</h2>
            <div class="text-xs text-yellow-800 space-y-2 mb-4">
                <p>To generate forms, you must configure Google OAuth:</p>
                <ol class="list-decimal pl-4 space-y-1">
                    <li>Go to <a href="https://console.cloud.google.com" target="_blank" class="underline hover:text-yellow-900">Google Cloud Console</a> and create a project.</li>
                    <li>Enable <strong>Google Forms API</strong> and <strong>Google Drive API</strong>.</li>
                    <li>Configure the <strong>OAuth Consent Screen</strong> (add your email to Audience/Test Users).</li>
                    <li>Create an <strong>OAuth 2.0 Client ID</strong> (Web application).</li>
                    <li>Add Authorized Redirect URI: <code class="bg-yellow-100 px-1 font-mono">http://localhost:42069/auth/google/callback</code></li>
                    <li>Download the JSON, rename it to <code class="bg-yellow-100 px-1 font-mono">oauth-credentials.json</code>, and save it in <code class="bg-yellow-100 px-1 font-mono">storage/app/private/</code>.</li>
                </ol>
            </div>
            <a href="{{ route('google.auth') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Login with Google
            </a>
        </div>
        @else
        <details class="mb-6 bg-white border border-gray-200 rounded-md shadow-sm">
            <summary class="px-4 py-3 font-semibold text-sm text-gray-700 cursor-pointer hover:bg-gray-50 flex justify-between items-center">
                <span>Google OAuth Setup Instructions</span>
            </summary>
            <div class="p-4 border-t border-gray-200 text-sm text-gray-600 space-y-3 bg-gray-50">
                <ol class="list-decimal pl-5 space-y-2">
                    <li>Go to <a href="https://console.cloud.google.com" target="_blank" class="text-indigo-600 hover:underline">Google Cloud Console</a> and create a project.</li>
                    <li>Enable the <strong>Google Forms API</strong> and <strong>Google Drive API</strong>.</li>
                    <li>Configure the <strong>OAuth Consent Screen</strong>. Add your email address under <strong>Test users</strong>.</li>
                    <li>Go to Credentials &rarr; Create Credentials &rarr; <strong>OAuth client ID</strong>.</li>
                    <li>Choose <strong>Web application</strong>. Under Authorized redirect URIs, add:<br>
                        <code class="bg-gray-200 px-1 py-0.5 rounded text-xs font-mono text-gray-800">http://localhost:42069/auth/google/callback</code>
                    </li>
                    <li>Download the JSON file, rename it to <code class="bg-gray-200 px-1 py-0.5 rounded text-xs font-mono text-gray-800">oauth-credentials.json</code> and place it in:<br>
                        <code class="bg-gray-200 px-1 py-0.5 rounded text-xs font-mono text-gray-800">storage/app/private/oauth-credentials.json</code>
                    </li>
                </ol>
            </div>
        </details>

        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-md flex justify-between items-center shadow-sm">
            <span class="text-green-800 text-sm font-medium flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Authenticated with Google
            </span>
            <a href="{{ route('google.auth') }}" class="text-xs text-indigo-700 hover:text-indigo-900 font-medium px-3 py-1 bg-green-100 rounded-full hover:bg-green-200 transition-colors">Re-authenticate</a>
        </div>
        @endif

        <form id="generate-form" class="space-y-4">
            <div>
                <label for="trainer_name" class="block text-sm font-medium text-gray-700">Trainer Name</label>
                <select name="trainer_name" id="trainer_name" required
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                    <option value="" disabled selected>Select a Trainer</option>
                    <option value="Henry N. Ong II">Henry N. Ong II</option>
                    <option value="Dianna A. Azores">Dianna A. Azores</option>
                    <option value="Joandlyn Mariano">Joandlyn Mariano</option>
                    <option value="Jan Panado">Jan Panado</option>
                    <option value="Stephen Singer">Stephen Singer</option>
                    <option value="Michael S. Riparip">Michael S. Riparip</option>
                    <option value="Richelle Bondoc">Richelle Bondoc</option>
                    <option value="Jansen Ang">Jansen Ang</option>
                    <option value="J.R. De Guzman">J.R. De Guzman</option>
                    <option value="James Santos">James Santos</option>
                    <option value="Elise Aguilar">Elise Aguilar</option>
                    <option value="Alfie Apodaca">Alfie Apodaca</option>
                    <option value="Monie Ferran">Monie Ferran</option>
                    <option value="Wally Lim">Wally Lim</option>
                    <option value="Monika Ortega">Monika Ortega</option>
                    <option value="Danniel Libor">Danniel Libor</option>
                    <option value="Ced Rick Flores">Ced Rick Flores</option>
                    <option value="Engr. Reynan Gelera">Engr. Reynan Gelera</option>
                    <option value="Ellison Bartolome">Ellison Bartolome</option>
                    <option value="Sil Silvestre">Sil Silvestre</option>
                    <option value="Vincent Valenzuela">Vincent Valenzuela</option>
                    <option value="Eizel Arianne Jugueta">Eizel Arianne Jugueta</option>
                    <option value="Bienvenido Basal">Bienvenido Basal</option>
                    <option value="James Bañez">James Bañez</option>
                    <option value="Josef Amarra">Josef Amarra</option>
                    <option value="Justin V. Macugay">Justin V. Macugay</option>
                    <option value="Paolo Bertola">Paolo Bertola</option>
                    <option value="Muriel Orobia">Muriel Orobia</option>
                    <option value="Roman Marcus Abad">Roman Marcus Abad</option>
                    <option value="Ruel G. Nopal">Ruel G. Nopal</option>
                    <option value="Carlo L. Pantilano">Carlo L. Pantilano</option>
                    <option value="Kyeth Harryz C. Pallanan">Kyeth Harryz C. Pallanan</option>
                    <option value="Harold B. Tomas">Harold B. Tomas</option>
                    <option value="Sydel Palinlin">Sydel Palinlin</option>
                    <option value="Ritche Villarico">Ritche Villarico</option>
                    <option value="Kirsten Nicole Mendoza">Kirsten Nicole Mendoza</option>
                    <option value="John Vincent Paragna">John Vincent Paragna</option>
                </select>
            </div>

            <div>
                <label for="course_name" class="block text-sm font-medium text-gray-700">Course Name</label>
                <select name="course_name" id="course_name" required
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                    <option value="" disabled selected>Select a Course</option>
                    <option value="Adobe Photoshop CC 2026 — Essentials to Advanced">Adobe Photoshop CC 2026 — Essentials to Advanced</option>
                    <option value="Adobe Lightroom CC 2026">Adobe Lightroom CC 2026</option>
                    <option value="Adobe Illustrator CC 2026 — Essentials to Advanced">Adobe Illustrator CC 2026 — Essentials to Advanced</option>
                    <option value="Adobe InDesign CC 2026 — Essentials to Advanced">Adobe InDesign CC 2026 — Essentials to Advanced</option>
                    <option value="CorelDRAW 2025 — Essentials to Advanced">CorelDRAW 2025 — Essentials to Advanced</option>
                    <option value="Adobe Animate CC">Adobe Animate CC</option>
                    <option value="HTML, CSS & Tailwind">HTML, CSS & Tailwind</option>
                    <option value="JavaScript Essentials">JavaScript Essentials</option>
                    <option value="React JS Essentials">React JS Essentials</option>
                    <option value="jQuery Essentials">jQuery Essentials</option>
                    <option value="PHP with MySQL">PHP with MySQL</option>
                    <option value="Laravel 12 PHP Framework">Laravel 12 PHP Framework</option>
                    <option value="CodeIgniter PHP Framework">CodeIgniter PHP Framework</option>
                    <option value="Ruby On Rails">Ruby On Rails</option>
                    <option value="WordPress CMS">WordPress CMS</option>
                    <option value="Mastering WordPress with Elementor">Mastering WordPress with Elementor</option>
                    <option value="Joomla CMS">Joomla CMS</option>
                    <option value="Drupal CMS">Drupal CMS</option>
                    <option value="Adobe Premiere Pro CC 2026 — Essentials to Advanced">Adobe Premiere Pro CC 2026 — Essentials to Advanced</option>
                    <option value="Adobe After Effects CC 2026 — Essentials to Advanced">Adobe After Effects CC 2026 — Essentials to Advanced</option>
                    <option value="DaVinci Resolve 18 — Video & Film Editing">DaVinci Resolve 18 — Video & Film Editing</option>
                    <option value="Indie Film Making">Indie Film Making</option>
                    <option value="Digital Marketing Fundamentals">Digital Marketing Fundamentals</option>
                    <option value="Search Engine Optimization (SEO)">Search Engine Optimization (SEO)</option>
                    <option value="Social Media Marketing (SMM)">Social Media Marketing (SMM)</option>
                    <option value="Google Ads (PPC) Certification">Google Ads (PPC) Certification</option>
                    <option value="Basic Networking: Wired & Wireless">Basic Networking: Wired & Wireless</option>
                    <option value="CCTV Security Surveillance — Install, Configure & Manage">CCTV Security Surveillance — Install, Configure & Manage</option>
                    <option value="Fire Detection & Alarm System (FDAS) — Conventional & Addressable">Fire Detection & Alarm System (FDAS) — Conventional & Addressable</option>
                    <option value="Fiber Optics Installer / Technician">Fiber Optics Installer / Technician</option>
                    <option value="Windows Server 2022: Administration">Windows Server 2022: Administration</option>
                    <option value="Windows Server 2022: Active Directory">Windows Server 2022: Active Directory</option>
                    <option value="Python Programming Essentials">Python Programming Essentials</option>
                    <option value="Django Python Framework">Django Python Framework</option>
                    <option value="Python for Data Science">Python for Data Science</option>
                    <option value="Java Programming Essentials">Java Programming Essentials</option>
                    <option value="AWS Cloud Practitioner">AWS Cloud Practitioner</option>
                    <option value="AWS Solutions Architecting">AWS Solutions Architecting</option>
                    <option value="Microsoft Azure Fundamentals (Exam: AZ-900)">Microsoft Azure Fundamentals (Exam: AZ-900)</option>
                    <option value="Microsoft Azure Administrator (Exam: AZ-104)">Microsoft Azure Administrator (Exam: AZ-104)</option>
                    <option value="ITIL v4 Foundation — IT Service Management">ITIL v4 Foundation — IT Service Management</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" name="start_date" id="start_date" required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" name="end_date" id="end_date" required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>

            <button type="submit" id="submit-btn" @if(!$hasToken) disabled @endif
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                Generate Form
            </button>
        </form>

        <!-- Loading State -->
        <div id="loading" class="hidden mt-6 text-center text-sm text-gray-500">
            Generating form, please wait...
        </div>

        <!-- Results Section -->
        <div id="results" class="hidden mt-6 p-4 rounded-md bg-green-50">
            <h3 class="text-sm font-medium text-green-800 mb-4">Form Generated Successfully!</h3>

            <div class="flex justify-center mb-4">
                <img id="qr-code" src="" alt="QR Code" class="w-32 h-32 border border-gray-200 rounded shadow-sm">
            </div>

            <div class="mt-2 text-sm text-green-700 space-y-3">
                <div>
                    <span class="font-medium">View URL (Share this):</span>
                    <a href="#" id="view-url" target="_blank" class="text-indigo-600 hover:text-indigo-500 block truncate"></a>
                </div>
                <div>
                    <span class="font-medium">Edit URL (Modify form):</span>
                    <a href="#" id="edit-url" target="_blank" class="text-indigo-600 hover:text-indigo-500 block truncate"></a>
                </div>
                <div>
                    <span class="font-medium">Drive Folder:</span>
                    <a href="#" id="folder-url" target="_blank" class="text-indigo-600 hover:text-indigo-500 block truncate"></a>
                </div>
            </div>
        </div>

        <!-- Error Section -->
        <div id="error" class="hidden mt-6 p-4 rounded-md bg-red-50">
            <div class="text-sm text-red-700" id="error-message"></div>
        </div>
    </div>

    <script>
        document.getElementById('generate-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = e.target;
            const submitBtn = document.getElementById('submit-btn');
            const loading = document.getElementById('loading');
            const results = document.getElementById('results');
            const error = document.getElementById('error');
            const trainerName = document.getElementById('trainer_name').value;
            const courseName = document.getElementById('course_name').value;
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            // Reset UI
            submitBtn.disabled = true;
            loading.classList.remove('hidden');
            results.classList.add('hidden');
            error.classList.add('hidden');

            try {
                const response = await fetch('/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        trainer_name: trainerName,
                        course_name: courseName,
                        start_date: startDate,
                        end_date: endDate
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Failed to generate form');
                }

                // Show results
                document.getElementById('view-url').href = data.view_url;
                document.getElementById('view-url').textContent = data.view_url;

                document.getElementById('edit-url').href = data.edit_url;
                document.getElementById('edit-url').textContent = data.edit_url;

                document.getElementById('folder-url').href = data.folder_url;
                document.getElementById('folder-url').textContent = "Open in Google Drive";

                // Generate QR Code via API
                document.getElementById('qr-code').src = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(data.view_url);

                results.classList.remove('hidden');
                form.reset();
            } catch (err) {
                // Show error
                document.getElementById('error-message').textContent = err.message;
                error.classList.remove('hidden');
            } finally {
                submitBtn.disabled = false;
                loading.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
