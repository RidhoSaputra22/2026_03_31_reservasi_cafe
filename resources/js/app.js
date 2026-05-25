import Alpine from 'alpinejs';

import './bootstrap';

const CART_KEY = 'AMIKOSPACE_cart';
const RESERVATION_KEY = 'AMIKOSPACE_last_reservation';

function rupiah(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount);
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function getCart() {
    try {
        return JSON.parse(localStorage.getItem(CART_KEY)) || [];
    } catch (error) {
        return [];
    }
}

function cartItemCount(cart = getCart()) {
    return cart.reduce((sum, item) => sum + Number(item.qty || 0), 0);
}

function updateCartCount(cart = getCart()) {
    const count = cartItemCount(cart);
    const cartStore = window.Alpine?.store?.('cart');

    if (cartStore) {
        cartStore.count = count;
    }

    document.querySelectorAll('[data-cart-count]').forEach((node) => {
        node.textContent = count;
    });
}

function saveCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    updateCartCount(cart);
}

function cartTotal(cart = getCart()) {
    return cart.reduce((sum, item) => sum + Number(item.price || 0) * Number(item.qty || 0), 0);
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className =
        'fixed bottom-6 left-1/2 z-[80] -translate-x-1/2 rounded-full bg-coffee-900 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-coffee-900/20';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 1800);
}

function addToCart(item) {
    const cart = getCart();
    const existing = cart.find((cartItem) => cartItem.id === item.id);

    if (existing) {
        existing.qty += 1;
    } else {
        cart.push({ ...item, qty: 1 });
    }

    saveCart(cart);
    showToast(`${item.name} ditambahkan ke cart`);
}

function changeCartQty(id, delta) {
    const cart = getCart()
        .map((item) => (item.id === id ? { ...item, qty: Number(item.qty) + delta } : item))
        .filter((item) => item.qty > 0);

    saveCart(cart);
    renderCart();
}

function removeCartItem(id) {
    saveCart(getCart().filter((item) => item.id !== id));
    renderCart();
}

function clearCart() {
    saveCart([]);
    renderCart();
}

function renderCart() {
    const wrapper = document.querySelector('#cartItems');
    const totalNode = document.querySelector('#cartTotal');

    if (!wrapper || !totalNode) {
        return;
    }

    const cart = getCart();
    totalNode.textContent = rupiah(cartTotal(cart));

    if (!cart.length) {
        wrapper.innerHTML = `
      <div class="rounded-3xl border border-dashed border-coffee-200 bg-coffee-50 p-8 text-center">
        <p class="text-xl font-black text-black">Cart masih kosong</p>
        <p class="mt-2 text-sm leading-6 text-coffee-600">Kamu bisa reservasi tanpa pre-order, atau tambahkan menu terlebih dahulu.</p>
      </div>
    `;
        return;
    }

    wrapper.innerHTML = cart
        .map(
            (item) => `
    <article class="grid gap-4 rounded-3xl border border-black p-5 sm:grid-cols-[1fr_auto] sm:items-center">
      <div>
        <p class="text-xs font-black uppercase tracking-[0.2em] text-coffee-400">${escapeHtml(item.category)}</p>
        <h3 class="mt-1 text-lg font-black text-coffee-900">${escapeHtml(item.name)}</h3>
        <p class="mt-1 text-sm text-coffee-600">${rupiah(Number(item.price || 0))} / item</p>
      </div>
      <div class="flex items-center justify-between gap-3 sm:justify-end">
        <div class="flex items-center gap-2 rounded-full bg-coffee-50 p-1">
          <button type="button" class="h-9 w-9 rounded-full bg-white font-black text-black" onclick="changeCartQty('${escapeHtml(item.id)}', -1)">-</button>
          <span class="min-w-8 text-center font-black text-coffee-900">${Number(item.qty || 0)}</span>
          <button type="button" class="h-9 w-9 rounded-full bg-white font-black text-black" onclick="changeCartQty('${escapeHtml(item.id)}', 1)">+</button>
        </div>
        <p class="w-28 text-right font-black text-coffee-900">${rupiah(Number(item.price || 0) * Number(item.qty || 0))}</p>
        <button type="button" class="rounded-full border border-coffee-200 px-4 py-2 text-sm font-black text-coffee-700 hover:bg-coffee-50" onclick="removeCartItem('${escapeHtml(item.id)}')">Hapus</button>
      </div>
    </article>
  `,
        )
        .join('');
}

function setReservationStep(step) {
    document.querySelectorAll('.reservation-panel').forEach((panel) => {
        panel.classList.toggle('hidden', panel.dataset.panel !== step);
    });

    document.querySelectorAll('.reservation-step').forEach((button) => {
        const active = button.dataset.step === step;
        button.classList.toggle('active', active);
        button.classList.toggle('bg-black', active);
        button.classList.toggle('text-white', active);
        button.classList.toggle('text-coffee-600', !active);
    });

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function buildReservation(form) {
    const data = new FormData(form);

    return {
        code: `INT-${Date.now().toString(36).toUpperCase().slice(-6)}`,
        guestName: data.get('guestName'),
        phone: data.get('phone'),
        guests: data.get('guests'),
        seat: data.get('seat'),
        date: data.get('date'),
        time: data.get('time'),
        note: data.get('note'),
        items: getCart(),
        total: cartTotal(),
        createdAt: new Date().toISOString(),
    };
}

function renderReservationSummary(reservation) {
    const codeNode = document.querySelector('#reservationCode');
    const summaryNode = document.querySelector('#reservationSummary');

    if (!codeNode || !summaryNode || !reservation) {
        return;
    }

    codeNode.textContent = reservation.code;

    const items = reservation.items?.length
        ? reservation.items
              .map(
                  (item) =>
                      `<li>${escapeHtml(item.name)} x ${Number(item.qty)} - ${rupiah(Number(item.price) * Number(item.qty))}</li>`,
              )
              .join('')
        : '<li>Tidak ada pre-order menu.</li>';

    summaryNode.innerHTML = `
    <div class="grid gap-4 md:grid-cols-2">
      <article class="rounded-3xl border border-black p-5">
        <p class="text-sm font-black uppercase tracking-[0.18em] text-coffee-400">Detail Tamu</p>
        <dl class="mt-4 grid gap-3 text-sm text-coffee-700">
          <div class="flex justify-between gap-4"><dt>Nama</dt><dd class="font-black text-coffee-900">${escapeHtml(reservation.guestName)}</dd></div>
          <div class="flex justify-between gap-4"><dt>WhatsApp</dt><dd class="font-black text-coffee-900">${escapeHtml(reservation.phone)}</dd></div>
          <div class="flex justify-between gap-4"><dt>Jumlah</dt><dd class="font-black text-coffee-900">${escapeHtml(reservation.guests)} orang</dd></div>
          <div class="flex justify-between gap-4"><dt>Area</dt><dd class="font-black text-coffee-900">${escapeHtml(reservation.seat)}</dd></div>
        </dl>
      </article>
      <article class="rounded-3xl border border-black p-5">
        <p class="text-sm font-black uppercase tracking-[0.18em] text-coffee-400">Jadwal</p>
        <dl class="mt-4 grid gap-3 text-sm text-coffee-700">
          <div class="flex justify-between gap-4"><dt>Tanggal</dt><dd class="font-black text-coffee-900">${escapeHtml(reservation.date)}</dd></div>
          <div class="flex justify-between gap-4"><dt>Jam</dt><dd class="font-black text-coffee-900">${escapeHtml(reservation.time)}</dd></div>
          <div><dt>Catatan</dt><dd class="mt-1 font-semibold text-coffee-900">${escapeHtml(reservation.note || '-')}</dd></div>
        </dl>
      </article>
    </div>
    <article class="rounded-3xl border border-black p-5">
      <div class="flex items-center justify-between gap-4">
        <p class="text-sm font-black uppercase tracking-[0.18em] text-coffee-400">Pre-order</p>
        <p class="font-black text-coffee-900">${rupiah(Number(reservation.total || 0))}</p>
      </div>
      <ul class="mt-4 list-disc space-y-2 pl-5 text-sm leading-6 text-coffee-700">${items}</ul>
    </article>
  `;
}

function formatDateLabel(value) {
    if (!value) {
        return '-';
    }

    const date = new Date(`${value}T00:00:00`);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date);
}

function localReservationReminder(reservation) {
    if (!reservation?.date || !reservation?.time) {
        return 'Jadwal belum lengkap';
    }

    const scheduledAt = new Date(`${reservation.date}T${reservation.time}`);

    if (Number.isNaN(scheduledAt.getTime())) {
        return 'Jadwal belum lengkap';
    }

    const diffMs = scheduledAt.getTime() - Date.now();

    if (diffMs < 0) {
        return 'Jadwal reservasi sudah lewat';
    }

    const diffHours = Math.ceil(diffMs / (1000 * 60 * 60));

    if (diffHours <= 24) {
        return `Datang dalam ${diffHours} jam lagi`;
    }

    const diffDays = Math.ceil(diffHours / 24);

    return `Datang dalam ${diffDays} hari lagi`;
}

function renderProfileLocalReservation() {
    const wrapper = document.querySelector('[data-profile-local-reservation]');

    if (!wrapper) {
        return;
    }

    let reservation = null;

    try {
        reservation = JSON.parse(localStorage.getItem(RESERVATION_KEY) || 'null');
    } catch (error) {
        reservation = null;
    }

    if (!reservation) {
        return;
    }

    const itemCount = reservation.items?.reduce((sum, item) => sum + Number(item.qty || 0), 0) || 0;
    const items = reservation.items?.length
        ? reservation.items
              .map(
                  (item) =>
                      `<li>${escapeHtml(item.name)} x ${Number(item.qty || 0)} - ${rupiah(Number(item.price || 0) * Number(item.qty || 0))}</li>`,
              )
              .join('')
        : '<li>Tidak ada pre-order menu.</li>';

    wrapper.innerHTML = `
    <div class="panel-surface p-6 sm:p-8">
      <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div>
          <p class="text-sm font-black uppercase tracking-[0.2em] text-coffee-400">Reservasi Browser</p>
          <h2 class="mt-2 text-2xl font-black text-black">Reservasi terakhir di perangkat ini</h2>
        </div>
        <span class="w-fit rounded-full bg-coffee-900 px-4 py-2 text-xs font-black uppercase tracking-[0.14em] text-white">${escapeHtml(reservation.code)}</span>
      </div>

      <div class="mt-6 grid gap-5 lg:grid-cols-[1fr_1fr]">
        <article class="rounded-[1.5rem] bg-coffee-50 p-5">
          <p class="text-sm font-black text-black">${escapeHtml(localReservationReminder(reservation))}</p>
          <dl class="mt-4 grid gap-3 text-sm text-coffee-700 sm:grid-cols-2">
            <div><dt>Tanggal</dt><dd class="mt-1 font-black text-coffee-900">${escapeHtml(formatDateLabel(reservation.date))}</dd></div>
            <div><dt>Jam</dt><dd class="mt-1 font-black text-coffee-900">${escapeHtml(reservation.time || '-')}</dd></div>
            <div><dt>Nama</dt><dd class="mt-1 font-black text-coffee-900">${escapeHtml(reservation.guestName || '-')}</dd></div>
            <div><dt>Jumlah</dt><dd class="mt-1 font-black text-coffee-900">${escapeHtml(reservation.guests || '-')} orang</dd></div>
          </dl>
        </article>

        <article class="rounded-[1.5rem] border border-coffee-100 p-5">
          <div class="flex items-center justify-between gap-4">
            <p class="text-sm font-black text-black">Pre-order</p>
            <p class="font-black text-coffee-900">${itemCount} item / ${rupiah(Number(reservation.total || 0))}</p>
          </div>
          <ul class="mt-4 list-disc space-y-2 pl-5 text-sm leading-6 text-coffee-700">${items}</ul>
        </article>
      </div>
    </div>
  `;
    wrapper.classList.remove('hidden');
}

function initializeReservationForm() {
    const today = new Date().toISOString().split('T')[0];
    const dateInput = document.querySelector('input[name="date"]');

    if (dateInput) {
        dateInput.min = today;
        dateInput.value = dateInput.value || today;
    }

    const timeInput = document.querySelector('input[name="time"]');

    if (timeInput && !timeInput.value) {
        timeInput.value = '10:00';
    }
}

function initializeDomBindings() {
    updateCartCount();
    renderCart();
    renderProfileLocalReservation();
    initializeReservationForm();

    document.querySelector('#toGuestStep')?.addEventListener('click', () => setReservationStep('guest'));
    document.querySelector('#backToCartStep')?.addEventListener('click', () => setReservationStep('cart'));
    document.querySelector('#clearCartBtn')?.addEventListener('click', clearCart);

    document.querySelectorAll('.reservation-step').forEach((button) => {
        button.addEventListener('click', () => {
            if (button.dataset.step === 'reserved') {
                const lastReservation = JSON.parse(localStorage.getItem(RESERVATION_KEY) || 'null');

                if (!lastReservation) {
                    showToast('Selesaikan data reservasi terlebih dahulu');
                    return;
                }

                renderReservationSummary(lastReservation);
            }

            setReservationStep(button.dataset.step);
        });
    });

    document.querySelector('#reservationForm')?.addEventListener('submit', (event) => {
        event.preventDefault();

        const reservation = buildReservation(event.currentTarget);
        localStorage.setItem(RESERVATION_KEY, JSON.stringify(reservation));
        localStorage.removeItem(CART_KEY);

        updateCartCount([]);
        renderCart();
        renderReservationSummary(reservation);
        setReservationStep('reserved');
    });
}

document.addEventListener('alpine:init', () => {
    Alpine.store('cart', {
        count: cartItemCount(),
    });

    Alpine.data('navbar', () => ({
        open: false,
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
    }));

    Alpine.data('menuCatalog', (items = []) => ({
        items,
        activeFilter: 'all',
        setFilter(filter) {
            this.activeFilter = filter;
        },
        isVisible(item) {
            return this.activeFilter === 'all' || item.category === this.activeFilter;
        },
        addItem(item) {
            addToCart(item);
        },
    }));
});

window.Alpine = Alpine;
window.appCafe = { addToCart, changeCartQty, removeCartItem, clearCart, setReservationStep };
window.changeCartQty = changeCartQty;
window.removeCartItem = removeCartItem;
window.clearCart = clearCart;

Alpine.start();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeDomBindings);
} else {
    initializeDomBindings();
}
