<?php
if (isset($_POST['submit'])) {
    $file = $_FILES['video_file'];

    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileDestination = 'uploads/' . $fileName;

    // Move the file from temp storage to your pulse/uploads folder
    if (move_uploaded_file($fileTmpName, $fileDestination)) {
        echo "Success! Video saved to uploads folder.";
        echo "<br><a href='index.php'>Go back to Editor</a>";
    } else {
        echo "Error: Upload failed.";
    }
}
?>