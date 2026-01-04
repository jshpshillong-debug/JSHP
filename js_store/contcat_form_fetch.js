fetch("contact_form.html")
    .then(response => {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.text();
    })
    .then(html => {
      document.getElementById("contact_form").innerHTML = html;
    })
    .catch(error => {
      console.error("Contact section load error:", error);
      document.getElementById("contact_form").innerHTML =
        "<p>Unable to load Contact section.</p>";
    });