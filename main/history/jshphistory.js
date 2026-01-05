 fetch("history load.html")
    .then(response => {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.text();
    })
    .then(html => {
      document.getElementById("jshphistory").innerHTML = html;
    })
    .catch(error => {
      console.error("load error:", error);
      document.getElementById("jshphistory").innerHTML =
        "<p>Unable to load History.</p>";
    });