// ============================================
// Indian Railway System — Main JavaScript
// ============================================

document.addEventListener('DOMContentLoaded', function() {

    // ---- Navbar Mobile Toggle ----
    const toggle = document.querySelector('.navbar-toggle');
    const nav    = document.querySelector('.navbar-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', () => nav.classList.toggle('open'));
    }

    // ---- Flash message auto-hide ----
    const flash = document.querySelector('.flash');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = 'opacity 0.5s';
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 500);
        }, 4500);
    }

    // ---- Passenger count dynamic rows ----
    const seatCountInput = document.getElementById('seat_count');
    const passengerContainer = document.getElementById('passenger-rows');
    if (seatCountInput && passengerContainer) {
        seatCountInput.addEventListener('change', updatePassengerRows);
        updatePassengerRows();
    }

    function updatePassengerRows() {
        const count = parseInt(seatCountInput.value) || 1;
        passengerContainer.innerHTML = '';
        for (let i = 1; i <= count; i++) {
            passengerContainer.insertAdjacentHTML('beforeend', passengerRowHTML(i));
        }
        updateTotal();
    }

    function passengerRowHTML(i) {
        return `
        <div class="passenger-row">
            <div class="passenger-row-header">
                <span>🧑 Passenger ${i}</span>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="passenger_name[]" placeholder="As per ID proof" required
                           maxlength="100" pattern="[A-Za-z ]{2,100}">
                </div>
                <div class="form-group">
                    <label>Age</label>
                    <input type="number" name="passenger_age[]" placeholder="Age" required min="1" max="120">
                </div>
            </div>
            <div class="form-group">
                <label>Gender</label>
                <select name="passenger_gender[]" required>
                    <option value="">Select gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>`;
    }

    // ---- Dynamic total price update ----
    const pricePerSeat = parseFloat(document.getElementById('price_per_seat')?.value) || 0;

    function updateTotal() {
        const count = parseInt(seatCountInput?.value) || 1;
        const total = count * pricePerSeat;
        const el = document.getElementById('total-price-display');
        if (el) el.textContent = '₹' + total.toFixed(2);
        const seatCountSummary = document.getElementById('seat-count-summary');
        if (seatCountSummary) seatCountSummary.textContent = count;
    }

    if (seatCountInput) {
        seatCountInput.addEventListener('change', updateTotal);
        seatCountInput.addEventListener('input', updateTotal);
        updateTotal();
    }

    // ---- Confirm Cancel Booking ----
    document.querySelectorAll('.cancel-booking-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const pnr = this.dataset.pnr;
            if (!confirm(`Cancel booking PNR: ${pnr}?\n\nThis action cannot be undone.`)) {
                e.preventDefault();
            }
        });
    });

    // ---- Confirm Admin Actions ----
    document.querySelectorAll('.confirm-action').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const msg = this.dataset.confirm || 'Are you sure?';
            if (!confirm(msg)) e.preventDefault();
        });
    });

    // ---- Search form validation ----
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const src   = document.getElementById('source')?.value.trim();
            const dest  = document.getElementById('destination')?.value.trim();
            const date  = document.getElementById('journey_date')?.value;
            if (!src || !dest || !date) {
                e.preventDefault();
                showAlert('Please fill in all search fields.', 'error');
                return;
            }
            if (src.toLowerCase() === dest.toLowerCase()) {
                e.preventDefault();
                showAlert('Source and destination cannot be the same.', 'error');
                return;
            }
            const today = new Date().toISOString().split('T')[0];
            if (date < today) {
                e.preventDefault();
                showAlert('Please select a valid future date.', 'error');
            }
        });
    }

    // ---- Set minimum date for date inputs ----
    document.querySelectorAll('input[type="date"]').forEach(input => {
        if (!input.min) {
            const today = new Date().toISOString().split('T')[0];
            input.min = today;
        }
    });

    // ---- Alert helper ----
    function showAlert(msg, type = 'info') {
        const existing = document.querySelector('.js-alert');
        if (existing) existing.remove();
        const icons = { success: '✓', error: '✕', info: 'ℹ', warning: '⚠' };
        const div = document.createElement('div');
        div.className = `flash flash-${type} js-alert`;
        div.innerHTML = `<span class="flash-icon">${icons[type]}</span><span>${msg}</span>`;
        const form = searchForm || document.querySelector('form');
        if (form) form.insertAdjacentElement('beforebegin', div);
        setTimeout(() => {
            div.style.opacity = '0';
            div.style.transition = 'opacity 0.5s';
            setTimeout(() => div.remove(), 500);
        }, 4000);
    }

    // ---- PNR Status search ----
    const pnrInput = document.getElementById('pnr-input');
    if (pnrInput) {
        pnrInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
    }

    // ---- Print ticket ----
    const printBtn = document.getElementById('print-ticket');
    if (printBtn) {
        printBtn.addEventListener('click', () => window.print());
    }
});
