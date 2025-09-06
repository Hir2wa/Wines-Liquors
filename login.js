// Sample user data (in real app, this would be in database)
const sampleUsers = {
  "john@example.com": {
    name: "John Doe",
    email: "john@example.com",
    phone: "+250 788 123 456",
    address: "KG 15 Ave, Kigali",
    city: "Kigali",
    district: "kigali",
  },
};

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

function handleLogin(event) {
  event.preventDefault();

  const email = document.getElementById("loginEmail").value;
  const password = document.getElementById("loginPassword").value;

  // Reset error messages
  document
    .querySelectorAll(".error-message")
    .forEach((el) => (el.style.display = "none"));

  // Basic validation
  if (!email || !password) {
    if (!email) {
      showError("loginEmailError", "Email is required");
    }
    if (!password) {
      showError("loginPasswordError", "Password is required");
    }
    return;
  }

  // Simulate login (in real app, make API call)
  const button = event.target;
  button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
  button.disabled = true;

  setTimeout(() => {
    if (sampleUsers[email]) {
      // Store user data for checkout auto-fill
      const userData = sampleUsers[email];
      Object.keys(userData).forEach((key) => {
        localStorage.setItem("user_" + key, userData[key]);
      });
      localStorage.setItem("user_logged_in", "true");

      alert(
        "Login successful! Your information will be auto-filled at checkout."
      );
      window.location.href = "checkout.html";
    } else {
      showError("loginEmailError", "Invalid email or password");
      button.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
      button.disabled = false;
    }
  }, 1500);
}

function handleRegister(event) {
  event.preventDefault();

  const name = document.getElementById("registerName").value;
  const email = document.getElementById("registerEmail").value;
  const phone = document.getElementById("registerPhone").value;
  const password = document.getElementById("registerPassword").value;
  const confirmPassword = document.getElementById("confirmPassword").value;

  // Reset error messages
  document
    .querySelectorAll(".error-message")
    .forEach((el) => (el.style.display = "none"));

  // Validation
  let isValid = true;

  if (!name) {
    showError("registerNameError", "Name is required");
    isValid = false;
  }

  if (!email) {
    showError("registerEmailError", "Email is required");
    isValid = false;
  } else if (sampleUsers[email]) {
    showError("registerEmailError", "Email already exists");
    isValid = false;
  }

  if (!phone) {
    showError("registerPhoneError", "Phone number is required");
    isValid = false;
  }

  if (!password) {
    showError("registerPasswordError", "Password is required");
    isValid = false;
  } else if (password.length < 6) {
    showError(
      "registerPasswordError",
      "Password must be at least 6 characters"
    );
    isValid = false;
  }

  if (password !== confirmPassword) {
    showError("confirmPasswordError", "Passwords do not match");
    isValid = false;
  }

  if (!isValid) return;

  // Simulate registration
  const button = event.target;
  button.innerHTML =
    '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
  button.disabled = true;

  setTimeout(() => {
    // Store new user data
    const userData = { name, email, phone };
    Object.keys(userData).forEach((key) => {
      localStorage.setItem("user_" + key, userData[key]);
    });
    localStorage.setItem("user_logged_in", "true");

    alert("Account created successfully! Welcome to WineStore.");
    window.location.href = "checkout.html";
  }, 2000);
}

function showError(elementId, message) {
  const element = document.getElementById(elementId);
  element.textContent = message;
  element.style.display = "block";
}

function showForgotPassword() {
  alert(
    "Password reset functionality would be implemented here. For demo, use: john@example.com"
  );
}

function socialLogin(provider) {
  alert(`${provider} login would be integrated here`);
}

// Auto-format phone number
document
  .getElementById("registerPhone")
  .addEventListener("input", function (e) {
    let value = e.target.value.replace(/\D/g, "");
    if (value.startsWith("250")) {
      value = "+" + value;
    } else if (!value.startsWith("+250") && value.length >= 9) {
      value = "+250 " + value;
    }
    e.target.value = value;
  });
