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


 // Navbar end------------------------------

 
