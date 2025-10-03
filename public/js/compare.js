const API = {
  get:   () => '/api/compare',
  clear: () => '/api/compare/clear',
  remove: (id) => `/api/compare/${encodeURIComponent(id)}`,
};

function updateCompareBadge(count){
  const el = document.getElementById('compare-count');
  if (el) el.textContent = count ?? 0;
}

async function safe(method, url){
  const r = await fetch(url, { method, headers: { 'Accept': 'application/json' }});
  if (!r.ok && r.status !== 204) throw new Error(`HTTP ${r.status}`);
  return r.status === 204 ? null : r.json();
}

function ratingStars(val){
  if (val == null) return '—';
  const full = Math.round(Number(val) * 2) / 2;
  return `${full.toFixed(1)} ★`;
}

function esc(s){
  return String(s ?? '')
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}

function renderEmpty(){
  document.getElementById('compare-skeleton').classList.add('hidden');
  document.getElementById('compare-wrapper').classList.add('hidden');
  document.getElementById('compare-one').classList.add('hidden');
  document.getElementById('compare-empty').classList.remove('hidden');
  document.getElementById('clear-all').style.display = 'none';
}

function renderOne(){
  document.getElementById('compare-skeleton').classList.add('hidden');
  document.getElementById('compare-wrapper').classList.add('hidden');
  document.getElementById('compare-empty').classList.add('hidden');
  document.getElementById('compare-one').classList.remove('hidden');
  document.getElementById('clear-all').style.display = '';
}

function renderTable(items){
  const wrap = document.getElementById('compare-wrapper');
  const grid = document.getElementById('compare-grid');

  document.getElementById('compare-skeleton').classList.add('hidden');
  document.getElementById('compare-empty').classList.add('hidden');
  document.getElementById('compare-one').classList.add('hidden');
  wrap.classList.remove('hidden');

  grid.style.setProperty('--cols', items.length);

  const headRow = `
    <div class="compare-row">
      <div class="compare-label">Product</div>
      ${items.map(p => `
        <div class="compare-head">
          <div class="img"><img src="${p.image}" alt="${esc(p.name)}" loading="lazy"></div>
          <div class="name" title="${esc(p.name)}">${p.name}</div>
          <div class="price">€${Number(p.price).toFixed(2)}</div>
          <div class="rating">${ratingStars(p.rating)}</div>
          <div class="actions">
            <button class="btn" data-remove="${p.id}">Remove</button>
            <a class="btn btn-ghost" href="listing.html?category=${encodeURIComponent(p.category_id)}&focus=${encodeURIComponent(p.id)}">Details</a>
          </div>
        </div>
      `).join('')}
    </div>
  `;

  const toList = (arr, prefix='') => {
    const a = Array.isArray(arr) ? arr : [];
    if (!a.length) return '—';
    return `<ul class="compare-list">${a.map(x=>`<li>${prefix}${esc(x)}</li>`).join('')}</ul>`;
  };

  const rows = [
    { label: 'Name', render: p => esc(p.name) },
    { label: 'Price', render: p => `€${Number(p.price).toFixed(2)}` },
    { label: 'Rating', render: p => ratingStars(p.rating) },
    { label: 'Key features', render: p => toList(p.key_features) },
    { label: 'Pros', render: p => toList(p.pros, '+ ') },
    { label: 'Cons', render: p => toList(p.cons, '− ') },
  ].map(row => `
    <div class="compare-row">
      <div class="compare-label">${row.label}</div>
      ${items.map(p => `<div class="compare-cell">${row.render(p)}</div>`).join('')}
    </div>
  `).join('');

  grid.innerHTML = headRow + rows;

  grid.querySelectorAll('[data-remove]').forEach(btn=>{
    btn.addEventListener('click', async ()=>{
      const id = btn.getAttribute('data-remove');
      try { await safe('DELETE', API.remove(id)); await load(); }
      catch { alert('Failed to remove item.'); }
    });
  });

  document.getElementById('clear-all').style.display = '';
}

async function load(){
  document.getElementById('year').textContent = new Date().getFullYear();
  document.getElementById('compare-skeleton').classList.remove('hidden');

  try{
    const payload = await safe('GET', API.get());
    const items = Array.isArray(payload?.data) ? payload.data : [];

    updateCompareBadge(items.length);

    if (items.length === 0) return renderEmpty();
    if (items.length === 1) return renderOne();
    return renderTable(items.slice(0, 3));
  }catch(e){
    document.getElementById('compare-skeleton').classList.add('hidden');
    document.getElementById('compare-empty').classList.remove('hidden');
    document.getElementById('compare-empty').querySelector('h3').textContent = 'Failed to load comparison';
  }
}

document.addEventListener('DOMContentLoaded', ()=>{
  load();

  const clearBtn = document.getElementById('clear-all');
  clearBtn.addEventListener('click', async ()=>{
    try{ await safe('DELETE', API.clear()); await load(); }
    catch{ alert('Failed to clear comparison.'); }
  });
});
