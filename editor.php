<?php
/* =========================================
   1. PHP BACKEND: HANDLE SAVING
   ================================********* */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database credentials
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "pulse"; 

    // Connect to DB
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

    // Get data from JavaScript
    $video_name = $conn->real_escape_string($_POST['video_name']);
    $start = $conn->real_escape_string($_POST['start']);
    $end = $conn->real_escape_string($_POST['end']);

    // Update the record
    // MAKE SURE you have a table named 'videos' with columns 'trim_start' and 'trim_end'
    $sql = "UPDATE videos SET trim_start='$start', trim_end='$end' WHERE filename='$video_name'";

    if ($conn->query($sql) === TRUE) {
        echo "Success: Video trim saved!";
    } else {
        echo "Error: " . $conn->error;
    }
    
    $conn->close();
    exit(); // Stop script here so we don't reload the HTML
}

/* =========================================
   2. PHP LOGIC: GET VIDEO TO EDIT
   ================================********* */
// Get video filename from URL (e.g., editor.php?video=dance.mp4)
$video_file = isset($_GET['video']) ? $_GET['video'] : 'default.mp4'; 
// Security Note: In a real app, validate that this file actually exists in your folder!
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pulse Video</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    
    <style>
        /* --- CSS STYLING (TikTok Theme) --- */
        :root { --bg: #121212; --panel: #1e1e1e; --red: #fe2c55; --text: #fff; }
        body { 
            background: var(--bg); color: var(--text); font-family: 'Montserrat', sans-serif;
            display: flex; flex-direction: column; align-items: center; min-height: 100vh; margin: 0;
        }

        /* Phone Container */
        .video-wrapper {
            position: relative; width: 350px; height: 600px; background: #000;
            border-radius: 12px; overflow: hidden; margin-top: 20px; border: 1px solid #333;
        }
        video { width: 100%; height: 100%; object-fit: cover; }

        /* Editor Controls */
        .editor-panel {
            width: 350px; background: var(--panel); padding: 20px;
            border-radius: 12px; margin-top: 20px;
        }
        
        .time-display {
            display: flex; justify-content: space-between; font-size: 0.8rem; 
            color: #aaa; font-weight: 700; margin-bottom: 10px;
        }
        .highlight { color: var(--red); }

        /* Timeline Track */
        .timeline {
            position: relative; height: 60px; background: #333; border-radius: 6px;
            margin-bottom: 20px; cursor: pointer;
            background-image: linear-gradient(90deg, #444 1px, transparent 1px);
            background-size: 10px 100%;
        }

        /* Active Red Zone */
        .active-zone {
            position: absolute; top: 0; bottom: 0; left: 0%; right: 0%;
            background: rgba(254, 44, 85, 0.2);
            border-top: 3px solid var(--red); border-bottom: 3px solid var(--red);
            pointer-events: none;
        }

        /* Handles */
        .handle {
            position: absolute; top: 0; bottom: 0; width: 20px; background: var(--red);
            cursor: col-resize; z-index: 10; display: flex; align-items: center; justify-content: center;
        }
        .handle::after { content: '||'; color: white; font-size: 8px; }
        .h-left { left: 0%; border-radius: 6px 0 0 6px; }
        .h-right { right: 0%; border-radius: 0 6px 6px 0; }

        /* Buttons */
        .btn-save {
            width: 100%; padding: 15px; background: var(--red); border: none;
            border-radius: 8px; color: white; font-weight: 900; cursor: pointer;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .btn-save:hover { opacity: 0.9; }
    </style>
</head>
<body>

    <h2>PULSE <span class="highlight">EDITOR</span></h2>

    <div class="video-wrapper">
        <video id="vid" src="uploads/<?php echo htmlspecialchars($video_file); ?>" playsinline loop muted></video>
    </div>

    <div class="editor-panel">
        <div class="time-display">
            <span>Start: <span id="sTime" class="highlight">0.00</span>s</span>
            <span>End: <span id="eTime" class="highlight">0.00</span>s</span>
        </div>

        <div class="timeline" id="track">
            <div class="active-zone" id="zone"></div>
            <div class="handle h-left" id="hL"></div>
            <div class="handle h-right" id="hR"></div>
        </div>

        <button class="btn-save" id="saveBtn">Save Changes</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const vid = document.getElementById('vid');
            const track = document.getElementById('track');
            const hL = document.getElementById('hL');
            const hR = document.getElementById('hR');
            const zone = document.getElementById('zone');
            
            let dur = 0, start = 0, end = 0;
            let dragL = false, dragR = false;

            // 1. Init
            vid.addEventListener('loadedmetadata', () => {
                dur = vid.duration; end = dur;
                document.getElementById('eTime').innerText = end.toFixed(2);
                vid.play();
            });

            // 2. Drag Logic
            track.addEventListener('mousedown', e => {
                if(e.target === hL) dragL = true;
                if(e.target === hR) dragR = true;
            });
            window.addEventListener('mouseup', () => { dragL = false; dragR = false; });
            
            window.addEventListener('mousemove', e => {
                if(!dragL && !dragR) return;
                const rect = track.getBoundingClientRect();
                let pct = ((e.clientX - rect.left) / rect.width) * 100;
                pct = Math.max(0, Math.min(100, pct));

                if(dragL) {
                    let rPct = parseFloat(hR.style.right) || 0;
                    if(pct < (100 - rPct - 5)) {
                        hL.style.left = pct + '%'; zone.style.left = pct + '%';
                        start = (pct/100) * dur; vid.currentTime = start;
                    }
                }
                if(dragR) {
                    let rPos = 100 - pct;
                    let lPos = parseFloat(hL.style.left) || 0;
                    if(rPos < (100 - lPos - 5)) {
                        hR.style.right = rPos + '%'; zone.style.right = rPos + '%';
                        end = (pct/100) * dur;
                    }
                }
                updateLabels();
            });

            function updateLabels() {
                document.getElementById('sTime').innerText = start.toFixed(2);
                document.getElementById('eTime').innerText = end.toFixed(2);
            }

            // 3. Loop Logic
            vid.addEventListener('timeupdate', () => {
                if(vid.currentTime >= end || vid.currentTime < start) {
                    vid.currentTime = start; vid.play();
                }
            });

            // 4. SAVE TO PHP (AJAX)
            document.getElementById('saveBtn').addEventListener('click', () => {
                const formData = new FormData();
                formData.append('video_name', "<?php echo $video_file; ?>");
                formData.append('start', start);
                formData.append('end', end);

                fetch('editor.php', { method: 'POST', body: formData })
                .then(res => res.text())
                .then(data => alert(data));
            });
        });
    </script>
</body>
</html>