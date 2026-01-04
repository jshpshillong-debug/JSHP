 fetch("load history.html")
    .then(response => {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.text();
    })
    .then(html => {
      document.getElementById("historycontent").innerHTML = html;
    })
    .catch(error => {
      console.error("load error:", error);
      document.getElementById("historycontent").innerHTML =
        "<p>Unable to load History.</p>";
    });