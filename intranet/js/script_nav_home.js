const hamBurger = document.querySelector(".toggle-btn");

hamBurger.addEventListener("click", function () {
  // Alternar la clase 'expand' en la barra lateral
  document.querySelector("#sidebar").classList.toggle("expand");

  // También alternar la clase 'expanded' en el contenido principal
  document.querySelector(".main-content").classList.toggle("expanded");

  // También alternar la clase 'expanded' en el contenido principal
  document.querySelector(".main").classList.toggle("expanded");
  // También alternar la clase 'expanded' en el contenido principal
  document.querySelector(".header-home").classList.toggle("expanded");
});