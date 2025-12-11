const menuToggle=document.getElementById('menuToggle');
const navLinks=document.getElementById('navLinks');
const links=document.querySelectorAll('.nav-links a');


menuToggle.addEventListener('click',()=>{
navLinks.classList.toggle('open');
menuToggle.classList.toggle('active');
});


links.forEach(link=>{
link.addEventListener('click',()=>{
links.forEach(l=>l.classList.remove('active'));
link.classList.add('active');
navLinks.classList.remove('open');
menuToggle.classList.remove('active');
});
});


window.addEventListener('scroll',()=>{
let fromTop=window.scrollY+150;
links.forEach(link=>{
const section=document.querySelector(link.getAttribute('href'));
if(section.offsetTop<=fromTop&&section.offsetTop+section.offsetHeight>fromTop){
links.forEach(l=>l.classList.remove('active'));
link.classList.add('active');
}
});
});
 // Programme PDF viewer logic
 // Modal Gallery
  const modal = document.getElementById("modal");
      const modalImg = document.getElementById("modalImg");
      const images = document.querySelectorAll(".gallery-item img");

      images.forEach((img) => {
        img.addEventListener("click", () => {
          modal.style.display = "flex";
          modalImg.src = img.src;
        });
      });

      // Close when clicking anywhere
      modal.addEventListener("click", () => {
        modal.style.display = "none";
      });
 
