(function () {
          // initialize with your public key
          emailjs.init("ZAX-M2UefU-IHTKm4");
        })();

        const form = document.getElementById("contactForm");
        const statusEl = document.getElementById("status");

        form.addEventListener("submit", function (e) {
          e.preventDefault();

          // small client-side validation
          const name = document.getElementById("name").value.trim();
          const email = document.getElementById("email").value.trim();
          const subject = document.getElementById("subject").value.trim();
          const message = document.getElementById("message").value.trim();

          if (!name || !email || !subject || !message) {
            statusEl.style.color = "#d93a3a";
            statusEl.textContent = "Please fill in all fields.";
            return;
          }

          statusEl.style.color = "#0b4a5a";
          statusEl.textContent = "Sending...";

          const params = {
            from_name: name,
            from_email: email,
            subject,
            message,
          };

          // send main email
          emailjs
            .send("service_18mblod", "template_ovj77an", params)
            .then(() => {
              // send auto-reply template (optional)
              emailjs
                .send("service_18mblod", "template_blbfaji", params)
                .catch(() => {
                  /* ignore autoreply error */
                });

              statusEl.style.color = "green";
              statusEl.textContent = "Message sent successfully!";
              form.reset();
              // keep status visible for a short while on mobile
              setTimeout(() => {
                statusEl.textContent = "";
              }, 5000);
            })
            .catch(() => {
              statusEl.style.color = "#d93a3a";
              statusEl.textContent = "Failed to send message. Try again later.";
            });
        });