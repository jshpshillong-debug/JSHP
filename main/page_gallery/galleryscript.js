
      const API_KEY = "AIzaSyDtpds40pfDlP5APglwnUGOBz4am2mTOJ4";
      const FOLDER_ID = "1nJ6ge0PamJLDPrdM6x7t-_EkqIwauGC3";

      const gallery = document.getElementById("gallery");
      let images = [];

      async function fetchImages() {
        try {
          const query = `'${FOLDER_ID}' in parents and mimeType contains 'image/' and trashed=false`;
          const url = `https://www.googleapis.com/drive/v3/files?q=${encodeURIComponent(
            query
          )}&key=${API_KEY}&fields=files(id,name,createdTime)&pageSize=1000`;

          const res = await fetch(url);
          const data = await res.json();

          images = data.files || [];

          renderAllImages();
        } catch {
          gallery.innerHTML =
            "<p style='text-align:center;color:red;'>Failed to load</p>";
        }
      }

      function renderAllImages() {
        images.forEach((file, index) => {
          const item = document.createElement("div");
          item.className = "gallery-item";

          const img = document.createElement("img");
          img.src = `https://lh3.googleusercontent.com/d/${file.id}=w800`;
          img.dataset.index = index;

          const downloadBtn = document.createElement("a");
          downloadBtn.className = "hover-btn download-btn";
          downloadBtn.href = `https://drive.google.com/uc?export=download&id=${file.id}`;
          downloadBtn.setAttribute("download", file.name);
          downloadBtn.innerHTML = '<i class="fas fa-download"></i>';

          const shareBtn = document.createElement("button");
          shareBtn.className = "hover-btn share-btn";
          shareBtn.innerHTML = '<img src="img/icons8-share.svg" alt="share"/>';
          shareBtn.onclick = () =>
            shareImage(
              `https://lh3.googleusercontent.com/d/${file.id}=w2000`,
              file.name
            );

          const mobileButtons = document.createElement("div");
          mobileButtons.className = "mobile-buttons";

          const mDown = document.createElement("a");
          mDown.className = "mobile-btn-download";
          mDown.innerHTML = '<i class="fas fa-download"></i>';
          mDown.href = downloadBtn.href;

          const mShare = document.createElement("button");
          mShare.className = "mobile-btn-share";
          mShare.innerHTML = '<i class="fas fa-share-alt"></i>';
          mShare.onclick = () =>
            shareImage(
              `https://lh3.googleusercontent.com/d/${file.id}=w2000`,
              file.name
            );

          mobileButtons.appendChild(mDown);
          mobileButtons.appendChild(mShare);

          item.appendChild(img);
          item.appendChild(downloadBtn);
          item.appendChild(shareBtn);
          item.appendChild(mobileButtons);
          gallery.appendChild(item);

          img.addEventListener("click", () => {
            if (window.innerWidth <= 768) return;
            openModal(index);
          });
        });
      }

      fetchImages();



      //--moddal display


      const modal = document.getElementById("modal");
      const modalImg = document.getElementById("modalImage");
      const closeBtn = document.querySelector(".close");
      const nextBtn = document.querySelector(".next");
      const prevBtn = document.querySelector(".prev");

      const modalShare = document.getElementById("modalShare");
      const modalDownload = document.getElementById("modalDownload");

      let currentImageIndex = 0;

      function openModal(index) {
        currentImageIndex = index;
        showModalImage();
        modal.style.display = "flex";
      }

      function closeModal() {
        modal.style.display = "none";
      }
      function showModalImage() {
        const file = images[currentImageIndex];
        const imageUrl = `https://lh3.googleusercontent.com/d/${file.id}=w2000`;
        modalImg.src = imageUrl;

        // Set share + download
        modalShare.onclick = () => shareImage(imageUrl, file.name);
        modalDownload.onclick = () => downloadImage(file.id, file.name);
      }
      function nextImage() {
        currentImageIndex = (currentImageIndex + 1) % images.length;
        showModalImage();
      }
      function prevImage() {
        currentImageIndex =
          (currentImageIndex - 1 + images.length) % images.length;
        showModalImage();
      }
      // Download function
      function downloadImage(id, name) {
        const a = document.createElement("a");
        a.href = `https://drive.google.com/uc?export=download&id=${id}`;
        a.download = name;
        a.click();
      }
      // Close modal
      closeBtn.onclick = closeModal;
      nextBtn.onclick = nextImage;
      prevBtn.onclick = prevImage;

      modal.addEventListener("click", (e) => {
        if (e.target === modal) closeModal();
      });

      document.addEventListener("keydown", (e) => {
        if (modal.style.display !== "flex") return;
        if (e.key === "ArrowRight") nextImage();
        if (e.key === "ArrowLeft") prevImage();
        if (e.key === "Escape") closeModal();
      });

      // Share image function
      function shareImage(imageUrl, imageName) {
        const encodedUrl = encodeURIComponent(imageUrl);
        const text = encodeURIComponent(
          "Check out this photo from JSHP Gallery!"
        );
        // ✅ Native mobile share (Quick Share / Nearby / Bluetooth)
        if (navigator.share && location.protocol === "https:") {
          navigator
            .share({
              title: imageName || "JSHP Photo",
              text: "Check out this photo from JSHP Gallery!",
              url: imageUrl,
            })
            .catch((err) => console.log("Share failed:", err));
          return;

          window.open(
            `https://wa.me/?text=${encodeURIComponent(shareUrl)}`,
            "_blank"
          );
        }
        // ✅ Fallback for desktop or non-supporting browsers
        const shareHtml = `
    <div style="
      display:flex;
      flex-direction:column;
      align-items:center;
      justify-content:center;
      padding:20px;
      font-family:sans-serif;
      max-width:90vw;
      box-sizing:border-box;
    ">
      <h3 style="margin-bottom:20px;color:#333;text-align:center;">Share this image</h3>

      <div style="
        display:flex;
        flex-wrap:wrap;
        justify-content:center;
        gap:10px;
        width:100%;
      ">
        <a href="https://wa.me/?text=${text}%20${encodedUrl}" target="_blank"
          style="flex:1 1 40%;padding:10px;text-align:center;background:#25D366;color:#fff;border-radius:8px;text-decoration:none;font-weight:bold;">WhatsApp</a>

        <a href="https://t.me/share/url?url=${encodedUrl}&text=${text}" target="_blank"
          style="flex:1 1 40%;padding:10px;text-align:center;background:#0088cc;color:#fff;border-radius:8px;text-decoration:none;font-weight:bold;">Telegram</a>

        <a href="https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}" target="_blank"
          style="flex:1 1 40%;padding:10px;text-align:center;background:#4267B2;color:#fff;border-radius:8px;text-decoration:none;font-weight:bold;">Facebook</a>

        <a href="https://www.instagram.com/" target="_blank"
          style="flex:1 1 40%;padding:10px;text-align:center;background:#E4405F;color:#fff;border-radius:8px;text-decoration:none;font-weight:bold;">Instagram</a>
      </div>

      <p style="margin-top:15px;color:#555;font-size:14px;text-align:center;">
        For Bluetooth / Nearby Share, open this page on mobile and use Quick Share.
      </p>
    </div>
  `;
        const shareWindow = window.open("", "Share", "width=400,height=450");
        shareWindow.document.write(shareHtml);
        shareWindow.document.close();
      }
      // If click is outside navLinks and menuToggle
      document.addEventListener("click", (e) => {
        const isMenuOpen = navLinks.classList.contains("open");

        if (!isMenuOpen) return;

        // If click is outside navLinks and menuToggle
        if (!navLinks.contains(e.target) && !menuToggle.contains(e.target)) {
          navLinks.classList.remove("open");
          menuToggle.classList.remove("active");
        }
      });