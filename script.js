// REPLACE your entire script.js with this corrected version

document.addEventListener("DOMContentLoaded", function () {
  // Desktop mega menu functionality
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

  // Slideshow functionality
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
  if (slides.length > 0) {
    showSlide(currentSlide);
    // Change every 5 seconds
    setInterval(nextSlide, 5000);
  }

  // Filter functionality
  document.querySelectorAll(".filter-title").forEach((button) => {
    button.addEventListener("click", () => {
      const content = button.nextElementSibling;
      if (content) {
        content.style.display =
          content.style.display === "block" ? "none" : "block";
      }
    });
  });

  // MOBILE MENU FUNCTIONALITY
  console.log("Mobile menu script loading...");

  // Get elements
  const hamburgerBtn = document.querySelector(".mobile-menu-toggle");
  const closeBtn = document.querySelector(".mobile-close-btn");
  const mobileMenu = document.querySelector(".nav-down");
  let overlay = document.querySelector(".mobile-menu-overlay");
  const megaMenusAll = document.querySelectorAll(".mega-menu");
  const mobileSearchToggle = document.querySelector(".mobile-search-toggle");
  const searchBar = document.querySelector(".search-bar");
  const searchClose = document.querySelector(".search-close");

  console.log("Elements found:", {
    hamburger: !!hamburgerBtn,
    closeBtn: !!closeBtn,
    mobileMenu: !!mobileMenu,
    overlay: !!overlay,
    megaMenus: megaMenusAll.length,
  });

  // Create overlay if it doesn't exist
  if (!overlay) {
    overlay = document.createElement("div");
    overlay.className = "mobile-menu-overlay";
    document.body.appendChild(overlay);
    console.log("Overlay created");
  }

  // Discover New & Noteworthy horizontal scroll with chevrons (mobile)
  const discoverList = document.getElementById("discover-list");
  const discoverPrev = document.querySelector(".discover-prev");
  const discoverNext = document.querySelector(".discover-next");

  function scrollDiscover(direction) {
    if (!discoverList) return;
    const amount = Math.min(discoverList.clientWidth * 0.8, 320);
    discoverList.scrollBy({ left: direction * amount, behavior: "smooth" });
  }

  if (discoverPrev) {
    discoverPrev.addEventListener("click", () => scrollDiscover(-1));
  }
  if (discoverNext) {
    discoverNext.addEventListener("click", () => scrollDiscover(1));
  }

  // Popular categories, Wines, Spirits scrollers
  function bindScroller(listId, prevSelector, nextSelector, widthFactor = 0.8) {
    const list = document.getElementById(listId);
    const prev = document.querySelector(prevSelector);
    const next = document.querySelector(nextSelector);
    if (!list) return;
    const amount = () => Math.min(list.clientWidth * widthFactor, 320);
    if (prev)
      prev.addEventListener("click", () =>
        list.scrollBy({ left: -amount(), behavior: "smooth" })
      );
    if (next)
      next.addEventListener("click", () =>
        list.scrollBy({ left: amount(), behavior: "smooth" })
      );
  }

  bindScroller("popular-list", ".popular-prev", ".popular-next", 0.7);
  bindScroller("wines-list", ".wines-prev", ".wines-next", 0.8);
  bindScroller("spirits-list", ".spirits-prev", ".spirits-next", 0.8);

  // Mobile search toggle
  function openSearch() {
    if (searchBar) {
      searchBar.classList.add("active");
      const input = searchBar.querySelector(".input-search-field");
      if (input) {
        setTimeout(() => input.focus(), 0);
      }
    }
    const navAll = document.querySelector(".navAll");
    if (navAll) {
      navAll.classList.add("search-open");
    }
  }

  function closeSearch() {
    if (searchBar) {
      searchBar.classList.remove("active");
    }
    const navAll = document.querySelector(".navAll");
    if (navAll) {
      navAll.classList.remove("search-open");
    }
  }

  // Open mobile menu function
  function openMobileMenu() {
    console.log("Opening mobile menu");
    if (mobileMenu) {
      mobileMenu.classList.add("active");
      overlay.classList.add("active");
      document.body.style.overflow = "hidden";
    }
  }

  // Close mobile menu function
  function closeMobileMenu() {
    console.log("Closing mobile menu");
    if (mobileMenu) {
      mobileMenu.classList.remove("active");
      overlay.classList.remove("active");
      document.body.style.overflow = "";

      // Close all open mega menus
      megaMenusAll.forEach((menu) => {
        menu.classList.remove("active");
        const productsContent = menu.querySelectorAll(".products-content");
        productsContent.forEach((content) =>
          content.classList.remove("active")
        );
      });
    }
  }

  // Add event listeners
  if (hamburgerBtn) {
    hamburgerBtn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      console.log("Hamburger clicked");
      openMobileMenu();
    });
  }

  if (mobileSearchToggle) {
    mobileSearchToggle.addEventListener("click", function (e) {
      e.preventDefault();
      if (window.innerWidth <= 768) {
        if (searchBar && !searchBar.classList.contains("active")) {
          openSearch();
        } else {
          closeSearch();
        }
      }
    });
  }

  if (searchClose) {
    searchClose.addEventListener("click", function (e) {
      e.preventDefault();
      closeSearch();
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      closeMobileMenu();
    });
  }

  if (overlay) {
    overlay.addEventListener("click", closeMobileMenu);
  }

  // Handle mega menu clicks in mobile
  megaMenusAll.forEach((megaMenu) => {
    const mainLink = megaMenu.querySelector("a");
    const categoryItems = megaMenu.querySelectorAll(".category-item");

    if (mainLink) {
      mainLink.addEventListener("click", function (e) {
        if (window.innerWidth <= 768) {
          e.preventDefault();
          console.log("Mega menu clicked in mobile");

          // Close other mega menus
          megaMenusAll.forEach((otherMenu) => {
            if (otherMenu !== megaMenu) {
              otherMenu.classList.remove("active");
              const otherProductsContent =
                otherMenu.querySelectorAll(".products-content");
              otherProductsContent.forEach((content) =>
                content.classList.remove("active")
              );
            }
          });

          // Toggle current mega menu
          megaMenu.classList.toggle("active");
        }
      });
    }

    // Handle category clicks in mobile
    categoryItems.forEach((categoryItem) => {
      categoryItem.addEventListener("click", function () {
        if (window.innerWidth <= 768) {
          const targetCategory = categoryItem.dataset.category;
          const productsContent = megaMenu.querySelector(".products-content");
          const targetGrid = megaMenu.querySelector(`#${targetCategory}`);

          console.log("Category clicked:", targetCategory);

          // Show products content
          if (productsContent) {
            productsContent.classList.add("active");
          }

          // Hide all product grids
          const allGrids = megaMenu.querySelectorAll(".products-grid");
          allGrids.forEach((grid) => grid.classList.remove("active"));

          // Show target grid
          if (targetGrid) {
            targetGrid.classList.add("active");
          }

          // Update active category
          categoryItems.forEach((item) => item.classList.remove("active"));
          categoryItem.classList.add("active");
        }
      });
    });
  });

  // Handle window resize
  window.addEventListener("resize", function () {
    if (window.innerWidth > 768) {
      closeMobileMenu();
    }
  });

  // Test hamburger menu on page load
  console.log("Script loaded completely");

  // Add a test click handler to verify JavaScript is working
  if (hamburgerBtn) {
    console.log("Hamburger button found and ready");
  } else {
    console.error("Hamburger button not found!");
  }
});
