 //script gallery modal display
  const galleryImages = document.querySelectorAll(".gallery__grid img");
  const modal = document.getElementById("galleryModal");
  const modalImage = document.getElementById("modalImage");
  const closeModal = document.getElementById("closeModal");

  galleryImages.forEach(img => {
    img.addEventListener("click", () => {
      modal.classList.add("active");
      modalImage.src = img.src;
      modalImage.alt = img.alt;
    });
  });

  closeModal.addEventListener("click", () => {
    modal.classList.remove("active");
    modalImage.src = "";
  });

  modal.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.classList.remove("active");
      modalImage.src = "";
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      modal.classList.remove("active");
      modalImage.src = "";
    }
  });