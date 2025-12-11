
        // Mobile menu toggle
        const menuToggle = document.getElementById("menuToggle");
        const navLinks = document.getElementById("navLinks");
        const links = document.querySelectorAll(".nav-links a");

        menuToggle.addEventListener("click", () => {
          navLinks.classList.toggle("open");
          menuToggle.classList.toggle("active");
        });

        // Close mobile menu when clicking on a link
        links.forEach((link) => {
          link.addEventListener("click", () => {
            links.forEach((l) => l.classList.remove("active"));
            link.classList.add("active");
            navLinks.classList.remove("open");
            menuToggle.classList.remove("active");
          });
        });

        // Active navigation based on scroll position
        // Active navigation based on scroll position (FIXED)
        window.addEventListener("scroll", () => {
          let fromTop = window.scrollY + 150;

          links.forEach((link) => {
            const href = link.getAttribute("href");

            // ✅ Only run for internal section links
            if (!href.startsWith("#")) return;

            const section = document.querySelector(href);
            if (!section) return;

            if (
              section.offsetTop <= fromTop &&
              section.offsetTop + section.offsetHeight > fromTop
            ) {
              links.forEach((l) => l.classList.remove("active"));
              link.classList.add("active");
            }
          });
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
          anchor.addEventListener("click", function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute("href"));
            if (target) {
              window.scrollTo({
                top: target.offsetTop - 80,
                behavior: "smooth",
              });
            }
          });
        });

        // Responsive image loading
        window.addEventListener("resize", function () {
          // This would typically handle responsive image loading
          // For now, we're using CSS for responsiveness
        });

         // JavaScript Navigation Links
      document.getElementById("homeBtn").addEventListener("click", function () {
        window.location.href = "../main/index.html";
      });

      document.getElementById("nextBtn").addEventListener("click", function () {
        window.location.href = "video page 2.html";
      });