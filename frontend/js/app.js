/* ============================================================
   GYM MANAGEMENT SYSTEM — app.js
   All data now comes from PHP API → MySQL (gym_management DB)
   ============================================================ */

/* ── API Base URL ──────────────────────────────────────────
   Place your entire project folder inside:
       C:/xampp/htdocs/Gym-management-System/
   Then the API will be at:
       http://localhost/Gym-management-System/frontend/api/
   ─────────────────────────────────────────────────────────── */
const API_BASE = 'http://localhost:8080/Gym-management-System/frontend/api';

/* ── Generic API caller ─────────────────────────────────── */
async function api(method, endpoint, body = null) {
    const options = { method, headers: { 'Content-Type': 'application/json' } };
    if (body) options.body = JSON.stringify(body);
    try {
        const res  = await fetch(`${API_BASE}/${endpoint}.php`, options);
        const data = await res.json();
        return data;
    } catch (err) {
        console.error(`API error [${method} ${endpoint}]:`, err);
        showToast('Cannot reach the server. Is XAMPP running?', 'error');
        return { success: false, error: err.message };
    }
}

const apiGet    = (ep)       => api('GET',    ep);
const apiPost   = (ep, body) => api('POST',   ep, body);
const apiPut    = (ep, body) => api('PUT',    ep, body);
const apiDelete = (ep, body) => api('DELETE', ep, body);

/* ── Toast Notifications ────────────────────────────────── */
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
        toast.style.opacity    = '0';
        toast.style.transform  = 'translateX(30px)';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

/* ── Modal Helpers ──────────────────────────────────────── */
function openModal(id)  { document.getElementById(id)?.classList.add('open');    }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }

document.addEventListener('click', e => {
    if (e.target.classList.contains('modal-overlay'))
        e.target.classList.remove('open');
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape')
        document.querySelectorAll('.modal-overlay.open')
            .forEach(m => m.classList.remove('open'));
});

/* ── Active Nav Highlight ───────────────────────────────── */
function highlightNav() {
    const current = window.location.pathname.split('/').pop() || 'index.html';
    document.querySelectorAll('.nav-item[href]').forEach(el => {
        const target = el.getAttribute('href').split('/').pop();
        el.classList.toggle('active', target === current);
    });
}
document.addEventListener('DOMContentLoaded', highlightNav);

/* ── Format Helpers ─────────────────────────────────────── */
const fmt = {
    currency: n => '₹' + Number(n).toLocaleString('en-IN'),
    date    : d => d ? new Date(d).toLocaleDateString('en-IN', {
        day: '2-digit', month: 'short', year: 'numeric'
    }) : '—',
};

/* ── Table Live Search ──────────────────────────────────── */
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

/* ── Loading row placeholder ────────────────────────────── */
function showLoading(tbodyId, cols = 5) {
    const el = document.getElementById(tbodyId);
    if (!el) return;
    el.innerHTML = `
        <tr>
          <td colspan="${cols}" style="text-align:center;padding:48px;color:var(--text-muted)">
            <div style="font-size:28px;margin-bottom:10px">⏳</div>
            Loading from MySQL database…
          </td>
        </tr>`;
}