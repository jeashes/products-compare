const API = {
  categories: () => `/api/categories`,
  products: ({ categoryId, page = 1, perPage = 15 }) => {
    const params = new URLSearchParams();
    params.set('per_page', perPage);
    if (categoryId) params.set('category_id', categoryId);
    if (page) params.set('page', page);
    return `/api/products?${params.toString()}`;
  },
};

const Compare = {
  storageKey: 'compareIds',
  get(){ try { return JSON.parse(localStorage.getItem(this.storageKey)||'[]'); } catch { return []; } },
  set(ids){ localStorage.setItem(this.storageKey, JSON.stringify(ids)); updateCompareBadge(); },
  add(id){
    id = Number(id);
    const ids = this.get();
    if (ids.includes(id)) return;
    if (ids.length >= 3) { alert('Compare limit is 3 items.'); return; }
    ids.push(id); this.set(ids);
    const btn = document.querySelector(`[data-compare-add="${id}"]`);
    if (btn) { btn.disabled = true; btn.textContent = 'Added'; }
  }
};

function updateCompareBadge(){
  const el = document.getElementById('compare-count');
  if (el) el.textContent = Compare.get().length;
}

function qs(name, dflt = null){
  const v = new URLSearchParams(location.search).get(name);
  return v == null ? dflt : v;
}

async function safeGet(url){
  const r = await fetch(url, { headers: { 'Accept': 'application/json' }});
  if (!r.ok) throw new Error(`HTTP ${r.status}`);
  return r.json();
}

function ratingStars(val){
  if (val == null) return '—';
  const full = Math.round(Number(val) * 2) / 2;
  return `${full.toFixed(1)} ★`;
}

function renderCategories(categories, activeId){
  const wrap = document.getElementById('categories-list');
  if (!Array.isArray(categories)) categories = categories?.data ?? [];
  if (!wrap) return;

  wrap.innerHTML = categories.map(c=>{
    const href = `listing.html?category=${encodeURIComponent(c.id)}`;
    const active = Number(activeId) === Number(c.id);
    return `
      <div class="filter-item">
        <a class="filter-link ${active?'active':''}" href="${href}">
          ${c.name}
        </a>
      </div>
    `;
  }).join('');

  const clear = document.getElementById('clear-filter');
  if (clear) clear.style.display = activeId ? '' : 'none';

  const title = document.getElementById('listing-title');
  if (title) title.textContent = activeId
    ? `Products · ${categories.find(x=>Number(x.id)===Number(activeId))?.name ?? ''}`
    : 'All Products';
}

function renderProducts(payload){
  const grid = document.getElementById('products-grid');
  if (!grid) return;

  const data = payload?.data ?? [];
  if (!data.length){
    grid.innerHTML = '<p class="muted">No products found.</p>';
    document.getElementById('pagination').innerHTML = '';
    return;
  }

  grid.innerHTML = data.map(p => `
    <article class="card product-card">
      <div class="img-wrap"><img src="${p.image}" alt="${escapeHtml(p.name)}" loading="lazy"></div>
      <h4 title="${escapeHtml(p.name)}">${p.name}</h4>
      <div class="price">€${Number(p.price).toFixed(2)}</div>
      <div class="rating">${ratingStars(p.rating)}</div>
      <div class="actions">
        <button class="btn" data-compare-add="${p.id}">Add to compare</button>
        <a class="btn btn-ghost" href="listing.html?category=${encodeURIComponent(p.category_id)}&focus=${encodeURIComponent(p.id)}">Details</a>
      </div>
    </article>
  `).join('');

  grid.querySelectorAll('[data-compare-add]').forEach(btn=>{
    const id = Number(btn.getAttribute('data-compare-add'));
    if (Compare.get().includes(id)) { btn.disabled = true; btn.textContent = 'Added'; }
    btn.addEventListener('click', ()=> Compare.add(id));
  });

  renderPagination(payload.meta, payload.links);
}

function renderPagination(meta, links){
  const p = document.getElementById('pagination');
  if (!p || !meta) { if (p) p.innerHTML=''; return; }

  const q = new URLSearchParams(location.search);
  const prevUrl = meta.current_page > 1 ? pageUrl(meta.current_page - 1) : null;
  const nextUrl = meta.current_page < meta.last_page ? pageUrl(meta.current_page + 1) : null;

  function pageUrl(page){
    const params = new URLSearchParams(location.search);
    params.set('page', page);
    return `listing.html?${params.toString()}`;
  }

  p.innerHTML = `
    <a class="page-btn" href="${prevUrl ?? '#'}" aria-disabled="${!prevUrl}">Prev</a>
    <span class="page-info">Page ${meta.current_page} of ${meta.last_page}</span>
    <a class="page-btn" href="${nextUrl ?? '#'}" aria-disabled="${!nextUrl}">Next</a>
  `;
}

function escapeHtml(s){
  return String(s ?? '')
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}

async function boot(){
  document.getElementById('year').textContent = new Date().getFullYear();
  updateCompareBadge();

  const categoryId = qs('category');
  const page = Number(qs('page', 1)) || 1;

  try {
    const [cats, prods] = await Promise.all([
      safeGet(API.categories()),
      safeGet(API.products({ categoryId, page, perPage: 15 })),
    ]);

    renderCategories(cats.data ?? cats, categoryId);
    renderProducts(prods);
  } catch (e) {
    document.getElementById('categories-list').innerHTML = '<p class="muted">Failed to load categories.</p>';
    document.getElementById('products-grid').innerHTML = '<p class="muted">Failed to load listing.</p>';
    document.getElementById('pagination').innerHTML = '';
  }
}

document.addEventListener('DOMContentLoaded', boot);
