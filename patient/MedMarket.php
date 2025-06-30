<?php
// filepath: c:\Users\Ronian\OneDrive\Desktop\EMed\patient\MedMarket.php
session_start();
include_once __DIR__ . '/../database/conection_db.php';
include_once __DIR__ . '/MedMarketController.php';

// Function to get medicines with proper image paths
function getMedicinesWithImages($conn) {
    $medicines = [];
    $result = $conn->query("SELECT m.*, c.name as category FROM medicines m LEFT JOIN categories c ON m.category_id = c.id");
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['image_path'])) {
                if (strpos($row['image_path'], '/') !== 0 && strpos($row['image_path'], 'http') !== 0) {
                    $row['image_path'] = '/Images/' . $row['image_path'];
                }
            } else {
                $row['image_path'] = '/Images/default-medicine.jpg';
            }
            $medicines[] = $row;
        }
    }
    return $medicines;
}

$categories = getCategories($conn);
$medicines = getMedicinesWithImages($conn);
$buyer_name = $_SESSION['Username'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>MedMarket - eMedConnect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet"/>
    <style>
      :root {
        --primary-color: #43c97e;
        --secondary-color: #e6f5ea;
      }
      .main-content {
        padding: 20px 0 0 0;
        min-height: 100vh;
        background: linear-gradient(180deg, #f8fff8 0%, #ffffff 100%);
      }
      .top-bar-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        margin-bottom: 2rem;
        padding: 0 1.5rem;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
      }
      .search-bar-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex: 1 1 auto;
        max-width: 600px;
      }
      .search-bar-group input,
      .search-bar-group select {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        transition: all 0.3s ease;
      }
      .search-bar-group input:focus,
      .search-bar-group select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(67, 201, 126, 0.1);
      }
      .cart-icon, .notif-icon {
        position: relative;
        cursor: pointer;
        transition: transform 0.2s ease;
      }
      .cart-icon:hover, .notif-icon:hover {
        transform: scale(1.1);
      }
      .cart-icon img { height: 32px; }
      .notif-icon { font-size: 1.8rem; color: var(--primary-color); }
      .notif-toast {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 2000;
        min-width: 280px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-radius: 8px;
      }
      .medicine-card {
        transition: all 0.3s ease;
        cursor: pointer;
        min-width: 220px;
        max-width: 260px;
        width: 100%;
        margin: 0 auto 20px;
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      }
      .medicine-card:hover {
        box-shadow: 0 6px 20px rgba(67, 201, 126, 0.2);
        transform: translateY(-5px);
      }
      .medicine-img {
        height: 120px;
        object-fit: contain;
        border-radius: 12px 12px 0 0;
        background: #f8f9fa;
        padding: 15px;
      }
      .img-placeholder {
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f0f0f0;
        border-radius: 12px 12px 0 0;
      }
      .card-body {
        padding: 1.25rem !important;
      }
      .card-title {
        font-size: 1.15rem;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
      .quantity-controls {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 15px 0;
      }
      .quantity-btn {
        width: 40px;
        height: 40px;
        border: none;
        background: var(--primary-color);
        color: white;
        border-radius: 8px;
        font-size: 1.2rem;
        transition: background 0.2s ease;
      }
      .quantity-btn:hover {
        background: #3ab46e;
      }
      .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
      }
      .modal-header {
        border-bottom: none;
        padding: 1.5rem 2rem;
        background: linear-gradient(45deg, var(--primary-color), #54d98c);
        color: white;
      }
      .modal-title {
        font-weight: 600;
      }
      .modal-body {
        padding: 2rem;
      }
      .modal-footer {
        border-top: none;
        padding: 1.5rem 2rem;
      }
      #trackingMap {
        height: 250px;
        border-radius: 12px;
        margin-top: 15px;
        border: 1px solid #dee2e6;
        cursor: pointer;
      }
      .address-confirmation {
        margin-top: 15px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
      }
    </style>
</head>
<body>
<div class="container-fluid">
  <div class="row flex-nowrap">
    <div class="col-auto d-none d-lg-block p-0">
      <?php include_once __DIR__ . '/../SideBar/Sidebar.php'; ?>
    </div>

    <div class="col main-content">
      <div class="top-bar-row">
        <div class="search-bar-group">
          <input id="searchBar" type="text" class="form-control" placeholder="Search medicines..."/>
          <select id="categoryFilter" class="form-select w-auto">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-success" onclick="filterMedicines()">Filter</button>
        </div>
        <div>
          <span class="cart-icon" onclick="showCart()" title="View Cart">
            <img src="/Images/shopping-cart.png" alt="Cart" />
            <span class="badge bg-danger" id="cartCount" style="position: absolute; top: -8px; right: -8px">0</span>
          </span>
          <span class="notif-icon" id="notifBell" title="Notifications">
            <i class="bi bi-bell-fill"></i>
          </span>
        </div>
      </div>
      <div id="notifToast" class="notif-toast"></div>

      <div class="container py-4">
        <div class="row g-4 justify-content-center" id="medicineGrid" style="max-width:1400px;margin:0 auto;">
          <?php foreach ($medicines as $med): 
              $imageName = strtolower(str_replace(' ', '-', $med['name'])) . '.jpg';
              $imagePath = '/Images/' . $imageName;
              $defaultImage = '/Images/default-medicine.jpg';
          ?>
              <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex">
                  <div class="card medicine-card h-100 shadow-sm border-0 flex-fill"
                       data-med='<?= htmlspecialchars(json_encode($med), ENT_QUOTES, 'UTF-8') ?>'
                       onclick="showMedicineModal(this)">
                      <img src="<?= $imagePath ?>" 
                           class="card-img-top medicine-img mt-2" 
                           alt="<?= htmlspecialchars($med['name']) ?>"
                           onerror="this.onerror=null;this.src='<?= $defaultImage ?>';" />
                      <div class="card-body p-2 text-center">
                          <h6 class="card-title mb-1"><?= htmlspecialchars($med['name']) ?></h6>
                          <div class="mb-1"><span class="badge bg-info"><?= htmlspecialchars($med['category']) ?></span></div>
                          <div class="mb-1" style="font-size:0.9em;">Grams: <?= htmlspecialchars($med['grams'] ?? '-') ?></div>
                          <div class="fw-bold text-success" style="font-size:1.1em;">₱<?= number_format($med['price'], 2) ?></div>
                      </div>
                  </div>
              </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php include_once __DIR__ . '/../SideBar_Right/SidebarRight.php'; ?>
    </div>
  </div>

  <!-- Medicine Modal -->
  <div class="modal fade" id="medicineModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form onsubmit="return handleBuy(event)">
          <div class="modal-header">
            <h5 class="modal-title">Medicine Details</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-5">
                <img id="modalImg" src="" class="img-fluid mb-3 rounded-3" onerror="this.onerror=null;this.src='/Images/default-medicine.jpg';" />
              </div>
              <div class="col-md-7">
                <h5 id="modalName" class="mb-2"></h5>
                <div><span class="badge bg-info" id="modalCategory"></span></div>
                <div id="modalGrams" class="mb-2"></div>
                <div class="fw-bold text-success mb-3" id="modalPrice"></div>
                <div class="mb-3">
                  <label for="buyerName" class="form-label">Your Name</label>
                  <input type="text" class="form-control" id="buyerName" required value="<?= htmlspecialchars($buyer_name) ?>" />
                </div>
                <div class="mb-3">
                  <label for="quantity" class="form-label">Quantity</label>
                  <div class="quantity-controls">
                    <button type="button" class="quantity-btn" onclick="updateQuantity(-1)">-</button>
                    <input type="number" class="form-control text-center" id="quantity" value="1" min="1" style="width: 70px;" readonly>
                    <button type="button" class="quantity-btn" onclick="updateQuantity(1)">+</button>
                  </div>
                </div>
                <div class="mb-3">
                  <label for="deliveryLocation" class="form-label">Delivery Location</label>
                  <div class="input-group mb-2">
                    <input type="text" class="form-control" id="deliveryLocation" readonly required />
                    <button type="button" class="btn btn-outline-primary" onclick="getLocation()">Use My Location</button>
                  </div>
                  <div class="address-confirmation" id="addressConfirmation" style="display: none;">
                    <p class="mb-2"><strong>Selected Address:</strong> <span id="selectedAddress"></span></p>
                    <button type="button" class="btn btn-sm btn-success" onclick="confirmAddress()">Confirm Address</button>
                  </div>
                  <div id="trackingMap"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" onclick="addToCart()">Add to Cart</button>
            <button type="submit" class="btn btn-success" id="buyButton" disabled>Buy Now</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Cart Modal -->
  <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="cartModalLabel">Your Cart</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="cartBody">Your cart is empty.</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let selectedMedicine = null;
let cart = [];
let map = null;
let marker = null;
let notifTimeout = null;
let selectedLatLng = null;
let confirmedAddress = false;

function showMedicineModal(cardElement) {
  const med = JSON.parse(cardElement.getAttribute("data-med"));
  selectedMedicine = med;
  document.getElementById("modalImg").src = med.image_path;
  document.getElementById("modalName").textContent = med.name;
  document.getElementById("modalCategory").textContent = med.category;
  document.getElementById("modalGrams").textContent = "Grams: " + (med.grams || "-");
  document.getElementById("modalPrice").textContent = "₱" + Number(med.price).toFixed(2);
  document.getElementById("deliveryLocation").value = "";
  document.getElementById("buyerName").value = "<?= htmlspecialchars($buyer_name) ?>";
  document.getElementById("quantity").value = 1;
  document.getElementById("trackingMap").innerHTML = "";
  document.getElementById("addressConfirmation").style.display = "none";
  document.getElementById("buyButton").disabled = true;
  confirmedAddress = false;
  selectedLatLng = null;
  initMap();
  new bootstrap.Modal(document.getElementById("medicineModal")).show();
}

function initMap() {
  map = L.map('trackingMap').setView([14.5995, 120.9842], 13); // Default: Manila
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  map.on('click', function(e) {
    selectedLatLng = e.latlng;
    if (marker) marker.remove();
    marker = L.marker([selectedLatLng.lat, selectedLatLng.lng]).addTo(map)
      .bindPopup('Selected Location').openPopup();
    map.panTo([selectedLatLng.lat, selectedLatLng.lng]);
    document.getElementById("deliveryLocation").value = `Lat: ${selectedLatLng.lat.toFixed(5)}, Lng: ${selectedLatLng.lng.toFixed(5)}`;
    getAddressFromLatLng(selectedLatLng.lat, selectedLatLng.lng);
  });
}

function getLocation() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        selectedLatLng = { lat: pos.coords.latitude, lng: pos.coords.longitude };
        if (!map) {
          initMap();
        } else {
          map.setView([selectedLatLng.lat, selectedLatLng.lng], 15);
        }
        if (marker) marker.remove();
        marker = L.marker([selectedLatLng.lat, selectedLatLng.lng]).addTo(map)
          .bindPopup('Your Location').openPopup();
        document.getElementById("deliveryLocation").value = `Lat: ${selectedLatLng.lat.toFixed(5)}, Lng: ${selectedLatLng.lng.toFixed(5)}`;
        getAddressFromLatLng(selectedLatLng.lat, selectedLatLng.lng);
      },
      () => showNotification("Unable to fetch location", "danger")
    );
  } else {
    showNotification("Geolocation not supported by your browser", "danger");
  }
}

function getAddressFromLatLng(lat, lng) {
  fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
    .then(response => response.json())
    .then(data => {
      const selectedAddress = data.display_name || "Address not found";
      document.getElementById("selectedAddress").textContent = selectedAddress;
      document.getElementById("addressConfirmation").style.display = "block";
      document.getElementById("buyButton").disabled = false; // Enable after confirmation
    })
    .catch(error => {
      console.error("Reverse geocoding error:", error);
      document.getElementById("selectedAddress").textContent = "Unable to fetch address";
      document.getElementById("addressConfirmation").style.display = "block";
      document.getElementById("buyButton").disabled = true;
    });
}


function confirmAddress() {
  confirmedAddress = true;
  document.getElementById("buyButton").disabled = false;
  showNotification("Address confirmed!", "success");
}

function updateQuantity(change) {
  let quantity = parseInt(document.getElementById("quantity").value);
  quantity += change;
  if (quantity < 1) quantity = 1;
  document.getElementById("quantity").value = quantity;
  updatePrice();
}

function updatePrice() {
  const quantity = parseInt(document.getElementById("quantity").value);
  const price = parseFloat(selectedMedicine.price);
  document.getElementById("modalPrice").textContent = "₱" + (price * quantity).toFixed(2);
}

function addToCart() {
  if (!selectedMedicine) return;
  const quantity = parseInt(document.getElementById("quantity").value);
  cart.push({ ...selectedMedicine, quantity });
  updateCartCount();
  showNotification("Added to cart!", "success");
}

function updateCartCount() {
  document.getElementById("cartCount").textContent = cart.length;
}

function showCart() {
  const cartBody = document.getElementById("cartBody");
  if (cart.length === 0) {
    cartBody.innerHTML = '<div class="text-center text-muted">Your cart is empty.</div>';
  } else {
    cartBody.innerHTML = "";
    cart.forEach((med, i) => {
      const div = document.createElement("div");
      div.className = "border-bottom py-3 d-flex justify-content-between align-items-center";
      div.innerHTML = `
        <div class="d-flex align-items-center gap-2">
          ${med.image_path ? `<img src="${med.image_path}" style="height: 50px; width: auto; border-radius: 8px;" onerror="this.style.display='none'">` : ''}
          <div>
            <strong>${med.name} x${med.quantity}</strong>
            <div class="text-success">₱${(med.price * med.quantity).toFixed(2)}</div>
          </div>
        </div>
        <div>
          <button class="btn btn-sm btn-success me-1" onclick="buyFromCart(${i});event.stopPropagation();">Buy Now</button>
          <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${i});event.stopPropagation();">Remove</button>
        </div>
      `;
      cartBody.appendChild(div);
    });
  }
  new bootstrap.Modal(document.getElementById("cartModal")).show();
}

function buyFromCart(index) {
  const med = cart[index];
  if (!med.lemonsqueezy_checkout_id) {
    showNotification("This item is not configured for purchase.", "info");
    return;
  }
  window.open(med.lemonsqueezy_checkout_id, "_blank");
  showNotification("Redirected to Lemon Squeezy.", "info");
}

function removeFromCart(index) {
  cart.splice(index, 1);
  updateCartCount();
  showCart();
  showNotification("Item removed from cart", "info");
}

function handleBuy(event) {
  event.preventDefault();
  if (!selectedMedicine) return false;
  const buyerName = document.getElementById("buyerName").value.trim();
  const location = document.getElementById("deliveryLocation").value.trim();
  const quantity = parseInt(document.getElementById("quantity").value);
  if (!buyerName || !location || !confirmedAddress) {
    showNotification("Please fill buyer name, select a location, and confirm the address.", "danger");
    return false;
  }
  if (!selectedMedicine.lemonsqueezy_checkout_id) {
    showNotification("This item is not configured for purchase.", "info");
    return false;
  }
  window.open(selectedMedicine.lemonsqueezy_checkout_id, "_blank");
  showNotification("Redirected to Lemon Squeezy.", "info");
  bootstrap.Modal.getInstance(document.getElementById("medicineModal")).hide();
  return false;
}

function showNotification(msg, type = "info") {
  const notifToast = document.getElementById("notifToast");
  notifToast.innerHTML = `
    <div class="toast align-items-center text-bg-${type} border-0 show" role="alert">
      <div class="d-flex">
        <div class="toast-body">${msg}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  `;
  const toast = new bootstrap.Toast(notifToast.querySelector('.toast'));
  toast.show();
  clearTimeout(notifTimeout);
  notifTimeout = setTimeout(() => toast.hide(), 5000);
}

function filterMedicines() {
  const searchText = document.getElementById("searchBar").value.toLowerCase();
  const category = document.getElementById("categoryFilter").value.toLowerCase();
  const cards = document.querySelectorAll("#medicineGrid .medicine-card");

  cards.forEach(card => {
    const med = JSON.parse(card.getAttribute("data-med"));
    const matchSearch = med.name.toLowerCase().includes(searchText);
    const matchCategory = category === "" || med.category.toLowerCase() === category;
    card.parentElement.style.display = (matchSearch && matchCategory) ? "block" : "none";
  });
}

function trackDelivery(lat, lng) {
  fetch(`https://api.example.com/delivery/track?lat=${lat}&lng=${lng}`)
    .then(response => response.json())
    .then(data => {
      if (data.status === "in_transit") {
        const deliveryLatLng = { lat: data.latitude, lng: data.longitude };
        if (map) {
          if (marker) marker.remove();
          marker = L.marker([deliveryLatLng.lat, deliveryLatLng.lng]).addTo(map)
            .bindPopup('Delivery Location').openPopup();
          map.panTo([deliveryLatLng.lat, deliveryLatLng.lng]);
          showNotification(`Delivery in transit to: Lat ${deliveryLatLng.lat.toFixed(5)}, Lng ${deliveryLatLng.lng.toFixed(5)}`, "info");
        }
      }
    })
    .catch(error => {
      console.error("Tracking error:", error);
      showNotification("Unable to track delivery", "danger");
    });
}

document.getElementById("searchBar").addEventListener("input", filterMedicines);
document.getElementById("categoryFilter").addEventListener("change", filterMedicines);
</script>
</body>
</html>