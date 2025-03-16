<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if it's a JPEG image
    if ($imageFileType !== "jpg" && $imageFileType !== "jpeg") {
        echo json_encode(["error" => "Only JPEG images are allowed."]);
        exit;
    }

    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

    // Dynamic max size adjustment
    $max_size = isset($_POST['max_size']) ? intval($_POST['max_size']) : 1000;
    list($width, $height) = getimagesize($target_file);

    if ($width > $max_size || $height > $max_size) {
        $scale = min($max_size / $width, $max_size / $height);
        $new_width = (int)($width * $scale);
        $new_height = (int)($height * $scale);

        $src = imagecreatefromjpeg($target_file);
        $dst = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagejpeg($dst, $target_file, 90);
    }

    // Convert Image to Base64
    $imageData = base64_encode(file_get_contents($target_file));

    echo json_encode(["image_base64" => $imageData]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image to Base64</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #4CAF50;
            color: white;
            padding: 15px 0;
            text-align: center;
        }

        h2, h3 {
            color: #333;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        form {
            margin-bottom: 20px;
        }

        input[type="file"], input[type="number"] {
            padding: 8px;
            margin: 10px 0;
            width: 100%;
            box-sizing: border-box;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        pre {
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }

        img {
            max-width: 100%;
            height: auto;
            display: block;
            margin-top: 20px;
        }
    </style>
    <script>
        function uploadImage() {
            let formData = new FormData(document.getElementById("uploadForm"));
            formData.append("max_size", document.getElementById("maxSize").value);

            fetch("", { method: "POST", body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        document.getElementById("outputText").innerText = data.image_base64;
                        document.getElementById("outputImage").src = "data:image/jpeg;base64," + data.image_base64;
                    }
                })
                .catch(error => console.error("Error:", error));
        }
    </script>
</head>
<body>
    <header>
        <h2>Upload a JPEG Image</h2>
    </header>

    <div class="container">
        <form id="uploadForm" enctype="multipart/form-data" onsubmit="event.preventDefault(); uploadImage();">
            <label for="image">Select Image:</label>
            <input type="file" name="image" accept="image/jpeg" required>

            <label for="maxSize">Max Size (px):</label>
            <input type="number" id="maxSize" name="max_size" value="1000" min="100" required>

            <button type="submit">Convert to Base64</button>
        </form>

        <h3>Base64 Encoded Image:</h3>
        <pre id="outputText"></pre>

        <h3>Uploaded Image:</h3>
        <img id="outputImage" />
    </div>
</body>
</html>
