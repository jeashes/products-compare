const API = {
  categories: () => `/api/categories`,
  products: ({ categorySlug, page = 1, perPage = 15 }) => {
    const params = new URLSearchParams();
    params.set('per_page', perPage);
    if (categorySlug) params.set('category_slug', categorySlug);
    if (page) params.set('page', page);
    return `/api/products?${params.toString()}`;
  },
  compareList: () => `/api/compare`,
  compareAdd: () => `/api/compare/add`,
  compareRemove: (id) => `/api/compare/remove/${encodeURIComponent(id)}`,
};

const CompareAPI = {
  async list() {
    const r = await fetch(API.compareList(), { headers: { 'Accept': 'application/json' } });
    if (!r.ok) throw new Error(`HTTP ${r.status}`);
    const json = await r.json();
    return Array.isArray(json?.data) ? json.data : [];
  },
  async add(id) {
    const r = await fetch(API.compareAdd(), {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id: Number(id) }),
    });
    if (!r.ok) {
      let msg = `HTTP ${r.status}`;
      try { const body = await r.json(); if (body?.message) msg = body.message; } catch {}
      throw new Error(msg);
    }
    const json = await r.json();
    return Array.isArray(json?.data) ? json.data : [];
  },
  async remove(id){
    const r = await fetch(API.compareRemove(id), { method:'DELETE', headers:{ 'Accept':'application/json' }});
    if (!r.ok && r.status !== 204) throw new Error(`HTTP ${r.status}`);
    return true;
  },
};

async function updateCompareBadgeFromAPI(){
  try {
    const list = await CompareAPI.list();
    const el = document.getElementById('compare-count');
    if (el) el.textContent = list.length;
  } catch {}
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

function renderCategories(categories, activeSlug){
  const wrap = document.getElementById('categories-list');
  if (!Array.isArray(categories)) categories = categories?.data ?? [];
  if (!wrap) return;

  wrap.innerHTML = categories.map(c=>{
    const href = `listing.html?category=${encodeURIComponent(c.slug)}`;
    const active = String(activeSlug || '') === String(c.slug);
    return `
      <div class="filter-item">
        <a class="filter-link ${active?'active':''}" href="${href}">
          ${c.name}
        </a>
      </div>
    `;
  }).join('');

  const clear = document.getElementById('clear-filter');
  if (clear) clear.style.display = activeSlug ? '' : 'none';

  const title = document.getElementById('listing-title');
  if (title) {
    const catName = categories.find(x => String(x.slug) === String(activeSlug))?.name ?? '';
    title.textContent = activeSlug ? `Products · ${catName}` : 'All Products';
  }
}

function renderProducts(payload, chosen = new Set(), currentCategorySlug = null){
  const grid = document.getElementById('products-grid');
  if (!grid) return;

  const data = payload?.data ?? [];
  if (!data.length){
    grid.innerHTML = '<p class="muted">No products found.</p>';
    document.getElementById('pagination').innerHTML = '';
    return;
  }

  grid.innerHTML = data.map(p => {
    const isChosen = chosen.has(p.id);
    return `
      <article class="card product-card">
        <div class="img-wrap"><img src="${p.image}" alt="${escapeHtml(p.name)}" loading="lazy"></div>
        <h4 title="${escapeHtml(p.name)}">${p.name}</h4>
        <div class="price">€${Number(p.price).toFixed(2)}</div>
        <div class="rating">${ratingStars(p.rating)}</div>
        <div class="actions">
          <button class="btn" data-compare-add="${p.id}" ${isChosen?'style="display:none"':''}>Add to compare</button>
          <button class="btn btn-ghost" data-compare-remove="${p.id}" ${isChosen?'':'style="display:none"'}>Remove</button>
        </div>
      </article>
    `;
  }).join('');

  grid.querySelectorAll('[data-compare-add]').forEach(btn=>{
    const id = Number(btn.getAttribute('data-compare-add'));
    btn.addEventListener('click', async ()=>{
      try{
        await CompareAPI.add(id);
        btn.style.display = 'none';
        const rm = grid.querySelector(`[data-compare-remove="${id}"]`);
        if (rm) rm.style.display = '';
        updateCompareBadgeFromAPI();
      }catch(e){ alert(e.message || 'Failed to add to compare'); }
    });
  });

  grid.querySelectorAll('[data-compare-remove]').forEach(btn=>{
    const id = Number(btn.getAttribute('data-compare-remove'));
    btn.addEventListener('click', async ()=>{
      try{
        await CompareAPI.remove(id);
        btn.style.display = 'none';
        const add = grid.querySelector(`[data-compare-add="${id}"]`);
        if (add) add.style.display = '';
        updateCompareBadgeFromAPI();
      }catch{ alert('Failed to remove from compare'); }
    });
  });

  renderPagination(payload.meta, payload.links);
}

function renderPagination(meta){
  const p = document.getElementById('pagination');
  if (!p || !meta) { if (p) p.innerHTML=''; return; }

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

  const categorySlug = qs('category');
  const page = Number(qs('page', 1)) || 1;

  try {
    const [cats, prods, selected] = await Promise.all([
      safeGet(API.categories()),
      safeGet(API.products({ categorySlug, page, perPage: 15 })),
      CompareAPI.list(),
    ]);

    const chosen = new Set(selected.map(p => p.id));

    renderCategories(cats.data ?? cats, categorySlug);
    renderProducts(prods, chosen, categorySlug);
  } catch (e) {
    document.getElementById('categories-list').innerHTML = '<p class="muted">Failed to load categories.</p>';
    document.getElementById('products-grid').innerHTML = '<p class="muted">Failed to load listing.</p>';
    document.getElementById('pagination').innerHTML = '';
  }

  updateCompareBadgeFromAPI();
}

document.addEventListener('DOMContentLoaded', boot);
