// CORRECTED Mobile Navigation JavaScript

document.addEventListener("DOMContentLoaded", function () {
  console.log("Mobile sidebar script loading...");

  // Get elements
  const hamburgerBtn = document.querySelector(".mobile-menu-toggle");
  const closeBtn = document.querySelector(".mobile-close-btn");
  const mobileMenu = document.querySelector(".nav-down");
  let overlay = document.querySelector(".mobile-menu-overlay");
  const megaMenusAll = document.querySelectorAll(".mega-menu");

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

  // Open mobile sidebar function
  function openMobileMenu() {
    console.log("Opening mobile sidebar");
    if (mobileMenu) {
      mobileMenu.classList.add("active");
      overlay.classList.add("active");
      document.body.style.overflow = "hidden";
    }
  }

  // Close mobile sidebar function
  function closeMobileMenu() {
    console.log("Closing mobile sidebar");
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

  // Hamburger button event listener
  if (hamburgerBtn) {
    hamburgerBtn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      console.log("Hamburger clicked");
      openMobileMenu();
    });
  }

  // Close button event listener
  if (closeBtn) {
    closeBtn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      closeMobileMenu();
    });
  }

  // Overlay click event listener
  if (overlay) {
    overlay.addEventListener("click", closeMobileMenu);
  }

  // Handle mega menu clicks in mobile sidebar
  megaMenusAll.forEach((megaMenu) => {
    const mainLink = megaMenu.querySelector("a");
    const categoryItems = megaMenu.querySelectorAll(".category-item");

    if (mainLink) {
      mainLink.addEventListener("click", function (e) {
        if (window.innerWidth <= 768) {
          // Only prevent default for links that are just placeholders (#)
          // Allow actual navigation links (like Wine.html) to work
          if (mainLink.getAttribute("href") === "#") {
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
          // If it's a real link (like Wine.html), let it navigate normally
        }
      });
    }

    // Handle category clicks in mobile sidebar
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

  // Handle window resize - close sidebar and search on desktop
  window.addEventListener("resize", function () {
    if (window.innerWidth > 768) {
      closeMobileMenu();
      closeSearch(); // Also close search when switching to desktop
    }
  });

  console.log("Mobile sidebar script loaded completely");
});

document.addEventListener("DOMContentLoaded", function () {
  // Desktop mega menu functionality (unchanged)
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

  // Filter functionality (unchanged)
  document.querySelectorAll(".filter-title").forEach((button) => {
    button.addEventListener("click", () => {
      const content = button.nextElementSibling;
      if (content) {
        content.style.display =
          content.style.display === "block" ? "none" : "block";
      }
    });
  });

  // WORKING MOBILE SIDEBAR FUNCTIONALITY (from Test.html)
  console.log("Mobile sidebar script loading...");

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

  // Horizontal scroll functionality (unchanged)
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

  // Mobile search functionality
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
    console.log("Opening mobile sidebar");
    if (mobileMenu) {
      mobileMenu.classList.add("active");
      overlay.classList.add("active");
      document.body.style.overflow = "hidden";
    }
  }

  // Close mobile sidebar function
  function closeMobileMenu() {
    console.log("Closing mobile sidebar");
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

  // Bind mobile menu events function
  function bindMobileMenuEvents() {
    console.log("Binding mobile menu events");

    // Re-bind mega menu events
    megaMenusAll.forEach((megaMenu) => {
      const mainLink = megaMenu.querySelector("a");
      if (mainLink) {
        // Remove existing listeners to avoid duplicates
        mainLink.removeEventListener("click", handleMainLinkClick);
        mainLink.addEventListener("click", handleMainLinkClick);
      }
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

  // Add click outside functionality to close mobile search
  document.addEventListener("click", function (e) {
    // Only handle on mobile view
    if (window.innerWidth <= 768) {
      const searchBar = document.querySelector(".search-bar");
      const mobileSearchToggle = document.querySelector(
        ".mobile-search-toggle"
      );

      // Check if search is open and click is outside search area
      if (searchBar && searchBar.classList.contains("active")) {
        const isClickInsideSearch = searchBar.contains(e.target);
        const isClickOnToggle =
          mobileSearchToggle && mobileSearchToggle.contains(e.target);

        // If click is outside search bar and not on the toggle button, close search
        if (!isClickInsideSearch && !isClickOnToggle) {
          closeSearch();
        }
      }
    }
  });

  // Add scroll functionality to close mobile search and enhance sticky nav
  let scrollTimeout;
  window.addEventListener(
    "scroll",
    function () {
      const navAll = document.querySelector(".navAll");

      // Enhance sticky navigation shadow when scrolling
      if (navAll) {
        if (window.scrollY > 10) {
          navAll.style.boxShadow = "0 4px 20px rgba(0, 0, 0, 0.15)";
        } else {
          navAll.style.boxShadow = "0 2px 10px rgba(0, 0, 0, 0.1)";
        }
      }

      // Only handle mobile search closing on mobile view
      if (window.innerWidth <= 768) {
        const searchBar = document.querySelector(".search-bar");

        // Check if search is open
        if (searchBar && searchBar.classList.contains("active")) {
          // Clear existing timeout
          clearTimeout(scrollTimeout);

          // Set a small delay to avoid closing immediately on every scroll event
          scrollTimeout = setTimeout(function () {
            closeSearch();
          }, 150); // 150ms delay to allow for smooth scrolling
        }
      }
    },
    { passive: true }
  ); // Use passive listener for better performance

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
          // Only prevent default for links that are just placeholders (#)
          // Allow actual navigation links (like Wine.html) to work
          if (mainLink.getAttribute("href") === "#") {
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
          // If it's a real link (like Wine.html), let it navigate normally
        }
      });
    }

    // Handle category clicks in mobile sidebar
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

  // Handle window resize - close sidebar and search on desktop
  window.addEventListener("resize", function () {
    if (window.innerWidth > 768) {
      closeMobileMenu();
      closeSearch(); // Also close search when switching to desktop
    }
  });

  // Footer collapsible sections
  const sectionHeaders = document.querySelectorAll(".section-header");

  sectionHeaders.forEach((header) => {
    header.addEventListener("click", function () {
      const section = this.getAttribute("data-section");
      const content = document.getElementById(section + "-content");
      const toggle = this.querySelector(".section-toggle");

      // Close other sections
      sectionHeaders.forEach((otherHeader) => {
        if (otherHeader !== this) {
          otherHeader.classList.remove("active");
          const otherSection = otherHeader.getAttribute("data-section");
          const otherContent = document.getElementById(
            otherSection + "-content"
          );
          const otherToggle = otherHeader.querySelector(".section-toggle");

          if (otherContent) {
            otherContent.classList.remove("active");
          }
          if (otherToggle) {
            otherToggle.style.transform = "rotate(0deg)";
          }
        }
      });

      this.classList.toggle("active");
      if (content) {
        content.classList.toggle("active");
      }
      if (toggle) {
        toggle.style.transform = this.classList.contains("active")
          ? "rotate(45deg)"
          : "rotate(0deg)";
      }
    });
  });

  // Featured products interaction
  const featuredItems = document.querySelectorAll(".featured-item");

  featuredItems.forEach((item) => {
    item.addEventListener("click", function () {
      this.style.transform = "scale(0.98)";
      setTimeout(() => {
        this.style.transform = "";
      }, 150);

      console.log(
        "Featured item clicked:",
        this.querySelector("h4").textContent
      );
    });
  });

  console.log("Mobile sidebar script loaded completely");
});
