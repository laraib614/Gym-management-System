/* ============================================================
   GYM MANAGEMENT SYSTEM — Shared Utilities
   app.js — navigation helpers, toast, modal, mock-db bridge
   ============================================================ */

/* ── Navigation Helper ─────────────────────────────────────── */
const PAGES = {
  dashboard:  '../index.html',
  members:    'members.html',
  trainers:   'trainers.html',
  plans:      'plans.html',
  products:   'products.html',
  purchases:  'purchases.html',
};

function navigate(page) {
  window.location.href = PAGES[page] || '#';
}

/* Mark active nav item based on current page filename */
function highlightNav() {
  const current = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-item[data-page]').forEach(el => {
    const target = PAGES[el.dataset.page]?.split('/').pop();
    el.classList.toggle('active', target === current);
  });
}
document.addEventListener('DOMContentLoaded', highlightNav);

/* ── Toast Notifications ───────────────────────────────────── */
function showToast(message, type = 'success') {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
  const toast = document.createElement('div');
  toast.className = `toast${type === 'error' ? ' error' : ''}`;
  toast.innerHTML = `<span>${icon}</span><span>${message}</span>`;
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.transition = 'opacity 0.3s, transform 0.3s';
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(30px)';
    setTimeout(() => toast.remove(), 300);
  }, 3200);
}

/* ── Modal Helpers ─────────────────────────────────────────── */
function openModal(id) {
  document.getElementById(id)?.classList.add('open');
}
function closeModal(id) {
  document.getElementById(id)?.classList.remove('open');
}
// Close modal on overlay click
document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('open');
  }
});
// Close on Escape
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open')
      .forEach(m => m.classList.remove('open'));
  }
});

/* ── Local Storage "DB" Bridge ─────────────────────────────── */
/*  In a real app these would be API calls to a PHP/Node backend
    that queries MySQL. Here we use localStorage so the UI is
    fully functional without a running server.                    */

const DB = {
  get(table) {
    try { return JSON.parse(localStorage.getItem('gms_' + table)) || []; }
    catch { return []; }
  },
  save(table, data) {
    localStorage.setItem('gms_' + table, JSON.stringify(data));
  },
  nextId(table) {
    const rows = DB.get(table);
    return rows.length ? Math.max(...rows.map(r => r.id)) + 1 : 1;
  },
  insert(table, row) {
    const rows = DB.get(table);
    row.id = DB.nextId(table);
    rows.push(row);
    DB.save(table, rows);
    return row;
  },
  update(table, id, updates) {
    const rows = DB.get(table).map(r => r.id === id ? { ...r, ...updates } : r);
    DB.save(table, rows);
  },
  delete(table, id) {
    const rows = DB.get(table).filter(r => r.id !== id);
    DB.save(table, rows);
  },
};

/* ── Seed Demo Data (runs once) ────────────────────────────── */
function seedDemoData() {
  if (localStorage.getItem('gms_seeded')) return;

  DB.save('plans', [
    { id: 1, plan_name: 'Basic',    duration_months: 1,  price: 999  },
    { id: 2, plan_name: 'Standard', duration_months: 3,  price: 2499 },
    { id: 3, plan_name: 'Premium',  duration_months: 6,  price: 4499 },
    { id: 4, plan_name: 'Annual',   duration_months: 12, price: 7999 },
  ]);

  DB.save('trainers', [
    { id: 1, trainer_name: 'Rohit Sharma',  specialization: 'Strength Training', phone: '9876543210', salary: 28000 },
    { id: 2, trainer_name: 'Priya Patel',   specialization: 'Yoga & Flexibility', phone: '9123456780', salary: 24000 },
    { id: 3, trainer_name: 'Amit Verma',    specialization: 'Cardio & HIIT',      phone: '9988776655', salary: 26000 },
    { id: 4, trainer_name: 'Sneha Joshi',   specialization: 'Zumba & Dance',      phone: '9765432109', salary: 22000 },
  ]);

  DB.save('members', [
    { id: 1, name: 'Ankit Gupta',   age: 25, gender: 'Male',   phone: '9871234560', join_date: '2024-01-10', membership_id: 2, trainer_id: 1 },
    { id: 2, name: 'Divya Mehra',   age: 28, gender: 'Female', phone: '9812345670', join_date: '2024-02-15', membership_id: 3, trainer_id: 2 },
    { id: 3, name: 'Kabir Singh',   age: 31, gender: 'Male',   phone: '9765432180', join_date: '2024-03-01', membership_id: 1, trainer_id: 3 },
    { id: 4, name: 'Riya Kapoor',   age: 23, gender: 'Female', phone: '9898765432', join_date: '2024-03-20', membership_id: 4, trainer_id: 2 },
    { id: 5, name: 'Manish Tiwari', age: 35, gender: 'Male',   phone: '9654321890', join_date: '2024-04-05', membership_id: 2, trainer_id: 4 },
  ]);

  DB.save('products', [
    { id: 1, product_name: 'Whey Protein 1kg',  category: 'Supplement', price: 1499, stock_quantity: 50 },
    { id: 2, product_name: 'BCAA 250g',          category: 'Supplement', price: 799,  stock_quantity: 35 },
    { id: 3, product_name: 'Gym Gloves',         category: 'Accessory',  price: 349,  stock_quantity: 20 },
    { id: 4, product_name: 'Pre-Workout 300g',   category: 'Supplement', price: 1199, stock_quantity: 28 },
    { id: 5, product_name: 'Shaker Bottle',      category: 'Accessory',  price: 249,  stock_quantity: 45 },
    { id: 6, product_name: 'Resistance Bands',   category: 'Equipment',  price: 599,  stock_quantity: 15 },
  ]);

  DB.save('purchases', [
    { id: 1, member_id: 1, product_id: 1, quantity: 2, total_price: 2998, purchase_date: '2024-04-01' },
    { id: 2, member_id: 2, product_id: 3, quantity: 1, total_price: 349,  purchase_date: '2024-04-03' },
    { id: 3, member_id: 1, product_id: 2, quantity: 3, total_price: 2397, purchase_date: '2024-04-07' },
    { id: 4, member_id: 3, product_id: 4, quantity: 1, total_price: 1199, purchase_date: '2024-04-10' },
    { id: 5, member_id: 4, product_id: 5, quantity: 2, total_price: 498,  purchase_date: '2024-04-12' },
    { id: 6, member_id: 5, product_id: 6, quantity: 1, total_price: 599,  purchase_date: '2024-04-15' },
  ]);

  localStorage.setItem('gms_seeded', '1');
}

seedDemoData();

/* ── Format Helpers ────────────────────────────────────────── */
const fmt = {
  currency: n => '₹' + Number(n).toLocaleString('en-IN'),
  date: d => d ? new Date(d).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) : '—',
};

/* ── Table Search Filter ───────────────────────────────────── */
function setupTableSearch(inputId, tableId) {
  const input = document.getElementById(inputId);
  const table = document.getElementById(tableId);
  if (!input || !table) return;
  input.addEventListener('input', () => {
    const q = input.value.toLowerCase();
    table.querySelectorAll('tbody tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
}