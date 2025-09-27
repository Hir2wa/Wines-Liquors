// User authentication using real API

function switchTab(tabName) {
  // Update tab buttons
  document.querySelectorAll(".auth-tab").forEach((tab) => {
    tab.classList.remove("active");
  });
  event.target.classList.add("active");

  // Update forms
  document.querySelectorAll(".auth-form").forEach((form) => {
    form.classList.remove("active");
  });
  document.getElementById(tabName + "Form").classList.add("active");
}

function togglePassword(inputId) {
  const input = document.getElementById(inputId);
  const icon = input.parentElement.querySelector(".password-toggle");

  if (input.type === "password") {
    input.type = "text";
    icon.classList.replace("fa-eye", "fa-eye-slash");
  } else {
    input.type = "password";
    icon.classList.replace("fa-eye-slash", "fa-eye");
  }
}

async function handleLogin(event) {
  console.log("handleLogin called");
  event.preventDefault();

  const emailElement = document.getElementById("email");
  const passwordElement = document.getElementById("password");

  if (!emailElement || !passwordElement) {
    console.log("Email or password element not found");
    return;
  }

  const email = emailElement.value;
  const password = passwordElement.value;

  console.log("Email:", email);
  console.log("Password:", password ? "***" : "empty");

  // Reset error messages
  document
    .querySelectorAll(".error-message")
    .forEach((el) => (el.style.display = "none"));

  // Basic validation
  if (!email || !password) {
    if (!email) {
      showError("emailError", "Email is required");
    }
    if (!password) {
      showError("passwordError", "Password is required");
    }
    return;
  }

  // Show loading state
  const button = event.target;
  button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
  button.disabled = true;

  try {
    const response = await fetch("/api/auth/login", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        emailOrPhone: email,
        password: password,
        deviceInfo: navigator.userAgent,
      }),
    });

    console.log("Login API response status:", response.status);

    const result = await response.json();
    console.log("Login API response:", result);

    if (response.ok) {
      // Store user data and session
      const userData = result.data.user;
      const sessionToken = result.data.session_token;

      console.log("Login successful!");
      console.log("User data:", userData);
      console.log("Session token:", sessionToken ? "Present" : "Missing");

      const profileData = {
        id: userData.id,
        firstName: userData.first_name,
        lastName: userData.last_name,
        email: userData.email,
        phone: userData.phone,
        isVerified: userData.is_verified,
        emailVerified: userData.email_verified,
        phoneVerified: userData.phone_verified,
        isAdmin: userData.is_admin || false,
      };

      localStorage.setItem("userData", JSON.stringify(profileData));
      localStorage.setItem("sessionToken", sessionToken);
      localStorage.setItem("user_logged_in", "true");

      console.log("Data saved to localStorage:");
      console.log("userData:", localStorage.getItem("userData"));
      console.log(
        "sessionToken:",
        localStorage.getItem("sessionToken") ? "Saved" : "Not saved"
      );
      console.log("Is Admin:", userData.is_admin);

      // Check if user is admin and show mode selection
      if (userData.is_admin) {
        showAdminModeSelection();
      } else {
        showFlashMessage(
          "Login successful! Welcome back to Total Wine & More.",
          "success"
        );

        // Redirect after a short delay
        setTimeout(() => {
          window.location.href = "index.html";
        }, 1500);
      }
    } else {
      showError("emailError", result.message || "Invalid email or password");
      button.innerHTML = "Log in";
      button.disabled = false;
    }
  } catch (error) {
    showError(
      "emailError",
      "Network error. Please check your connection and try again."
    );
    button.innerHTML = "Log in";
    button.disabled = false;
  }
}

function handleRegister(event) {
  // Redirect to the dedicated registration page
  window.location.href = "Register.html";
}

function showError(elementId, message) {
  const element = document.getElementById(elementId);
  if (element) {
    element.textContent = message;
    element.style.display = "block";
  }
}

function showFlashMessage(message, type = "success") {
  // Create flash message container
  const flashDiv = document.createElement("div");
  flashDiv.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: ${type === "success" ? "#28a745" : "#dc3545"};
    color: white;
    padding: 16px 24px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 10000;
    font-weight: 500;
    font-size: 14px;
    max-width: 350px;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    display: flex;
    align-items: center;
    gap: 12px;
  `;

  // Add icon
  const icon = document.createElement("i");
  icon.className =
    type === "success" ? "fas fa-check-circle" : "fas fa-exclamation-circle";
  icon.style.fontSize = "18px";

  // Add message text
  const messageText = document.createElement("span");
  messageText.textContent = message;

  flashDiv.appendChild(icon);
  flashDiv.appendChild(messageText);

  // Add to page
  document.body.appendChild(flashDiv);

  // Animate in
  setTimeout(() => {
    flashDiv.style.transform = "translateX(0)";
  }, 100);

  // Auto remove after 3 seconds
  setTimeout(() => {
    flashDiv.style.transform = "translateX(100%)";
    setTimeout(() => {
      if (flashDiv.parentNode) {
        flashDiv.parentNode.removeChild(flashDiv);
      }
    }, 300);
  }, 3000);
}

function showAdminModeSelection() {
  // Create admin mode selection modal
  const modalDiv = document.createElement("div");
  modalDiv.style.cssText = `
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10000;
  `;

  const modalContent = document.createElement("div");
  modalContent.style.cssText = `
    background: white;
    padding: 40px;
    border-radius: 15px;
    text-align: center;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  `;

  modalContent.innerHTML = `
    <div style="margin-bottom: 30px;">
      <i class="fas fa-user-shield" style="font-size: 3rem; color: #dc3545; margin-bottom: 15px;"></i>
      <h2 style="color: #333; margin-bottom: 10px;">Welcome, Admin!</h2>
      <p style="color: #666; font-size: 16px;">You have admin privileges. How would you like to continue?</p>
    </div>
    
    <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
      <button id="adminModeBtn" style="
        background: #dc3545;
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
      ">
        <i class="fas fa-cog"></i>
        Continue as Admin
      </button>
      
      <button id="userModeBtn" style="
        background: #28a745;
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
      ">
        <i class="fas fa-user"></i>
        Continue as User
      </button>
    </div>
    
    <p style="color: #999; font-size: 12px; margin-top: 20px;">
      You can switch between modes anytime from your profile
    </p>
  `;

  modalDiv.appendChild(modalContent);
  document.body.appendChild(modalDiv);

  // Add hover effects
  const adminBtn = modalContent.querySelector("#adminModeBtn");
  const userBtn = modalContent.querySelector("#userModeBtn");

  adminBtn.addEventListener("mouseenter", () => {
    adminBtn.style.background = "#c82333";
    adminBtn.style.transform = "translateY(-2px)";
  });
  adminBtn.addEventListener("mouseleave", () => {
    adminBtn.style.background = "#dc3545";
    adminBtn.style.transform = "translateY(0)";
  });

  userBtn.addEventListener("mouseenter", () => {
    userBtn.style.background = "#218838";
    userBtn.style.transform = "translateY(-2px)";
  });
  userBtn.addEventListener("mouseleave", () => {
    userBtn.style.background = "#28a745";
    userBtn.style.transform = "translateY(0)";
  });

  // Handle admin mode selection
  adminBtn.addEventListener("click", () => {
    localStorage.setItem("admin_mode", "true");
    showFlashMessage("Welcome to Admin Dashboard!", "success");
    setTimeout(() => {
      window.location.href = "AdminDashboard.html";
    }, 1000);
  });

  // Handle user mode selection
  userBtn.addEventListener("click", () => {
    localStorage.setItem("admin_mode", "false");
    showFlashMessage("Welcome back to Total Wine & More!", "success");
    setTimeout(() => {
      window.location.href = "index.html";
    }, 1000);
  });
}

function showForgotPassword() {
  showFlashMessage(
    "Password reset functionality is not yet implemented.",
    "error"
  );
}

function socialLogin(provider) {
  showFlashMessage(`${provider} login is not yet implemented.`, "error");
}

// Check if user is already logged in
function checkLoginStatus() {
  const isLoggedIn = localStorage.getItem("user_logged_in") === "true";
  const userData = localStorage.getItem("userData");

  if (isLoggedIn && userData) {
    const user = JSON.parse(userData);
    showFlashMessage(
      `You are already logged in as ${user.firstName} ${user.lastName}. Please log out first if you want to switch accounts.`,
      "error"
    );

    // Redirect to home page after 3 seconds
    setTimeout(() => {
      window.location.href = "index.html";
    }, 3000);

    return true;
  }
  return false;
}

// Logout function
function logout() {
  localStorage.removeItem("userData");
  localStorage.removeItem("sessionToken");
  localStorage.removeItem("user_logged_in");

  showFlashMessage("You have been logged out successfully.", "success");

  // Redirect to login page after 2 seconds
  setTimeout(() => {
    window.location.href = "login.html";
  }, 2000);
}

// Auto-format phone number (only if element exists)
document.addEventListener("DOMContentLoaded", function () {
  // Check if user is already logged in
  if (checkLoginStatus()) {
    return; // Stop execution if already logged in
  }

  const phoneInput = document.getElementById("registerPhone");
  if (phoneInput) {
    phoneInput.addEventListener("input", function (e) {
      let value = e.target.value.replace(/\D/g, "");
      if (value.startsWith("250")) {
        value = "+" + value;
      } else if (!value.startsWith("+250") && value.length >= 9) {
        value = "+250 " + value;
      }
      e.target.value = value;
    });
  }

  // Add form submit event handler (only on login page)
  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    console.log("Login form found, adding submit handler");
    loginForm.addEventListener("submit", function (e) {
      console.log("Form submitted, preventing default");
      e.preventDefault(); // Prevent default form submission
      handleLogin(e);
    });
  }
  // Note: No error message if form not found (Register page doesn't have login form)
});
