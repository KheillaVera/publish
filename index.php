<!DOCTYPE html>
<html>
<head>
    <title>Pulse - Video Editor</title>
    <link rel="stylesheet" href="./assets/style.css/style.css">
</head>
<body>
    <div class="main-container">
        <img src="assets/style.css/logo.png.png" alt="Pulse Logo" style="width:100px;">
        <h2>Upload your clip to Pulse</h2>
        
        <form action="./upload.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="video_file" accept="video/mp4,video/mov">
            <button type="submit" name="submit">Upload Video</button>
        </form>
    </div>
</body>
</html>