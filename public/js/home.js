const API = {
  categories: (limit = 5) => `/api/categories?limit=${encodeURIComponent(limit)}`,
  top10: () => `/api/top10Products`,
  compareList: () => `/api/compare`,
  compareAdd: () => `/api/compare/add`,
  compareRemove: (id) => `/api/compare/remove/${encodeURIComponent(id)}`,
};

const CompareAPI = {
  async list() {
    const r = await fetch(
      API.compareList(), { 
        headers: { 
          'Accept': 'application/json' 
        }
      }
    );

    if (!r.ok) {
      throw new Error(`HTTP ${r.status}`);
    }
    const json = await r.json();
    return Array.isArray(json?.data) ? json.data : [];
  },

  async add(id) {
    const r = await fetch(API.compareAdd(), {
      method: 'POST',
      headers: { 
        'Accept': 'application/json', 
        'Content-Type': 'application/json' 
      },
      body: JSON.stringify({ product_id: Number(id) }),
      keepalive: true,
      credentials: 'same-origin',
    });

    if (!r.ok) {
      let msg = `HTTP ${r.status}`;

      try { 
        const body = await r.json(); 

        if (body?.message) {
          msg = body.message;
        } 
      } catch {
        throw new Error(msg);
      }
    }

    const json = await r.json();
    return Array.isArray(json?.data) ? json.data : [];
  },

  async remove(id) {
    const r = await fetch(
      API.compareRemove(id), { 
        method:'DELETE', 
        headers:{ 
          'Accept': 'application/json' 
        },
        credentials: 'same-origin'
      }
    );
  
    if (!r.ok && r.status !== 204) {
      throw new Error(`HTTP ${r.status}`);
    }

    return true;
  }
};

async function updateCompareBadgeFromAPI() {
  try {
    const list = await CompareAPI.list();
    const el = document.getElementById('compare-count');

    if (el) {
      el.textContent = list.length;
    }
  } catch {}
}

async function safeGet(url) {
  const res = await fetch(
    url, { 
      headers: { 
        'Accept': 'application/json' 
      } 
    }
  );

  if (!res.ok) {
    throw new Error(`HTTP ${res.status}`);
  }

  return res.json();
}

function ratingStars(val) {
  if (val == null) {
    return '—';
  }

  const full = Math.round(Number(val) * 2) / 2;

  return `${full.toFixed(1)} ★`;
}

function renderCategories(list) {
  const grid = document.getElementById('categories-grid');

  if (!grid) return;

  if (!Array.isArray(list)) {
    list = list?.data ?? [];
  }

  if (!list.length) { 
    grid.innerHTML = '<p class="muted">No categories.</p>'; 
    return; 
  }

  grid.innerHTML = list.map(c => {
    const href = `/listing?category=${encodeURIComponent(c.slug)}`;

    return `
      <article class="card category-card">
        <a class="tile" href="${href}" style="text-decoration:none;color:inherit;">
          <h3>${c.name}</h3>
          <div class="pills">
            <a class="pill" href="${href}">Explore →</a>
            <a class="pill" href="/compare">Compare →</a>
          </div>
        </a>
      </article>
    `;
  }).join('');
}

function renderTop10(list, chosen = new Set()) {
  const grid = document.getElementById('top10-grid');
  if (!grid) return;

  if (!Array.isArray(list)) {
    list = list?.data ?? [];
  }

  if (!list.length) { 
    grid.innerHTML = '<p class="muted">No products in Top 10.</p>'; 
    return; 
  }

  grid.innerHTML = list.map(p => {
    const isChosen = chosen.has(p.id);
    return `
      <article class="card product-card">
        <div class="img-wrap">
          <img src="${p.image}" alt="${escapeHtml(p.name)}" loading="lazy" />
        </div>
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

  grid.querySelectorAll('[data-compare-add]').forEach(btn => {
    const id = Number(btn.getAttribute('data-compare-add'));

    btn.addEventListener('click', async () => {
      try {
        await CompareAPI.add(id);
        btn.style.display = 'none';
        const rm = grid.querySelector(`[data-compare-remove="${id}"]`);

        if (rm) {
          rm.style.display = '';
        }
        updateCompareBadgeFromAPI();
      } catch (e) { 
        alert(e.message || 'Failed to add to compare'); 
      }
    });
  });

  grid.querySelectorAll('[data-compare-remove]').forEach(btn => {
    const id = Number(btn.getAttribute('data-compare-remove'));
    btn.addEventListener('click', async () => {
      try {
        await CompareAPI.remove(id);
        btn.style.display = 'none';
        const add = grid.querySelector(`[data-compare-add="${id}"]`);

        if (add) {
          add.style.display = '';
        }

        updateCompareBadgeFromAPI();
      } catch { 
        alert('Failed to remove from compare'); 
      }
    });
  });
}

function escapeHtml(s) {
  return String(s ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

async function boot() {
  document.getElementById('year').textContent = new Date().getFullYear();

  try {
    const [
      { data: categoriesData = [] } = {},
      { data: topData = [] } = {},
      selected = []
    ] = await Promise.all([
      safeGet(API.categories(5)),
      safeGet(API.top10()),
      CompareAPI.list(),
    ]);

    const chosen = new Set(selected.map(p => p.id));
    renderCategories(categoriesData);
    renderTop10(topData, chosen);
  } catch (e) {
    document.getElementById('categories-grid').innerHTML = '<p class="muted">Failed to load categories.</p>';
    document.getElementById('top10-grid').innerHTML = '<p class="muted">Failed to load Top 10.</p>';
  }

  updateCompareBadgeFromAPI();

  const reloadBtn = document.getElementById('reload-categories');
  if (reloadBtn) {
    reloadBtn.addEventListener('click', async () => {
      document.getElementById('categories-grid').innerHTML = '<div class="skeleton category-skeleton"></div>'.repeat(5);
      try {
        const { data } = await safeGet(API.categories(5));
        renderCategories(data);
      } catch {
        document.getElementById('categories-grid').innerHTML = '<p class="muted">Failed to reload.</p>';
      }
    });
  }
}

document.addEventListener('DOMContentLoaded', boot);
