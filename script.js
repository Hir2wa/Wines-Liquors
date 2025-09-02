document.addEventListener("DOMContentLoaded", function () {
  const categoryItems = document.querySelectorAll(".category-item");

  categoryItems.forEach((item) => {
    item.addEventListener("mouseenter", function () {
      const category = this.getAttribute("data-category");
      const parentContent = this.closest(".mega-menu-content");
      const allGrids = parentContent.querySelectorAll(".products-grid");
      const targetGrid = parentContent.querySelector("#" + category);

      // Hide all grids
      allGrids.forEach((grid) => grid.classList.remove("active"));

      // Show target grid
      if (targetGrid) {
        targetGrid.classList.add("active");
      }
    });
  });

  const megaMenus = document.querySelectorAll(".mega-menu-content");
  megaMenus.forEach((menu) => {
    const firstCategory = menu.querySelector(".category-item");
    const firstGrid = menu.querySelector(".products-grid");
    if (firstCategory && firstGrid) {
      firstGrid.classList.add("active");
    }
  });
});

let currentSlide = 0;
const slides = document.querySelectorAll(".slide-show > div");

function showSlide(index) {
  slides.forEach((slide, i) => {
    slide.classList.toggle("active", i === index);
  });
}

function nextSlide() {
  currentSlide = (currentSlide + 1) % slides.length;
  showSlide(currentSlide);
}

// Show first slide initially
showSlide(currentSlide);

// Change every 5 seconds
setInterval(nextSlide, 5000);

document.querySelectorAll(".filter-title").forEach((button) => {
  button.addEventListener("click", () => {
    const content = button.nextElementSibling;
    content.style.display =
      content.style.display === "block" ? "none" : "block";
  });
});
s;
