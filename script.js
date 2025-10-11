// CORRECTED Mobile Navigation JavaScript

// Wine Filter Toggle Function
function toggleWineFilter(button) {
  const filterGroup = button.closest(".wine-filter-group");
  filterGroup.classList.toggle("active");
}

// Filter Functionality
function applyFilters() {
  const checkboxes = document.querySelectorAll(
    '.wine-filter-content input[type="checkbox"]'
  );
  const products = document.querySelectorAll(".wine-product-card");
  const categories = document.querySelectorAll(".wine-category-section");

  // Get selected filters
  const selectedFilters = {
    category: [],
    price: [],
    size: [],
  };

  checkboxes.forEach((checkbox) => {
    if (checkbox.checked) {
      const filterType = checkbox.getAttribute("data-filter");
      const filterValue = checkbox.getAttribute("data-value");
      if (filterType && filterValue) {
        selectedFilters[filterType].push(filterValue);
      }
    }
  });

  // Filter products
  products.forEach((product) => {
    let showProduct = true;

    // Check category filter
    if (selectedFilters.category.length > 0) {
      const productCategory = product.getAttribute("data-category");
      if (!selectedFilters.category.includes(productCategory)) {
        showProduct = false;
      }
    }

    // Check price filter
    if (selectedFilters.price.length > 0 && showProduct) {
      const productPrice = product.getAttribute("data-price");
      if (!selectedFilters.price.includes(productPrice)) {
        showProduct = false;
      }
    }

    // Check size filter
    if (selectedFilters.size.length > 0 && showProduct) {
      const productSize = product.getAttribute("data-size");
      if (!selectedFilters.size.includes(productSize)) {
        showProduct = false;
      }
    }

    // Show/hide product
    if (showProduct) {
      product.style.display = "block";
      product.classList.remove("filtered-out");
    } else {
      product.style.display = "none";
      product.classList.add("filtered-out");
    }
  });

  // Hide empty category sections
  categories.forEach((category) => {
    const visibleProducts = category.querySelectorAll(
      ".wine-product-card:not(.filtered-out)"
    );
    if (visibleProducts.length === 0) {
      category.style.display = "none";
    } else {
      category.style.display = "block";
    }
  });

  // Update filter counts
  updateFilterCounts();
}

// Clear all filters
function clearAllFilters() {
  const checkboxes = document.querySelectorAll(
    '.wine-filter-content input[type="checkbox"]'
  );
  checkboxes.forEach((checkbox) => {
    checkbox.checked = false;
  });
  applyFilters();
}

// Update filter counts
function updateFilterCounts() {
  const filterGroups = document.querySelectorAll(".wine-filter-group");

  filterGroups.forEach((group) => {
    const checkboxes = group.querySelectorAll('input[type="checkbox"]');
    const checkedCount = Array.from(checkboxes).filter(
      (cb) => cb.checked
    ).length;
    const totalCount = checkboxes.length;

    const title = group.querySelector(".wine-filter-title span");
    if (title && checkedCount < totalCount) {
      title.textContent =
        title.textContent.replace(/ \(\d+\/\d+\)/, "") +
        ` (${checkedCount}/${totalCount})`;
    } else if (title) {
      title.textContent = title.textContent.replace(/ \(\d+\/\d+\)/, "");
    }
  });
}

// Add event listeners to filter checkboxes
function initializeFilters() {
  const checkboxes = document.querySelectorAll(
    '.wine-filter-content input[type="checkbox"]'
  );
  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", applyFilters);
  });

  // Auto-assign data attributes to product cards
  autoAssignDataAttributes();

  // Apply initial filters
  applyFilters();
}

// Auto-assign data attributes to product cards based on their content
function autoAssignDataAttributes() {
  const productCards = document.querySelectorAll(".wine-product-card");

  productCards.forEach((card) => {
    // Get product info
    const productName =
      card.querySelector("h4")?.textContent?.toLowerCase() || "";
    const productPrice = card.querySelector(".wine-price")?.textContent || "";
    const productSize =
      card.querySelector(".wine-size")?.textContent?.toLowerCase() || "";

    // Determine category based on section or product name
    const categorySection = card.closest(".wine-category-section");
    let category = "wine";

    if (categorySection) {
      const sectionTitle =
        categorySection
          .querySelector(".wine-category-title")
          ?.textContent?.toLowerCase() || "";
      if (sectionTitle.includes("champagne")) category = "champagne";
      else if (sectionTitle.includes("cognac")) category = "cognac";
      else if (sectionTitle.includes("whiskey")) category = "whiskey";
      else if (sectionTitle.includes("tequila")) category = "tequila";
      else if (sectionTitle.includes("gin")) category = "gin";
      else if (sectionTitle.includes("vodka")) category = "vodka";
      else if (sectionTitle.includes("wine bottle")) category = "wine-bottle";
      else if (sectionTitle.includes("sparkling")) category = "sparkling-wine";
      else if (sectionTitle.includes("wine box")) category = "wine-box";
      else if (sectionTitle.includes("beer")) {
        // For beer page, determine category based on product name
        if (
          productName.includes("fanta") ||
          productName.includes("inyage") ||
          productName.includes("bavaria 0")
        ) {
          category = "non-alcoholic";
        } else if (productName.includes("leffe")) {
          category = "ale";
        } else {
          category = "lager";
        }
      }
    }

    // Determine price range
    let priceRange = "under-50k";
    const priceNum = parseInt(productPrice.replace(/[^\d]/g, ""));

    // Special handling for beer products (lower prices)
    if (sectionTitle && sectionTitle.includes("beer")) {
      if (priceNum >= 5000) priceRange = "5k-10k";
      else priceRange = "under-5k";
    } else {
      // Original logic for wine/spirits
      if (priceNum >= 200000) priceRange = "over-200k";
      else if (priceNum >= 100000) priceRange = "100k-200k";
      else if (priceNum >= 50000) priceRange = "50k-100k";
      else if (priceNum >= 100000) priceRange = "over-100k";
    }

    // Determine size
    let size = "750ml";
    if (productSize.includes("700ml")) size = "700ml";
    else if (productSize.includes("1.5l")) size = "1.5l";
    else if (productSize.includes("3l")) size = "3l";
    else if (productSize.includes("330ml")) size = "330ml";
    else if (productSize.includes("500ml")) size = "500ml";
    else if (productSize.includes("variable")) size = "variable";

    // Set data attributes
    card.setAttribute("data-category", category);
    card.setAttribute("data-price", priceRange);
    card.setAttribute("data-size", size);
  });
}

// Cart Functionality
let cartCount = 0;

function addToCart(productName, productPrice) {
  // Increment cart count
  cartCount++;

  // Store cart item in localStorage
  const cartItem = {
    name: productName,
    price: productPrice,
    quantity: 1, // Default quantity
    id: Date.now(), // Unique ID for each item
  };

  // Get existing cart items
  let cartItems = JSON.parse(localStorage.getItem("cartItems")) || [];
  cartItems.push(cartItem);
  localStorage.setItem("cartItems", JSON.stringify(cartItems));

  // Update cart count display
  updateCartCount();

  // Add bounce animation to the product card
  const productCard = event.target.closest(
    ".wine-product-card, .champagne-product-card"
  );
  if (productCard) {
    productCard.classList.add("bounce-animation");
    setTimeout(() => {
      productCard.classList.remove("bounce-animation");
    }, 600);
  }

  // Show success message
  showCartMessage(`${productName} added to cart!`);

  // Store in localStorage for persistence
  localStorage.setItem("cartCount", cartCount);
}

function updateCartCount() {
  const cartCountElements = document.querySelectorAll(".cart-count");
  cartCountElements.forEach((element) => {
    element.textContent = cartCount;
    element.classList.add("updated");

    // Add bounce animation to cart icon
    const cartIcon = element.closest(
      ".sub-cart, .fas.fa-shopping-cart"
    ).parentElement;
    if (cartIcon) {
      cartIcon.classList.add("cart-bounce");
      setTimeout(() => {
        cartIcon.classList.remove("cart-bounce");
        element.classList.remove("updated");
      }, 500);
    }
  });
}

function showCartMessage(message) {
  // Create toast notification
  const toast = document.createElement("div");
  toast.className = "cart-toast";
  toast.textContent = message;
  toast.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: #28a745;
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 10000;
    font-size: 14px;
    font-weight: 500;
    transform: translateX(100%);
    transition: transform 0.3s ease;
  `;

  document.body.appendChild(toast);

  // Animate in
  setTimeout(() => {
    toast.style.transform = "translateX(0)";
  }, 100);

  // Remove after 3 seconds
  setTimeout(() => {
    toast.style.transform = "translateX(100%)";
    setTimeout(() => {
      document.body.removeChild(toast);
    }, 300);
  }, 3000);
}

// Initialize cart count from localStorage
function initializeCart() {
  const savedCount = localStorage.getItem("cartCount");
  if (savedCount) {
    cartCount = parseInt(savedCount);
    updateCartCount();
  }
}

// Flash message function
function showFlashMessage(message, type = "info") {
  // Remove any existing flash message
  const existingMessage = document.querySelector(".flash-message");
  if (existingMessage) {
    existingMessage.remove();
  }

  // Create flash message element
  const flashMessage = document.createElement("div");
  flashMessage.className = `flash-message flash-${type}`;
  flashMessage.innerHTML = `
    <div class="flash-content">
      <i class="fas fa-${
        type === "success" ? "check-circle" : "info-circle"
      }"></i>
      <span>${message}</span>
    </div>
  `;

  // Add styles
  flashMessage.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: ${type === "success" ? "#28a745" : "#17a2b8"};
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 10000;
    font-weight: 500;
    max-width: 400px;
    animation: slideInRight 0.3s ease-out;
  `;

  // Add animation styles
  const style = document.createElement("style");
  style.textContent = `
    @keyframes slideInRight {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
    .flash-content {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .flash-content i {
      font-size: 18px;
    }
  `;
  document.head.appendChild(style);

  // Add to page
  document.body.appendChild(flashMessage);

  // Auto remove after 3 seconds
  setTimeout(() => {
    if (flashMessage.parentNode) {
      flashMessage.style.animation = "slideInRight 0.3s ease-out reverse";
      setTimeout(() => {
        flashMessage.remove();
      }, 300);
    }
  }, 3000);
}

// Handle profile click with validation
function handleProfileClick() {
  const userData = localStorage.getItem("userData");

  if (!userData) {
    // User not logged in - show message and redirect to create account
    showFlashMessage(
      "Please create an account to access your profile.",
      "success"
    );
    setTimeout(() => {
      window.location.href = "Register.html";
    }, 2000);
  } else {
    // User is logged in - go directly to profile
    window.location.href = "Profile.html";
  }
}

// Logout function
function logout() {
  localStorage.removeItem("userData");
  localStorage.removeItem("sessionToken");
  localStorage.removeItem("user_logged_in");

  // Refresh the page to update the UI
  window.location.reload();
}

document.addEventListener("DOMContentLoaded", function () {
  console.log("Mobile sidebar script loading...");

  // Initialize cart functionality
  initializeCart();

  // Initialize filters
  initializeFilters();

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

    // Handle mega menu positioning to prevent overflow
    const megaMenuParent = menu.closest(".mega-menu");
    if (megaMenuParent) {
      megaMenuParent.addEventListener("mouseenter", function () {
        // Small delay to ensure menu is rendered before positioning
        setTimeout(() => {
          const rect = this.getBoundingClientRect();
          const viewportWidth = window.innerWidth;
          const menuWidth = 800; // Approximate menu width

          // Calculate ideal center position
          const centerX = rect.left + rect.width / 2;
          const menuLeft = centerX - menuWidth / 2;
          const menuRight = centerX + menuWidth / 2;

          // Reset styles first
          menu.style.left = "50%";
          menu.style.right = "auto";
          menu.style.transform = "translateX(-50%)";
          menu.style.marginLeft = "0";
          menu.style.marginRight = "0";

          // Check if menu would overflow on the right
          if (menuRight > viewportWidth - 20) {
            menu.style.left = "auto";
            menu.style.right = "20px";
            menu.style.transform = "none";
          }
          // Check if menu would overflow on the left
          else if (menuLeft < 20) {
            menu.style.left = "20px";
            menu.style.transform = "none";
          }
          // If menu is too wide for viewport, constrain it
          else if (menuWidth > viewportWidth - 40) {
            menu.style.maxWidth = `${viewportWidth - 40}px`;
            menu.style.left = "50%";
            menu.style.transform = "translateX(-50%)";
          }
        }, 10);
      });
    }
  });

  // Handle window resize to reposition mega menus
  window.addEventListener("resize", function () {
    const activeMenus = document.querySelectorAll(
      ".mega-menu:hover .mega-menu-content"
    );
    activeMenus.forEach((menu) => {
      const megaMenuParent = menu.closest(".mega-menu");
      if (megaMenuParent) {
        const rect = megaMenuParent.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const menuWidth = 800;

        const centerX = rect.left + rect.width / 2;
        const menuLeft = centerX - menuWidth / 2;
        const menuRight = centerX + menuWidth / 2;

        if (menuRight > viewportWidth - 20) {
          menu.style.left = "auto";
          menu.style.right = "20px";
          menu.style.transform = "none";
        } else if (menuLeft < 20) {
          menu.style.left = "20px";
          menu.style.transform = "none";
        } else {
          menu.style.left = "50%";
          menu.style.right = "auto";
          menu.style.transform = "translateX(-50%)";
        }
      }
    });
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
  window.openSearch = function () {
    const searchBar = document.querySelector(".search-bar");
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
  };

  // Make closeSearch globally available
  window.closeSearch = function () {
    const searchBar = document.querySelector(".search-bar");
    if (searchBar) {
      searchBar.classList.remove("active");
    }
    const navAll = document.querySelector(".navAll");
    if (navAll) {
      navAll.classList.remove("search-open");
    }
  };

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

// ========================================
// SEARCH FUNCTIONALITY
// ========================================

// Search data structure with categories and products
const searchData = {
  categories: [
    {
      name: "wine",
      page: "Wine.html",
      keywords: [
        "wine",
        "wine bottle",
        "wine box",
        "sparkling wine",
        "prosecco",
        "champagne",
      ],
    },
    {
      name: "whiskey",
      page: "Whiskey.html",
      keywords: ["whiskey", "whisky", "bourbon", "scotch", "irish whiskey"],
    },
    {
      name: "beer",
      page: "Beer.html",
      keywords: ["beer", "ale", "lager", "stout", "ipa"],
    },
    {
      name: "champagne",
      page: "Champagne.html",
      keywords: ["champagne", "cognac", "sparkling", "bubbly"],
    },
    {
      name: "gin",
      page: "GinVodka.html",
      keywords: ["gin", "vodka", "gin and tonic"],
    },
    {
      name: "vodka",
      page: "GinVodka.html",
      keywords: ["vodka", "gin", "clear spirits"],
    },
    {
      name: "rum",
      page: "Rum.html",
      keywords: ["rum", "dark rum", "white rum", "spiced rum"],
    },
    {
      name: "tequila",
      page: "Whiskey.html",
      keywords: ["tequila", "margarita", "mexican spirits"],
    },
    {
      name: "single malt",
      page: "SingleMalt.html",
      keywords: ["single malt", "scotch", "glenfiddich", "glenlivet"],
    },
  ],
  products: [
    // Wine products
    { name: "cellar cask", category: "wine", page: "Wine.html" },
    { name: "four cousins", category: "wine", page: "Wine.html" },
    { name: "grand verdus", category: "wine", page: "Wine.html" },
    { name: "jacobs", category: "wine", page: "Wine.html" },
    { name: "nederburg", category: "wine", page: "Wine.html" },
    { name: "pinta negra", category: "wine", page: "Wine.html" },
    { name: "baron sparkling", category: "wine", page: "Wine.html" },
    { name: "canitelli prosecco", category: "wine", page: "Wine.html" },
    { name: "freixnet", category: "wine", page: "Wine.html" },
    { name: "maison castel", category: "wine", page: "Wine.html" },
    { name: "masottina", category: "wine", page: "Wine.html" },
    { name: "signore prosecco", category: "wine", page: "Wine.html" },

    // Whiskey products
    { name: "chivas", category: "whiskey", page: "Whiskey.html" },
    { name: "jack daniels", category: "whiskey", page: "Whiskey.html" },
    { name: "ballantine", category: "whiskey", page: "Whiskey.html" },
    { name: "black label", category: "whiskey", page: "Whiskey.html" },
    { name: "red label", category: "whiskey", page: "Whiskey.html" },
    { name: "double black", category: "whiskey", page: "Whiskey.html" },
    { name: "william lowson", category: "whiskey", page: "Whiskey.html" },

    // Tequila products
    { name: "jose cuervo", category: "tequila", page: "Whiskey.html" },
    { name: "olmeca", category: "tequila", page: "Whiskey.html" },
    { name: "patron", category: "tequila", page: "Whiskey.html" },
    { name: "camino", category: "tequila", page: "Whiskey.html" },

    // Gin & Vodka products
    { name: "beefeater", category: "gin", page: "GinVodka.html" },
    { name: "gordon", category: "gin", page: "GinVodka.html" },
    { name: "tanqueray", category: "gin", page: "GinVodka.html" },
    { name: "hendrick", category: "gin", page: "GinVodka.html" },
    { name: "absolute", category: "vodka", page: "GinVodka.html" },
    { name: "smirnoff", category: "vodka", page: "GinVodka.html" },
    { name: "belvedere", category: "vodka", page: "GinVodka.html" },
    { name: "ciroc", category: "vodka", page: "GinVodka.html" },
    { name: "stoli", category: "vodka", page: "GinVodka.html" },

    // Rum products
    { name: "bacardi", category: "rum", page: "Rum.html" },
    { name: "captain morgan", category: "rum", page: "Rum.html" },
    { name: "havana club", category: "rum", page: "Rum.html" },
    { name: "malibu", category: "rum", page: "Rum.html" },

    // Single Malt products
    { name: "glenfiddich", category: "single malt", page: "SingleMalt.html" },
    { name: "glenlivet", category: "single malt", page: "SingleMalt.html" },

    // Champagne products
    { name: "moet", category: "champagne", page: "Champagne.html" },
    { name: "veuve clicquot", category: "champagne", page: "Champagne.html" },
    { name: "ruinart", category: "champagne", page: "Champagne.html" },
    { name: "courvoisier", category: "champagne", page: "Champagne.html" },
    { name: "hennessy", category: "champagne", page: "Champagne.html" },
    { name: "martel", category: "champagne", page: "Champagne.html" },
    { name: "remy martin", category: "champagne", page: "Champagne.html" },

    // Beer products
    { name: "bavaria", category: "beer", page: "Beer.html" },
    { name: "brok", category: "beer", page: "Beer.html" },
    { name: "carlsberg", category: "beer", page: "Beer.html" },
    { name: "exo", category: "beer", page: "Beer.html" },
    { name: "guarana", category: "beer", page: "Beer.html" },
    { name: "leffe", category: "beer", page: "Beer.html" },
    { name: "savanna", category: "beer", page: "Beer.html" },
    { name: "stella artois", category: "beer", page: "Beer.html" },
  ],
};

// Search function with improved regex matching
function performSearch(query) {
  if (!query || query.trim().length < 2) {
    return null;
  }

  const searchTerm = query.toLowerCase().trim();

  // Create regex pattern for flexible matching
  const searchPattern = new RegExp(
    searchTerm.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"),
    "i"
  );

  // First, check for exact category matches
  for (const category of searchData.categories) {
    if (
      searchPattern.test(category.name) ||
      category.keywords.some((keyword) => searchPattern.test(keyword))
    ) {
      return {
        type: "category",
        name: category.name,
        page: category.page,
        query: searchTerm,
      };
    }
  }

  // Then, check for product matches with regex
  for (const product of searchData.products) {
    if (searchPattern.test(product.name)) {
      return {
        type: "product",
        name: product.name,
        category: product.category,
        page: product.page,
        query: searchTerm,
      };
    }
  }

  // Fuzzy matching for products (partial word matches)
  const fuzzyPattern = new RegExp(searchTerm.split("").join(".*"), "i");
  for (const product of searchData.products) {
    if (fuzzyPattern.test(product.name)) {
      return {
        type: "product",
        name: product.name,
        category: product.category,
        page: product.page,
        query: searchTerm,
      };
    }
  }

  return null;
}

// Handle search functionality
function handleSearch() {
  const searchInputs = document.querySelectorAll(
    ".input-search-field, .sub-search-input"
  );

  searchInputs.forEach((input) => {
    input.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        e.stopPropagation();

        const query = this.value.trim();

        if (query.length >= 2) {
          const result = performSearch(query);

          if (result) {
            // Store search result in sessionStorage for filtering
            sessionStorage.setItem("searchResult", JSON.stringify(result));
            sessionStorage.setItem("searchQuery", query);

            // Clear the input field
            this.value = "";

            // Redirect to the appropriate page
            window.location.href = result.page;
          } else {
            // Show no results message
            showNoResultsMessage(query);
            // Clear the input field
            this.value = "";
            // Keep focus on input for mobile
            this.focus();
          }
        } else {
          // Show message for short queries
          showNoResultsMessage("Please enter at least 2 characters");
          // Clear the input field
          this.value = "";
          this.focus();
        }
      }
    });
  });

  // Handle search button clicks
  const searchButtons = document.querySelectorAll(".searchButton");
  searchButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();

      // Find the search input - try multiple selectors for mobile compatibility
      let searchInput =
        this.parentElement.querySelector(".input-search-field") ||
        this.parentElement.querySelector(".sub-search-input") ||
        document.querySelector(".input-search-field") ||
        document.querySelector(".sub-search-input");

      if (searchInput) {
        const query = searchInput.value.trim();

        // Don't clear the input immediately on mobile
        if (query.length >= 2) {
          const result = performSearch(query);

          if (result) {
            // Store search result in sessionStorage for filtering
            sessionStorage.setItem("searchResult", JSON.stringify(result));
            sessionStorage.setItem("searchQuery", query);

            // Clear the input field
            searchInput.value = "";

            // Redirect to the appropriate page
            window.location.href = result.page;
          } else {
            // Show no results message in a better way
            showNoResultsMessage(query);
            // Clear the input field
            searchInput.value = "";
            // Keep focus on input for mobile
            searchInput.focus();
          }
        } else {
          // Show message for short queries
          showNoResultsMessage("Please enter at least 2 characters");
          // Clear the input field
          searchInput.value = "";
          searchInput.focus();
        }
      }
    });
  });
}

// Apply search filters when page loads
function applySearchFilters() {
  const searchResult = sessionStorage.getItem("searchResult");
  const searchQuery = sessionStorage.getItem("searchQuery");

  if (searchResult && searchQuery) {
    const result = JSON.parse(searchResult);

    // Clear previous search
    sessionStorage.removeItem("searchResult");
    sessionStorage.removeItem("searchQuery");

    // Apply filters based on search result
    if (result.type === "product") {
      // Filter to show only matching products
      filterProductsByName(result.name);
    } else if (result.type === "category") {
      // Filter to show only matching category
      filterProductsByCategory(result.name);
    }

    // Show search results message
    showSearchResultsMessage(searchQuery, result);
  }
}

// Show search results message
function showSearchResultsMessage(query, result) {
  const message = document.createElement("div");
  message.className = "search-results-message";
  message.innerHTML = `
    <div class="search-message-content">
      <h3>Search Results for "${query}"</h3>
      <p>Found ${result.type === "product" ? "product" : "category"}: ${
    result.name
  }</p>
      <button onclick="clearSearchResults()" class="clear-search-btn">Clear Search</button>
    </div>
  `;

  // Insert message at the top of the main content
  const mainContent = document.querySelector(
    ".wine-main-content, .spirits-container, .discover"
  );
  if (mainContent) {
    mainContent.insertBefore(message, mainContent.firstChild);
  }
}

// Clear search results
function clearSearchResults() {
  const products = document.querySelectorAll(
    ".wine-product-card, .spirit-card"
  );
  products.forEach((product) => {
    product.style.display = "block";
    product.classList.remove("search-highlight");
  });

  const message = document.querySelector(".search-results-message");
  if (message) {
    message.remove();
  }
}

// Show no results message
function showNoResultsMessage(query) {
  // Create a temporary message element
  const message = document.createElement("div");
  message.className = "no-results-message";
  message.innerHTML = `
    <div class="no-results-content">
      <h3>No results found for "${query}"</h3>
      <p>Try searching for:</p>
      <ul>
        <li>Categories: wine, whiskey, beer, gin, vodka, rum, tequila</li>
        <li>Brands: chivas, jack daniels, bacardi, absolute, beefeater</li>
        <li>Types: sparkling wine, single malt, cognac</li>
      </ul>
    </div>
  `;

  // Insert message at the top of the page
  const mainContent = document.querySelector(
    ".navAll, .wine-page-container, .spirits-container"
  );
  if (mainContent) {
    mainContent.insertBefore(message, mainContent.firstChild);

    // Remove message after 5 seconds
    setTimeout(() => {
      if (message.parentNode) {
        message.remove();
      }
    }, 5000);
  }
}

// Improved product filtering with regex
function filterProductsByName(productName) {
  const products = document.querySelectorAll(
    ".wine-product-card, .spirit-card"
  );

  // Create regex pattern for flexible matching
  const searchPattern = new RegExp(
    productName.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"),
    "i"
  );

  products.forEach((product) => {
    const productTitle = product.querySelector("h4, .spirit-name");
    if (productTitle) {
      const title = productTitle.textContent;
      if (searchPattern.test(title)) {
        product.style.display = "block";
        product.classList.add("search-highlight");
      } else {
        product.style.display = "none";
      }
    }
  });
}

// Improved category filtering with regex
function filterProductsByCategory(categoryName) {
  const products = document.querySelectorAll(
    ".wine-product-card, .spirit-card"
  );

  // Create regex pattern for flexible matching
  const searchPattern = new RegExp(
    categoryName.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"),
    "i"
  );

  products.forEach((product) => {
    const productCategory =
      product.getAttribute("data-category") ||
      product
        .closest(".wine-category-section")
        ?.querySelector(".wine-category-title")?.textContent;

    if (productCategory && searchPattern.test(productCategory)) {
      product.style.display = "block";
      product.classList.add("search-highlight");
    } else {
      product.style.display = "none";
    }
  });
}

// ========================================
// BACK TO TOP BUTTON FUNCTIONALITY
// ========================================

document.addEventListener("DOMContentLoaded", function () {
  // Initialize search functionality
  handleSearch();
  applySearchFilters();

  const backToTopButton = document.getElementById("backToTop");

  if (backToTopButton) {
    // Show/hide button based on scroll position
    window.addEventListener("scroll", function () {
      if (window.pageYOffset > 300) {
        backToTopButton.classList.add("show");
      } else {
        backToTopButton.classList.remove("show");
      }
    });

    // Smooth scroll to top when clicked
    backToTopButton.addEventListener("click", function () {
      // Add bouncing animation
      backToTopButton.classList.add("bounce");

      // Remove bounce class after animation completes
      setTimeout(() => {
        backToTopButton.classList.remove("bounce");
      }, 1000);

      // Smooth scroll to top
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    });

    // Remove bounce class if user scrolls while animation is playing
    window.addEventListener("scroll", function () {
      if (backToTopButton.classList.contains("bounce")) {
        backToTopButton.classList.remove("bounce");
      }
    });
  }

  // Mobile search functionality
  const mobileSearchToggle = document.querySelector(".mobile-search-toggle");
  const mobileSearchOverlay = document.querySelector(".mobile-search-overlay");
  const searchBar = document.querySelector(".search-bar");
  const searchInput = document.querySelector(".input-search-field");
  const searchClose = document.querySelector(".search-close");
  const body = document.body;

  if (mobileSearchToggle && mobileSearchOverlay && searchBar) {
    // Open mobile search
    mobileSearchToggle.addEventListener("click", function () {
      body.classList.add("mobile-search-active");
      setTimeout(() => {
        if (searchInput) {
          searchInput.focus();
        }
      }, 100);
    });

    // Close mobile search
    function closeMobileSearch() {
      body.classList.remove("mobile-search-active");
      if (searchInput) {
        searchInput.blur();
        searchInput.value = "";
      }
    }

    // Close on overlay click
    mobileSearchOverlay.addEventListener("click", closeMobileSearch);

    // Close on close button click
    if (searchClose) {
      searchClose.addEventListener("click", closeMobileSearch);
    }

    // Close on escape key
    document.addEventListener("keydown", function (e) {
      if (
        e.key === "Escape" &&
        body.classList.contains("mobile-search-active")
      ) {
        closeMobileSearch();
      }
    });

    // Close when search is performed
    const searchButton = document.querySelector(".searchButton");
    if (searchButton) {
      searchButton.addEventListener("click", function () {
        // Clear the search input
        if (searchInput) {
          searchInput.value = "";
        }
        setTimeout(() => {
          closeMobileSearch();
        }, 500);
      });
    }

    // Close on enter key in search input
    if (searchInput) {
      searchInput.addEventListener("keypress", function (e) {
        if (e.key === "Enter") {
          // Clear the search input
          this.value = "";
          setTimeout(() => {
            closeMobileSearch();
          }, 500);
        }
      });
    }
  }
});

// Function to handle size selection and price updates
function addToCartWithSize(productName, defaultPrice, defaultSize) {
  // Find the product card
  const productCard = event.target.closest(".wine-product-card");
  const sizeOptions = productCard.querySelectorAll('input[type="radio"]');
  const priceElement = productCard.querySelector(".wine-price");
  const addToCartButton = productCard.querySelector(".wine-add-to-cart");

  // Find selected size
  let selectedSize = defaultSize;
  let selectedPrice = defaultPrice;

  sizeOptions.forEach((option) => {
    if (option.checked) {
      selectedSize = option.value;
      // Extract price from the label text
      const labelText = option.nextElementSibling.textContent;
      const priceMatch = labelText.match(/(\d{1,3}(?:,\d{3})*)frw/);
      if (priceMatch) {
        selectedPrice = priceMatch[1] + "frw";
      }
    }
  });

  // Update the displayed price
  priceElement.textContent = selectedPrice;

  // Update the onclick function with new price
  addToCartButton.setAttribute(
    "onclick",
    `addToCartWithSize('${productName}', '${selectedPrice}', '${selectedSize}'); return false;`
  );

  // Add to cart with size information
  addToCart(`${productName} (${selectedSize})`, selectedPrice);
}

// Add event listeners for size selection
document.addEventListener("DOMContentLoaded", function () {
  // Add event listeners to all size radio buttons
  const sizeRadios = document.querySelectorAll(
    '.size-options input[type="radio"]'
  );
  sizeRadios.forEach((radio) => {
    radio.addEventListener("change", function () {
      const productCard = this.closest(".wine-product-card");
      const priceElement = productCard.querySelector(".wine-price");
      const addToCartButton = productCard.querySelector(".wine-add-to-cart");
      const productName = productCard.querySelector("h4").textContent;

      // Extract price from the selected option
      const labelText = this.nextElementSibling.textContent;
      const priceMatch = labelText.match(/(\d{1,3}(?:,\d{3})*)frw/);
      if (priceMatch) {
        const newPrice = priceMatch[1] + "frw";
        const newSize = this.value;

        // Update displayed price
        priceElement.textContent = newPrice;

        // Update onclick function
        addToCartButton.setAttribute(
          "onclick",
          `addToCartWithSize('${productName}', '${newPrice}', '${newSize}'); return false;`
        );
      }
    });
  });
});
