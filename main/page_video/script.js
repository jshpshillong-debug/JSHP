// ===== LOAD MORE VIDEOS FEATURE =====

let videosPerLoad = 4; // show 4 more videos per click
let currentIndex = 0;

// Get all videos already loaded (iframe wrappers)
function getAllVideos() {
  return Array.from(document.querySelectorAll(".video-item"));
}

document.addEventListener("DOMContentLoaded", () => {
  const container = document.getElementById("jshpvideo");
  const loadMoreBtn = document.getElementById("loadMoreVideos");

  if (!container || !loadMoreBtn) return;

  const allVideos = getAllVideos();

  // Hide all videos initially
  allVideos.forEach(video => (video.style.display = "none"));

  function showMoreVideos() {
    for (
      let i = currentIndex;
      i < currentIndex + videosPerLoad && i < allVideos.length;
      i++
    ) {
      allVideos[i].style.display = "block";
    }

    currentIndex += videosPerLoad;

    // Hide button if no more videos
    if (currentIndex >= allVideos.length) {
      loadMoreBtn.style.display = "none";
    }
  }

  // Show first set
  showMoreVideos();

  // Button click
  loadMoreBtn.addEventListener("click", showMoreVideos);
});
