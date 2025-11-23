<?php
// Directory where images are stored (relative to this script)
$imageDir = 'https://mega.nz/folder/KV1TCIja#4BrpW4_4uDVl3T2laAl_dw/';

// Get all image files (jpg, jpeg, png, gif) from the directory
$images = glob($imageDir . '*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF}', GLOB_BRACE);

// Sort images alphabetically (optional, for consistent order)
sort($images);

// Prepare images array for JavaScript
$imagesJson = json_encode($images);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>JSHP SHILLONG</title>
    <link rel="icon" href="img/emlem.png" alt="icon" />
<link rel="preconnect" href="https://fonts.googleapis.com" />

    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
    
    <style>
        .gallery {
            display: grid;
            grid-template-columns: repeat(3, 460px); /* 3 columns, each 460px wide */
            gap: 15px; /* Space between images */
            justify-content: center; /* Center the grid if fewer than 3 images */
            max-width: 1380px; /* Total width for 3 columns */
            margin: 0 auto; /* Center the gallery */
        }
        .gallery-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            position: relative;
        }
        .gallery img {
            width: 100%; /* Fill the column width (460px) */
            height: auto; /* Maintain aspect ratio based on image resolution */
            display: block;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .gallery img:hover {
            transform: scale(1.05);
        }
        .download-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 8px;
            background-color: transparent; /* Removed blue background */
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 18px;
            transition: background-color 0.2s;
            display: none; /* Hidden by default */
        }
        .gallery-item:hover .download-btn {
            display: block; /* Show on hover */
        }
        .download-btn:hover {
            background-color: rgba(255, 255, 255, 0.2); /* Subtle hover effect */
        }
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            max-width: 90%;
            max-height: 90%;
            position: relative;
        }
        .modal img {
            width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .close {
            position: absolute;
            top: -40px;
            right: 0;
            color: white;
            font-size: 30px;
            font-weight: bold;
            cursor: pointer;
        }
        .prev, .next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-size: 30px;
            font-weight: bold;
            cursor: pointer;
            user-select: none;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transition: background-color 0.2s;
        }
        .prev { left: -50px; }
        .next { right: -50px; }
        .prev:hover, .next:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
        .arrow-icon {
            width: 20px;
            height: 20px;
            fill: white;
        }
        .modal-buttons {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .modal-btn {
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.2s;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
        h1 {
            text-align: center;
            color: #333;
        }
    </style>
<body>
    <h1>Photo Gallery</h1>
    <div class="gallery">
        <?php if (!empty($images)): ?>
            <?php foreach ($images as $index => $image): ?>
                <div class="gallery-item" data-index="<?php echo $index; ?>">
                    <img src="<?php echo htmlspecialchars($image); ?>" alt="Gallery Image">
                    <a href="<?php echo htmlspecialchars($image); ?>" download class="download-btn" title="Download">
                        <i class="fas fa-download"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: span 3; text-align: center;">No images found in the gallery folder.</p>
        <?php endif; ?>
    </div>

    <!-- Modal for full image view -->
    <div id="imageModal" class="modal">
        <div class="modal-buttons">
            <a id="modalDownload" href="" download class="modal-btn" title="Download">
                <i class="fas fa-download"></i>
            </a>
            <button id="modalShare" class="modal-btn">Share</button>
        </div>
        <div class="modal-content">
            <span class="close">&times;</span>
            <img id="modalImage" src="" alt="Full Image">
            <span class="prev">
                <svg class="arrow-icon" viewBox="0 0 24 24" transform="scale(-1,1)">
                    <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z"/>
                </svg>
            </span>
            <span class="next">
                <svg class="arrow-icon" viewBox="0 0 24 24">
                    <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z"/>
                </svg>
            </span>
        </div>
    </div>

    <script>
        const images = <?php echo $imagesJson; ?>;
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        const modalDownload = document.getElementById('modalDownload');
        const modalShare = document.getElementById('modalShare');
        const prevBtn = document.querySelector('.prev');
        const nextBtn = document.querySelector('.next');
        const closeBtn = document.querySelector('.close');
        let currentIndex = 0;

        // Open modal when image is clicked
        document.querySelectorAll('.gallery-item').forEach((item, index) => {
            item.addEventListener('click', (e) => {
                if (e.target.tagName !== 'A') { // Avoid triggering if download button is clicked
                    currentIndex = index;
                    showImage(currentIndex);
                    modal.style.display = 'flex';
                }
            });
        });

        // Close modal
        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        // Navigate to previous image
        prevBtn.addEventListener('click', () => {
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            showImage(currentIndex);
        });

        // Navigate to next image
        nextBtn.addEventListener('click', () => {
            currentIndex = (currentIndex + 1) % images.length;
            showImage(currentIndex);
        });

        // Share button
        modalShare.addEventListener('click', () => {
            const imageUrl = window.location.origin + '/' + images[currentIndex];
            if (navigator.share) {
                navigator.share({
                    title: 'Check out this image',
                    url: imageUrl
                });
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(imageUrl).then(() => {
                    alert('Image URL copied to clipboard!');
                });
            }
        });

        // Function to show image in modal
        function showImage(index) {
            modalImage.src = images[index];
            modalDownload.href = images[index];
        }

        // Close modal on outside click
        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    </script>
    <script src="navbarscript.js"></script>
</body>
</html>
