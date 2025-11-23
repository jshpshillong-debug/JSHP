<?php
/**
 * drive-gallery.php
 *
 * Single-file gallery page that:
 *  - Uses a service account JSON to access Google Drive
 *  - Lists image files inside the configured folder ID
 *  - Serves image binary via a proxy endpoint (this file) to the browser
 *
 * Important: keep the JSON key file outside the webroot and DO NOT publish it.
 */

// ---------------- CONFIG ----------------
$serviceAccountJsonPath = 'C:/Users/User/Desktop/Website/first-strength-479117-i9-f54f0ee08272.json';
$driveFolderId = '1nJ6ge0PamJLDPrdM6x7t-_EkqIwauGC3';
$scope = 'https://www.googleapis.com/auth/drive.readonly';
// ----------------------------------------

/**
 * Helper: Base64 URL encode
 */
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Create signed JWT and exchange for OAuth2 access token
 */
function getServiceAccountAccessToken($jsonPath, $scope) {
    if (!file_exists($jsonPath)) {
        throw new Exception("Service account JSON file not found at: $jsonPath");
    }
    $json = json_decode(file_get_contents($jsonPath), true);
    if (!$json) {
        throw new Exception("Unable to parse service account JSON.");
    }

    $now = time();
    $jwtHeader = ['alg' => 'RS256', 'typ' => 'JWT'];
    $jwtClaimSet = [
        'iss' => $json['client_email'],
        'scope' => $scope,
        'aud' => $json['token_uri'],
        'exp' => $now + 3600,
        'iat' => $now
    ];

    $encodedHeader = base64url_encode(json_encode($jwtHeader));
    $encodedClaim = base64url_encode(json_encode($jwtClaimSet));
    $unsignedJwt = $encodedHeader . '.' . $encodedClaim;

    $privateKey = $json['private_key'];
    $signature = '';
    $success = openssl_sign($unsignedJwt, $signature, $privateKey, OPENSSL_ALGO_SHA256);
    if (!$success) {
        throw new Exception("Failed to sign JWT.");
    }
    $signedJwt = $unsignedJwt . '.' . base64url_encode($signature);

    // Exchange JWT for access token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $json['token_uri']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    $postFields = http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $signedJwt
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new Exception("cURL error when requesting token: $err");
    }
    curl_close($ch);

    $resp = json_decode($response, true);
    if (isset($resp['error'])) {
        $err = isset($resp['error_description']) ? $resp['error_description'] : json_encode($resp['error']);
        throw new Exception("Error fetching access token: $err");
    }
    if (!isset($resp['access_token'])) {
        throw new Exception("No access token returned from Google.");
    }
    return [
        'access_token' => $resp['access_token'],
        'expires_in' => $resp['expires_in'] ?? 3600
    ];
}

/**
 * List image files inside the specified Drive folder (top-level only)
 */
function listImagesInFolder($accessToken, $folderId, $pageSize = 1000) {
    $q = sprintf("'%s' in parents and mimeType contains 'image/' and trashed = false", $folderId);
    $params = [
        'q' => $q,
        'pageSize' => $pageSize,
        'fields' => 'files(id,name,mimeType,thumbnailLink,webViewLink)'
    ];
    $url = 'https://www.googleapis.com/drive/v3/files?' . http_build_query($params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Accept: application/json'
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new Exception("cURL error when listing files: $err");
    }
    curl_close($ch);

    $resp = json_decode($response, true);
    if (isset($resp['error'])) {
        $err = isset($resp['error']['message']) ? $resp['error']['message'] : json_encode($resp['error']);
        throw new Exception("Drive API error: $err");
    }
    if (!isset($resp['files'])) {
        return [];
    }
    return $resp['files'];
}

/**
 * Helper: Fetch raw file content from Drive and stream to browser.
 * This function uses the access token and streams the binary content.
 */
function streamDriveFile($accessToken, $fileId, $downloadName = null) {
    $url = "https://www.googleapis.com/drive/v3/files/" . rawurlencode($fileId) . "?alt=media";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Accept: */*'
    ]);
    // get headers separately first to determine content-type
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $err = curl_error($ch);
        curl_close($ch);
        header($_SERVER['SERVER_PROTOCOL'] . ' 502 Bad Gateway');
        echo "Error fetching file: $err";
        exit;
    }

    // separate headers and body
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headersRaw = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 400) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        echo "File not available (HTTP $httpCode).";
        exit;
    }

    // parse content-type header
    $contentType = 'application/octet-stream';
    if (preg_match('/Content-Type:\s*([^\r\n]+)/i', $headersRaw, $m)) {
        $contentType = trim($m[1]);
    }

    header('Content-Type: ' . $contentType);
    if ($downloadName) {
        header('Content-Disposition: attachment; filename="' . basename($downloadName) . '"');
    } else {
        // inline
        header('Content-Disposition: inline');
    }
    echo $body;
    exit;
}


// ---------------- Main flow ----------------
try {
    // Acquire access token
    $tokenData = getServiceAccountAccessToken($serviceAccountJsonPath, $scope);
    $accessToken = $tokenData['access_token'];

    // List files in the folder
    $files = listImagesInFolder($accessToken, $driveFolderId);

    // Build a quick map of fileId => fileMeta for security when proxying
    $fileMap = [];
    foreach ($files as $f) {
        $fileMap[$f['id']] = $f;
    }

    // If the request is a proxy request to fetch an image binary:
    if (isset($_GET['file_id'])) {
        $fileId = $_GET['file_id'];
        if (!isset($fileMap[$fileId])) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
            echo "Forbidden or file not in configured folder.";
            exit;
        }
        $downloadName = isset($_GET['dl']) && $_GET['dl'] == '1' ? $fileMap[$fileId]['name'] : null;
        streamDriveFile($accessToken, $fileId, $downloadName);
        // streamDriveFile exits
    }

    // Now $files is available to render the HTML gallery
} catch (Exception $e) {
    // Friendly error page
    $err = htmlspecialchars($e->getMessage());
    echo "<!doctype html><html><head><meta charset='utf-8'><title>Gallery Error</title></head><body>";
    echo "<h2>Failed to load gallery</h2>";
    echo "<pre>$err</pre>";
    echo "<p>Check service account JSON path, Drive folder sharing, and PHP extensions (curl/openssl).</p>";
    echo "</body></html>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>JSHP Gallery</title>
  <link rel="icon" href="img/emlem.png" />
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* Gallery styles (kept simple) */
    body { font-family: Arial, sans-serif; 
        padding: 0; 
        margin: 0; 
        background: #fff; }
    h1 {
         text-align: center; 
         margin: 20px 0; 
         color: #333; }
    .gallery { 
          column-count: 3;
  column-gap: 20px;
  padding: 3% 15%;
    }
    .gallery-item { 
        position: relative; 
        overflow: hidden; 
        border-radius: 6px; 
        box-shadow: 0 4px 18px rgba(0,0,0,0.08); 
        background: #fafafa; }
    .gallery-item img { 
        width: 100%; 
        height: auto; 
        
        display: block;
    cursor: pointer;
    }
    .download-btn { 
        position: absolute; 
        top: 8px; 
        right: 8px; 
        background: rgba(0,0,0,0.5); 
        color: #fff; 
        padding: 8px; 
        border-radius: 6px; 
        text-decoration: none; 
        display: none; }
    .gallery-item:hover .download-btn { 
        display: block; }
    /* Modal */
    .modal { 
        display: none;
         position: fixed;
          z-index: 10000; 
          left: 0; top: 0; 
          width: 100%; 
          height: 100%; 
          background: rgba(0,0,0,0.9);
           align-items: center; 
           justify-content: center; }
    .modal-content {
         max-width: 94%;
          max-height: 94%; 
          position: relative; }
    .modal img { 
        max-width: 80%;
         max-height: 80%;
          border-radius: 4px;
           display: block; }
    .close {
         position: absolute;
          top: -48px; 
          right: 0; 
          color: #fff; 
          font-size: 30px; 
          cursor: pointer; }
    .prev, .next {
         position: absolute;
          top: 50%;
           transform: translateY(-50%); 
           color: #fff;
            font-size: 30px;
             cursor: pointer; 
             width: 48px;
              height: 48px;
               display: flex; 
               align-items: center; 
               justify-content: center; 
               background: rgba(255,255,255,0.08); 
               border-radius: 50%; }
    .prev { 
        left: -60px; } 
        .next {
             right: -60px; }
    .modal-buttons {
         position: absolute; 
         top: 10px; right: 10px; 
         display: flex; 
         flex-direction: column;
          gap: 8px; }
    .modal-btn {
         padding: 10px; 
         background: rgba(255,255,255,0.08); 
         color: #fff; 
         border-radius: 6px; 
         border: none; cursor: pointer; }
    @media (max-width: 900px) { 
        .gallery { grid-template-columns: repeat(2, 1fr); } 
        .gallery-item img { height: 180px; } }
    @media (max-width: 480px) { 
        .gallery { grid-template-columns: 1fr; } 
        .gallery-item img { height: 240px; } 
        .prev, .next { display: none; } }
        /* Mobile Layout for Download Button */
@media (max-width: 600px) {
    .gallery {
        column-count: 1 !important;
        padding: 10px !important;
    }

    .gallery-item {
        display: flex;

        align-items: center;
    }
    .gallery-item img {
  width: 100%;
  height: auto;
  /*--border-radius: 10px;--*/
  cursor: pointer;
}

    .download-btn {
        position: static !important;
        display: block !important;
        margin: 10px auto 0 auto;
        background: rgba(0, 0, 0, 0.6);
        padding: 8px 12px;
        border-radius: 8px;
    }

    .download-btn i {
        font-size: 18px;
    }
}

  </style>
</head>
<body>
   <header class="navbar">
      <div class="container">
        <div><img src="img/JSHP Emblem.png" class="logo" alt="logo" /></div>

        <nav class="nav-links" id="navLinks">
          <a href="../main/index.html" class="active">🏠Home</a>
          <a href="../main/index.html">📖About JSHP</a>
          <a href="../Video/video page 1.html">🎬Videos</a>
          <a href="../Notification/notification&programe.html"
            >📅Notification</a
          >
          <a href="../Gallery/Gallery.html">📅Gallery</a>
          <a href="#contact">☎️Contact</a>
        </nav>
        <button
          class="menu-toggle"
          id="menuToggle"
          aria-label="Toggle navigation"
        >
          <span class="bar"></span>
          <span class="bar"></span>
          <span class="bar"></span>
        </button>
      </div>
    </header>

  <h1>Photo Gallery</h1>

  <div class="gallery" id="gallery">
    <?php if (empty($files)): ?>
      <p style="grid-column: span 3; text-align: center;">No images found in the Drive folder.</p>
    <?php else: ?>
      <?php $index = 0; foreach ($files as $f): ?>
        <?php
          $id = $f['id'];
          $name = $f['name'];
          // Proxy URL to fetch the raw binary via this script:
          $imgUrl = htmlspecialchars(basename($_SERVER['PHP_SELF']) . "?file_id=" . urlencode($id));
          // Download link (dl=1 will set content-disposition attachment)
          $dlUrl = htmlspecialchars(basename($_SERVER['PHP_SELF']) . "?file_id=" . urlencode($id) . "&dl=1");
        ?>
        <div class="gallery-item" data-index="<?php echo $index; ?>">
          <img src="<?php echo $imgUrl; ?>" 
     alt="<?php echo htmlspecialchars($name); ?>" 
     title="<?php echo htmlspecialchars($name); ?>">

          <a class="download-btn" href="<?php echo $dlUrl; ?>" title="Download">
            <i class="fas fa-download"></i>
          </a>
        </div>
      <?php $index++; endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Modal for viewing images -->
  <div id="imageModal" class="modal" role="dialog" aria-hidden="true">
    <div class="modal-buttons">
      <a id="modalDownload" class="modal-btn" href="#" download><i class="fas fa-download"></i></a>
      <button id="modalShare" class="modal-btn">Share</button>
    </div>
    <div class="modal-content">
      <span class="close" aria-label="Close">&times;</span>
      <img id="modalImage" src="" alt="Full Image">
      <span class="prev" aria-hidden="true">&#10094;</span>
      <span class="next" aria-hidden="true">&#10095;</span>
    </div>
  </div>

  <script>
    const items = document.querySelectorAll('.gallery-item');
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const modalDownload = document.getElementById('modalDownload');
    const modalShare = document.getElementById('modalShare');
    const prevBtn = document.querySelector('.prev');
    const nextBtn = document.querySelector('.next');
    const closeBtn = document.querySelector('.close');
    let currentIndex = 0;
    const images = Array.from(items).map((it) => {
      const img = it.querySelector('img');
      const dl = it.querySelector('.download-btn');
      return { src: img.getAttribute('src'), dl: dl ? dl.getAttribute('href') : null };
    });

    function showImage(i) {
      currentIndex = i;
      modalImage.src = images[i].src;
      modalDownload.href = images[i].dl;
      modal.style.display = 'flex';
      modal.setAttribute('aria-hidden', 'false');
    }

    items.forEach((it, idx) => {
      it.addEventListener('click', (e) => {
        if (e.target.tagName.toLowerCase() === 'a') return; // ignore download clicks
        showImage(idx);
      });
    });

    closeBtn.addEventListener('click', () => {
      modal.style.display = 'none';
      modal.setAttribute('aria-hidden', 'true');
      modalImage.src = '';
    });

    prevBtn.addEventListener('click', () => {
      showImage((currentIndex - 1 + images.length) % images.length);
    });
    nextBtn.addEventListener('click', () => {
      showImage((currentIndex + 1) % images.length);
    });

    modalShare.addEventListener('click', () => {
      const shareUrl = window.location.origin + window.location.pathname + images[currentIndex].src;
      if (navigator.share) {
        navigator.share({ title: 'Check out this image', url: shareUrl });
      } else {
        navigator.clipboard.writeText(shareUrl).then(() => alert('Image URL copied to clipboard!'));
      }
    });

    window.addEventListener('click', (e) => {
      if (e.target === modal) { closeBtn.click(); }
    });
  </script>
  <script src="navbarscript.js"></script>
</body>
</html>
