const API = {
  categories: (limit = 5) => `/api/categories?limit=${encodeURIComponent(limit)}`,
  top10: () => `/api/top10Products`,
};

const Compare = {
  storageKey: 'compareIds',
  get() {
    try { return JSON.parse(localStorage.getItem(this.storageKey) || '[]'); }
    catch { return []; }
  },
  set(ids) {
    localStorage.setItem(this.storageKey, JSON.stringify(ids));
    updateCompareBadge();
  },
  add(id) {
    id = Number(id);
    const ids = this.get();
    if (ids.includes(id)) return;
    if (ids.length >= 3) { alert('Compare limit is 3 items.'); return; }
    ids.push(id);
    this.set(ids);
    const btn = document.querySelector(`[data-compare-add="${id}"]`);
    if (btn) { btn.disabled = true; btn.textContent = 'Added'; }
  }
};

function updateCompareBadge() {
  const el = document.getElementById('compare-count');
  if (!el) return;
  el.textContent = Compare.get().length;
}

async function safeGet(url){
  const res = await fetch(url, { headers: { 'Accept':'application/json' }});
  if(!res.ok) throw new Error(`HTTP ${res.status}`);
  return res.json();
}

function ratingStars(val){
  if (val == null) return '—';
  const full = Math.round(Number(val) * 2) / 2;
  const stars = '★★★★★☆☆☆☆☆'.slice(5 - Math.round(full), 10 - Math.round(full));
  return `${full.toFixed(1)} ★`;
}

function renderCategories(list){
  const grid = document.getElementById('categories-grid');
  if (!grid) return;
  if (!Array.isArray(list)) list = list?.data ?? [];
  if (!list.length) { grid.innerHTML = '<p class="muted">No categories.</p>'; return; }

  grid.innerHTML = list.map(c => {
    const href = `products.html?category=${encodeURIComponent(c.id)}`;
    return `
      <article class="card category-card">
        <a class="tile" href="${href}" style="text-decoration:none;color:inherit;">
          <h3>${c.name}</h3>
          <div class="pills">
            <a class="pill" href="${href}">Explore →</a>
            <a class="pill" href="compare.html">Compare →</a>
          </div>
        </a>
      </article>
    `;
  }).join('');
}

function renderTop10(list){
  const grid = document.getElementById('top10-grid');
  if (!grid) return;
  if (!Array.isArray(list)) list = list?.data ?? [];
  if (!list.length) { grid.innerHTML = '<p class="muted">No products in Top 10.</p>'; return; }

  grid.innerHTML = list.map(p => `
    <article class="card product-card">
      <div class="img-wrap">
        <img src="${p.image}" alt="${escapeHtml(p.name)}" loading="lazy" />
      </div>
      <h4 title="${escapeHtml(p.name)}">${p.name}</h4>
      <div class="price">€${Number(p.price).toFixed(2)}</div>
      <div class="rating">${ratingStars(p.rating)}</div>
      <div class="actions">
        <button class="btn" data-compare-add="${p.id}">Add to compare</button>
        <a class="btn btn-ghost" href="products.html?category=${encodeURIComponent(p.category_id)}&focus=${encodeURIComponent(p.id)}">Details</a>
      </div>
    </article>
  `).join('');

  grid.querySelectorAll('[data-compare-add]').forEach(btn => {
    const id = Number(btn.getAttribute('data-compare-add'));
    if (Compare.get().includes(id)) { btn.disabled = true; btn.textContent = 'Added'; }
    btn.addEventListener('click', () => Compare.add(id));
  });
}

function escapeHtml(s){
  return String(s ?? '')
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;')
    .replace(/'/g,'&#039;');
}

async function boot(){
  document.getElementById('year').textContent = new Date().getFullYear();
  updateCompareBadge();

  try {
    const [{ data: categoriesData = [] } = {}, { data: topData = [] } = {}] = await Promise.all([
      safeGet(API.categories(5)),
      safeGet(API.top10()),
    ]);
    renderCategories(categoriesData);
    renderTop10(topData);
  } catch (e) {
    document.getElementById('categories-grid').innerHTML = '<p class="muted">Failed to load categories.</p>';
    document.getElementById('top10-grid').innerHTML = '<p class="muted">Failed to load Top 10.</p>';
  }

  const reloadBtn = document.getElementById('reload-categories');
  if (reloadBtn){
    reloadBtn.addEventListener('click', async ()=>{
      document.getElementById('categories-grid').innerHTML = '<div class="skeleton category-skeleton"></div>'.repeat(5);
      try{
        const { data } = await safeGet(API.categories(5));
        renderCategories(data);
      }catch{
        document.getElementById('categories-grid').innerHTML = '<p class="muted">Failed to reload.</p>';
      }
    });
  }
}

document.addEventListener('DOMContentLoaded', boot);
