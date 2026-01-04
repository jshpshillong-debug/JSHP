 fetch("about_section.html")
    .then(response => {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.text();
    })
    .then(html => {
      document.getElementById("aboutContent").innerHTML = html;
    })
    .catch(error => {
      console.error("About section load error:", error);
      document.getElementById("aboutContent").innerHTML =
        "<p>Unable to load About section.</p>";
    });