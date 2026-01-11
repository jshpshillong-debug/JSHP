 //-- NOTIFICATION -->
        // ---------------- Notifications Section ----------------
        const notificationList = document.getElementById("notificationList");
        // Fetch notifications from another HTML
        fetch("notificationlistupdate.html")
          .then((res) => res.text())
          .then((data) => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(data, "text/html");
            const items = doc.querySelectorAll(".notification-item");
            if (items.length === 0) {
              notificationList.innerHTML = `<tr><td colspan="4" style="text-align:center;">No notifications found.</td></tr>`;
              return;
            }
            notificationList.innerHTML = Array.from(items)
              .map((item, index) => {
                const title =
                  item.querySelector(".title")?.textContent.trim() ||
                  "Untitled";
                const pdf =
                  item.querySelector(".pdf")?.getAttribute("href") || "#";

                // 👇 New line: fetch date from each .notification-item
                const date =
                  item.querySelector(".date")?.textContent.trim() ||
                  "Date not specified";
                return `
          <tr>
            <td>${index + 1}</td>
            <td>${title}</td>
            <td>${date}</td>
            <td><a href="${pdf}" target="_blank">📄 View</a></td>
          </tr>
        `;
              })
              .join("");
          })
          .catch((err) => {
            console.error(err);
            notificationList.innerHTML = `<tr><td colspan="4" style="text-align:center;color:red;">Failed to load notifications.</td></tr>`;
          });

          //---Notification List---
          fetch("notification_list.html")
            .then((response) => response.text())
            .then((data) => {
              const parser = new DOMParser();
              const htmlDoc = parser.parseFromString(data, "text/html");

              const notices = htmlDoc.querySelectorAll(".notice");
              let output = "";

              notices.forEach((n) => {
                const title =
                  n.querySelector(".title")?.innerText || "Untitled";
                const date = n.querySelector(".date")?.innerText || "No date";
                const pdf =
                  n.querySelector(".pdf")?.getAttribute("href") || "#";

                output += `
            <div class="notif-item">
              <div>
                <div class="notif-title">${title}</div>
                <div class="notif-date">${date}</div>
              </div>

              <a href="${pdf}" target="_blank" class="pdf-btn">📄PDF</a>
            </div>
          `;
              });

              document.getElementById("notificationBox").innerHTML =
                output ||
                "<p style='padding:15px;text-align:center;'>No notifications found.</p>";
            })
            .catch((error) => {
              document.getElementById("notificationBox").innerHTML =
                "<p style='padding:15px;text-align:center;color:red;'>Error loading notifications.</p>";
            });