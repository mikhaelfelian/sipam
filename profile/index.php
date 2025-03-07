<?php
require_once '../config/connect.php';
require_once '../config/config.php';
require_once '../config/flash_message.php';
require_once '../config/auth.php';

startSession();

// Check if user is logged in
checkLogin();

$settings = getSettings();
$page_title = "Profile";

// Get user data
$user_id = $_SESSION['user']['id'];
$query = "SELECT u.*, w.id as warga_id 
          FROM tbl_users u 
          LEFT JOIN tbl_m_warga w ON u.id_warga = w.id 
          WHERE u.id = ?";
$stmt = mysqli_prepare($conn, $query);
if ($stmt === false) {
    die("Error preparing statement: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Get warga data if exists
$warga = null;
if ($user['id_warga']) {
    $query = "SELECT id, nama, nik FROM tbl_m_warga WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        die("Error preparing statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "i", $user['id_warga']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $warga = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

// Add temporary debug output
echo "<!-- Debug: User ID Warga = " . ($user['id_warga'] ?? 'null') . " -->";

require_once '../template/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Profile</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Profile</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <!-- Profile Image -->
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center mb-3">
                            <?php if (!empty($user['profile_picture'])): ?>
                                <img class="profile-user-img img-fluid img-circle"
                                     src="<?php echo $base_url; ?>profile/get_image.php?filename=<?php echo urlencode($user['profile_picture']); ?>"
                                     alt="User profile picture"
                                     style="width: 100px; height: 100px; object-fit: cover; border-radius: 50% !important; border: 3px solid #adb5bd;">
                            <?php else: ?>
                                <img class="profile-user-img img-fluid img-circle"
                                     src="<?php echo $base_url_style; ?>dist/img/user2-160x160.jpg"
                                     alt="Default profile picture"
                                     style="width: 100px; height: 100px; object-fit: cover; border-radius: 50% !important; border: 3px solid #adb5bd;">
                            <?php endif; ?>
                        </div>

                        <h3 class="profile-username text-center">
                            <?php if ($_SESSION['user']['id_warga']): ?>
                                <a href="<?php echo $base_url; ?>master/warga_detail.php?id=<?php echo $_SESSION['user']['id_warga']; ?>" class="text-dark">
                                    <?php echo htmlspecialchars($warga['nama']); ?>
                                </a>
                            <?php else: ?>
                                <a href="<?php echo $base_url; ?>profile/index.php" class="text-dark">
                                    <?php echo htmlspecialchars($_SESSION['user']['username']); ?>
                                </a>
                            <?php endif; ?>
                        </h3>
                        <p class="text-muted text-center"><?php echo ucfirst($_SESSION['user']['role']); ?></p>

                        <?php if ($flash_message = getFlashMessage()): ?>
                            <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <?php echo $flash_message['text']; ?>
                            </div>
                        <?php endif; ?>

                        <form action="update_picture.php" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <select class="form-control mb-3" id="imageSource" onchange="toggleImageSource(this.value)">
                                    <option value="upload">Unggah dari Perangkat</option>
                                    <option value="camera">Ambil dari Kamera</option>
                                </select>

                                <!-- File Upload Section -->
                                <div id="uploadSection">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="profile_picture" 
                                               name="profile_picture" accept="image/jpeg,image/png">
                                        <label class="custom-file-label" for="profile_picture">Pilih foto</label>
                                    </div>
                                    <small class="form-text text-muted">Format: JPG, PNG. Max: 2MB</small>
                                </div>

                                <!-- Camera Section -->
                                <div id="cameraSection" style="display: none;">
                                    <select class="form-control mb-2" id="cameraSelect">
                                        <option value="">Loading cameras...</option>
                                    </select>
                                    <div class="text-center">
                                        <!-- Camera container with fixed aspect ratio -->
                                        <div style="position: relative; width: 100%; max-width: 320px; margin: 0 auto;">
                                            <div style="padding-top: 75%; /* 4:3 Aspect Ratio */ position: relative; background: #000;">
                                                <video id="camera" 
                                                       style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; display: none;" 
                                                       autoplay playsinline></video>
                                                <canvas id="canvas" style="display: none;"></canvas>
                                            </div>
                                            <img id="capturedImage" 
                                                 style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; display: none;" 
                                                 class="img-fluid">
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-info btn-block mb-2" id="captureBtn" onclick="captureImage()">
                                            <i class="fas fa-camera"></i> Ambil Foto
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-block mb-2" id="retakeBtn" 
                                                onclick="retakePhoto()" style="display: none;">
                                            <i class="fas fa-redo"></i> Ambil Ulang
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block" id="submitBtn">Unggah</button>
                            <input type="hidden" name="image_data" id="imageData">
                        </form>
                    </div>
                </div>
            </div>

            <!-- Main content section -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a class="nav-link active" href="#username" data-toggle="tab">Profile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#password" data-toggle="tab">Kata Sandi</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Username Update Tab -->
                            <div class="tab-pane active" id="username">
                                <form action="update_username.php" method="POST">
                                    <div class="form-group">
                                        <label>Username</label>
                                        <input type="text" class="form-control" name="username" 
                                               value="<?php echo htmlspecialchars($_SESSION['user']['username']); ?>">
                                        <small class="form-text text-muted">Username can be changed</small>
                                    </div>
                                    
                                    <?php if ($warga): ?>
                                    <div class="form-group">
                                        <label>Nama</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($warga['nama']); ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>NIK</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($warga['nik']); ?>" readonly>
                                    </div>
                                    <?php endif; ?>

                                    <div class="form-group">
                                        <label>Role</label>
                                        <input type="text" class="form-control" value="<?php echo ucfirst($_SESSION['user']['role']); ?>" readonly>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Update Username</button>
                                </form>
                            </div>

                            <!-- Password Update Tab -->
                            <div class="tab-pane" id="password">
                                <form action="update_password.php" method="POST">
                                    <div class="form-group">
                                        <label for="current_password">Password Saat Ini</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="new_password">Password Baru</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="confirm_password">Konfirmasi Password Baru</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Update Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../template/footer.php'; ?>

<!-- Add this JavaScript at the bottom of the file, before closing </body> tag -->
<script>
let stream = null;
let selectedCamera = null;

// File input handler
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    const file = this.files[0];
    const label = this.nextElementSibling;
    
    if (file) {
        label.textContent = file.name;
        
        // Validate file size
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 2MB');
            this.value = '';
            label.textContent = 'Pilih foto';
            return;
        }
        
        // Validate file type
        if (!['image/jpeg', 'image/png'].includes(file.type)) {
            alert('Format file tidak valid. Gunakan JPG atau PNG');
            this.value = '';
            label.textContent = 'Pilih foto';
            return;
        }
    } else {
        label.textContent = 'Pilih foto';
    }
});

// Toggle between camera and upload
function toggleImageSource(source) {
    const uploadSection = document.getElementById('uploadSection');
    const cameraSection = document.getElementById('cameraSection');
    const submitBtn = document.getElementById('submitBtn');
    
    if (source === 'camera') {
        uploadSection.style.display = 'none';
        cameraSection.style.display = 'block';
        initializeCamera();
        submitBtn.disabled = true;
    } else {
        uploadSection.style.display = 'block';
        cameraSection.style.display = 'none';
        stopCamera();
        submitBtn.disabled = false;
    }
}

// Initialize camera
async function initializeCamera() {
    try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        const videoDevices = devices.filter(device => device.kind === 'videoinput');
        const cameraSelect = document.getElementById('cameraSelect');
        
        cameraSelect.innerHTML = videoDevices.map(device => 
            `<option value="${device.deviceId}">${device.label || `Camera ${videoDevices.indexOf(device) + 1}`}</option>`
        ).join('');
        
        cameraSelect.onchange = () => {
            selectedCamera = cameraSelect.value;
            startCamera();
        };
        
        if (videoDevices.length > 0) {
            selectedCamera = videoDevices[0].deviceId;
            startCamera();
        }
    } catch (err) {
        console.error('Error accessing camera:', err);
        alert('Error accessing camera. Please make sure you have granted camera permissions.');
    }
}

// Start camera with selected device
async function startCamera() {
    if (stream) {
        stopCamera();
    }
    
    const constraints = {
        video: {
            deviceId: selectedCamera ? { exact: selectedCamera } : undefined,
            aspectRatio: 4/3,  // Force 4:3 aspect ratio
            width: { ideal: 1280 },
            height: { ideal: 960 }
        }
    };
    
    try {
        stream = await navigator.mediaDevices.getUserMedia(constraints);
        const video = document.getElementById('camera');
        video.srcObject = stream;
        video.style.display = 'block';
        document.getElementById('captureBtn').style.display = 'block';
        document.getElementById('retakeBtn').style.display = 'none';
        document.getElementById('capturedImage').style.display = 'none';
    } catch (err) {
        console.error('Error starting camera:', err);
        alert('Error starting camera. Please try again.');
    }
}

// Stop camera
function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    document.getElementById('camera').style.display = 'none';
}

// Capture image
function captureImage() {
    const video = document.getElementById('camera');
    const canvas = document.getElementById('canvas');
    const capturedImage = document.getElementById('capturedImage');
    
    // Set canvas dimensions to match video
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Draw video frame to canvas
    canvas.getContext('2d').drawImage(video, 0, 0);
    
    // Convert to base64
    const imageData = canvas.toDataURL('image/jpeg');
    document.getElementById('imageData').value = imageData;
    
    // Display captured image
    capturedImage.src = imageData;
    capturedImage.style.display = 'block';
    
    // Update UI
    video.style.display = 'none';
    document.getElementById('captureBtn').style.display = 'none';
    document.getElementById('retakeBtn').style.display = 'block';
    document.getElementById('submitBtn').disabled = false;
    
    // Stop camera
    stopCamera();
}

// Retake photo
function retakePhoto() {
    document.getElementById('imageData').value = '';
    document.getElementById('capturedImage').style.display = 'none';
    document.getElementById('submitBtn').disabled = true;
    startCamera();
}

// Clean up on page unload
window.onbeforeunload = function() {
    stopCamera();
};
</script>