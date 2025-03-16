<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $file = $_FILES['image']['tmp_name'];
    $imageSize = getimagesize($file);
    
    if ($imageSize === false) {
        die("Invalid image file.");
    }

    // Maximum size allowed (1000px)
    $maxSize = 1000;
    $width = $imageSize[0];
    $height = $imageSize[1];

    if ($width > $maxSize || $height > $maxSize) {
        $scale = min($maxSize / $width, $maxSize / $height);
        $newWidth = (int)($width * $scale);
        $newHeight = (int)($height * $scale);

        $srcImage = imagecreatefromjpeg($file);
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resizedImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        ob_start();
        imagejpeg($resizedImage);
        $imageData = ob_get_clean();
        imagedestroy($srcImage);
        imagedestroy($resizedImage);
    } else {
        $imageData = file_get_contents($file);
    }

    // Convert to Base64
    echo base64_encode($imageData);
} else {
    echo "No image uploaded.";
}
?>
