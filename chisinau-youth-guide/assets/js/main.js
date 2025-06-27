// Theme Management
function toggleTheme() {
  const html = document.documentElement
  const currentTheme = html.getAttribute("data-theme")
  const newTheme = currentTheme === "dark" ? "light" : "dark"

  html.setAttribute("data-theme", newTheme)
  localStorage.setItem("theme", newTheme)
  updateThemeIcon(newTheme)

  console.log(`Theme switched to: ${newTheme}`)
}

function updateThemeIcon(theme) {
  const themeIcon = document.getElementById("theme-icon")
  if (themeIcon) {
    themeIcon.className = theme === "dark" ? "fas fa-sun" : "fas fa-moon"
  }
}

function initTheme() {
  const savedTheme = localStorage.getItem("theme") || "light"
  const html = document.documentElement

  html.setAttribute("data-theme", savedTheme)
  updateThemeIcon(savedTheme)

  console.log(`Theme initialized: ${savedTheme}`)
}

// Mobile Navigation
function toggleMobileMenu() {
  const navbarNav = document.getElementById("navbar-nav")
  const navbarToggle = document.querySelector(".navbar-toggle")

  if (navbarNav && navbarToggle) {
    navbarNav.classList.toggle("active")
    const icon = navbarToggle.querySelector("i")
    icon.className = navbarNav.classList.contains("active") ? "fas fa-times" : "fas fa-bars"
  }
}

// Search Functionality - Database Only
let searchTimeout

function initSearch() {
  const searchInput = document.getElementById("search")
  const categorySelect = document.getElementById("category")
  const searchForm = document.getElementById("search-form")

  if (searchInput) {
    // Real-time search suggestions from database
    searchInput.addEventListener("input", function () {
      const query = this.value.trim()

      clearTimeout(searchTimeout)

      if (query.length >= 2) {
        searchTimeout = setTimeout(() => {
          performDatabaseSearch(query)
        }, 300)
      } else {
        hideLiveSearchResults()
      }
    })

    // Hide suggestions when clicking outside
    document.addEventListener("click", (e) => {
      if (!searchInput.contains(e.target)) {
        hideLiveSearchResults()
      }
    })
  }

  if (categorySelect) {
    // Auto-submit on category change
    categorySelect.addEventListener("change", () => {
      if (searchForm) {
        searchForm.submit()
      }
    })
  }
}

function performDatabaseSearch(query) {
  // Create or get search results container
  let resultsContainer = document.getElementById("live-search-results")
  if (!resultsContainer) {
    resultsContainer = document.createElement("div")
    resultsContainer.id = "live-search-results"
    resultsContainer.className = "live-search-results"
    resultsContainer.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            z-index: 1000;
            max-height: 400px;
            overflow-y: auto;
            margin-top: 0.5rem;
        `

    const searchContainer = document.getElementById("search").parentElement
    searchContainer.style.position = "relative"
    searchContainer.appendChild(resultsContainer)
  }

  // Show loading
  resultsContainer.innerHTML = `
        <div class="p-4 text-center">
            <i class="fas fa-spinner fa-spin text-primary"></i>
            <span class="ml-2">Căutare în baza de date...</span>
        </div>
    `
  resultsContainer.style.display = "block"

  // Perform database search
  fetch(`api/search.php?q=${encodeURIComponent(query)}&limit=5`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.results && data.results.length > 0) {
        displayLiveSearchResults(data.results, resultsContainer)
      } else {
        showNoLiveResults(resultsContainer, query)
      }
    })
    .catch((error) => {
      console.error("Database search error:", error)
      showSearchError(resultsContainer)
    })
}

function displayLiveSearchResults(results, container) {
  let html = '<div class="p-2">'

  results.forEach((place) => {
    html += `
            <div class="live-search-item p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                 onclick="selectSearchResult('${escapeHtml(place.name)}')">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <i class="fas fa-map-marker-alt text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="font-semibold text-gray-900 mb-1">${escapeHtml(place.name)}</h4>
                        <p class="text-sm text-gray-600 mb-1">${escapeHtml(place.description)}</p>
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <span class="badge badge-primary">${getCategoryName(place.category)}</span>
                            ${place.rating > 0 ? `<span>★ ${place.rating}</span>` : ""}
                        </div>
                    </div>
                </div>
            </div>
        `
  })

  html += "</div>"
  container.innerHTML = html
}

function showNoLiveResults(container, query) {
  container.innerHTML = `
        <div class="p-4 text-center text-gray-500">
            <i class="fas fa-search mb-2"></i>
            <p>Nu s-au găsit rezultate pentru "${escapeHtml(query)}"</p>
            <small>Încearcă un alt termen de căutare</small>
        </div>
    `
}

function showSearchError(container) {
  container.innerHTML = `
        <div class="p-4 text-center text-red-500">
            <i class="fas fa-exclamation-triangle mb-2"></i>
            <p>Eroare la căutare în baza de date</p>
            <small>Te rog să încerci din nou</small>
        </div>
    `
}

function hideLiveSearchResults() {
  const resultsContainer = document.getElementById("live-search-results")
  if (resultsContainer) {
    resultsContainer.style.display = "none"
  }
}

function selectSearchResult(placeName) {
  const searchInput = document.getElementById("search")
  const searchForm = document.getElementById("search-form")

  if (searchInput) {
    searchInput.value = placeName
  }

  hideLiveSearchResults()

  if (searchForm) {
    searchForm.submit()
  }
}

function getCategoryName(category) {
  const categoryNames = {
    park: "🌳 Parc",
    restaurant: "🍽️ Restaurant",
    cafe: "☕ Cafenea",
    museum: "🏛️ Muzeu",
    shopping: "🛍️ Shopping",
    education: "🎓 Educație",
    entertainment: "🎬 Divertisment",
    sports: "⚽ Sport",
    coworking: "💻 Coworking",
    nightlife: "🌙 Viața de noapte",
    health: "🏥 Sănătate",
    transport: "🚌 Transport",
  }

  return categoryNames[category] || category
}

// Form Enhancements
function initForms() {
  const forms = document.querySelectorAll("form")

  forms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      const submitBtn = form.querySelector('button[type="submit"]')
      if (submitBtn && !submitBtn.disabled) {
        const originalText = submitBtn.innerHTML
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Se procesează...'
        submitBtn.disabled = true

        // Re-enable after 10 seconds as fallback
        setTimeout(() => {
          submitBtn.innerHTML = originalText
          submitBtn.disabled = false
        }, 10000)
      }
    })
  })
}

// Animations
function initAnimations() {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("fade-in")
        observer.unobserve(entry.target)
      }
    })
  }, observerOptions)

  // Observe elements for animation
  document.querySelectorAll(".card, .category-header, .place-card").forEach((el) => {
    observer.observe(el)
  })
}

// Notifications
function showNotification(message, type = "info", duration = 5000) {
  const notification = document.createElement("div")
  notification.className = `alert alert-${type}`
  notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `

  const icons = {
    success: "check-circle",
    error: "exclamation-triangle",
    warning: "exclamation-triangle",
    info: "info-circle",
  }

  notification.innerHTML = `
        <i class="fas fa-${icons[type] || "info-circle"}"></i>
        ${message}
        <button onclick="this.parentElement.remove()" style="float: right; background: none; border: none; font-size: 1.2em; cursor: pointer;">&times;</button>
    `

  document.body.appendChild(notification)

  // Animate in
  setTimeout(() => {
    notification.style.transform = "translateX(0)"
  }, 100)

  // Auto remove
  setTimeout(() => {
    notification.style.transform = "translateX(100%)"
    setTimeout(() => {
      if (notification.parentElement) {
        notification.parentElement.removeChild(notification)
      }
    }, 300)
  }, duration)
}

// Utility Functions
function escapeHtml(text) {
  const div = document.createElement("div")
  div.textContent = text
  return div.innerHTML
}

// Keyboard Shortcuts
function initKeyboardShortcuts() {
  document.addEventListener("keydown", (e) => {
    // Ctrl/Cmd + K to focus search
    if ((e.ctrlKey || e.metaKey) && e.key === "k") {
      e.preventDefault()
      const searchInput = document.getElementById("search")
      if (searchInput) {
        searchInput.focus()
        searchInput.select()
      }
    }

    // Escape to clear search and hide results
    if (e.key === "Escape") {
      const searchInput = document.getElementById("search")
      if (searchInput && searchInput === document.activeElement) {
        searchInput.blur()
        hideLiveSearchResults()
      }
    }

    // Ctrl/Cmd + D to toggle dark mode
    if ((e.ctrlKey || e.metaKey) && e.key === "d") {
      e.preventDefault()
      toggleTheme()
    }
  })
}

// Admin Form Validation
function initAdminValidation() {
  const adminForm = document.getElementById("admin-form")
  if (!adminForm) return

  const requiredFields = adminForm.querySelectorAll("input[required], select[required], textarea[required]")

  requiredFields.forEach((field) => {
    field.addEventListener("blur", function () {
      validateField(this)
    })

    field.addEventListener("input", function () {
      clearFieldError(this)
    })
  })

  adminForm.addEventListener("submit", (e) => {
    let isValid = true

    requiredFields.forEach((field) => {
      if (!validateField(field)) {
        isValid = false
      }
    })

    if (!isValid) {
      e.preventDefault()
      showNotification("Te rog să completezi toate câmpurile obligatorii.", "error")
    }
  })
}

function validateField(field) {
  const value = field.value.trim()
  let isValid = true
  let errorMessage = ""

  // Required field validation
  if (field.hasAttribute("required") && !value) {
    isValid = false
    errorMessage = "Acest câmp este obligatoriu."
  }

  // URL validation - more flexible
  if ((field.type === "url" || field.name === "website_url" || field.name === "image_url") && value) {
    // Simple validation - just check if it looks like a URL
    const urlPattern = /^(https?:\/\/)?([\da-z.-]+)\.([a-z.]{2,6})([/\w .-]*)*\/?$/
    if (!urlPattern.test(value)) {
      isValid = false
      errorMessage = "Te rog să introduci un URL valid (ex: example.com sau https://example.com)."
    }
  }

  // Email validation
  if (field.type === "email" && value && !isValidEmail(value)) {
    isValid = false
    errorMessage = "Te rog să introduci o adresă de email validă."
  }

  // Number validation
  if (field.type === "number" && value) {
    const num = Number.parseFloat(value)
    const min = Number.parseFloat(field.min)
    const max = Number.parseFloat(field.max)

    if (isNaN(num)) {
      isValid = false
      errorMessage = "Te rog să introduci un număr valid."
    } else if (!isNaN(min) && num < min) {
      isValid = false
      errorMessage = `Valoarea trebuie să fie cel puțin ${min}.`
    } else if (!isNaN(max) && num > max) {
      isValid = false
      errorMessage = `Valoarea trebuie să fie maximum ${max}.`
    }
  }

  if (!isValid) {
    showFieldError(field, errorMessage)
  } else {
    clearFieldError(field)
  }

  return isValid
}

function showFieldError(field, message) {
  clearFieldError(field)

  field.style.borderColor = "var(--error, #ef4444)"

  const errorDiv = document.createElement("div")
  errorDiv.className = "field-error"
  errorDiv.style.cssText = `
        color: var(--error, #ef4444);
        font-size: 0.75rem;
        margin-top: 0.25rem;
    `
  errorDiv.textContent = message

  field.parentNode.appendChild(errorDiv)
}

function clearFieldError(field) {
  field.style.borderColor = ""

  const errorDiv = field.parentNode.querySelector(".field-error")
  if (errorDiv) {
    errorDiv.remove()
  }
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

// Initialize everything when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  console.log("🚀 Initializing Chișinău Youth Guide...")

  initTheme()
  initSearch()
  initForms()
  initAnimations()
  initKeyboardShortcuts()
  initAdminValidation()

  console.log("✅ Chișinău Youth Guide loaded successfully!")

  // NO WELCOME MESSAGE - REMOVED COMPLETELY
})

// Global functions for HTML onclick events
window.toggleTheme = toggleTheme
window.toggleMobileMenu = toggleMobileMenu
window.selectSearchResult = selectSearchResult
window.showNotification = showNotification
