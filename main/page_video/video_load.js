 fetch("video_load.html")
    .then(response => {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.text();
    })
    .then(html => {
      document.getElementById("jshpvideo").innerHTML = html;
    })
    .catch(error => {
      console.error("load error:", error);
      document.getElementById("jshpvideo").innerHTML =
        "<p>Unable to load.....</p>";
    });



   /* =====================================
   LOAD MORE VIDEOS – FIXED & STABLE
===================================== */

const observer = new MutationObserver(() => {
  const videos = document.querySelectorAll(".video-item");
  const btn = document.getElementById("loadMoreVideos");

  if (!videos.length || !btn) return;

  observer.disconnect(); // stop once videos exist

  const perLoad = 6;
  let index = 0;

  // Hide all videos
  videos.forEach(v => (v.style.display = "none"));

  function showMore() {
    for (let i = index; i < index + perLoad && i < videos.length; i++) {
      videos[i].style.display = "block";
    }
    index += perLoad;

    if (index >= videos.length) {
      btn.style.display = "none";
    }
  }

  // Show first 6
  showMore();

  btn.style.display = "inline-block";
  btn.addEventListener("click", showMore);
});

// Wait until videos are injected
observer.observe(document.getElementById("jshpvideo"), {
  childList: true,
  subtree: true,
});
