// ============================================
// AGENCE IMMOBILIÈRE - JAVASCRIPT PRINCIPAL
// ============================================

document.addEventListener('DOMContentLoaded', function () {

  // --- Flash messages auto-dismiss ---
  const flashMsgs = document.querySelectorAll('.flash-msg');
  flashMsgs.forEach(msg => {
    setTimeout(() => {
      msg.style.opacity = '0';
      msg.style.transform = 'translateX(120%)';
      setTimeout(() => msg.remove(), 400);
    }, 4000);
  });

  // --- Gallery photo switcher ---
  const thumbs = document.querySelectorAll('.thumb-photo');
  const mainPhoto = document.getElementById('mainPhoto');
  if (thumbs.length && mainPhoto) {
    thumbs.forEach(thumb => {
      thumb.addEventListener('click', function () {
        mainPhoto.style.opacity = '0';
        setTimeout(() => {
          mainPhoto.src = this.src;
          mainPhoto.style.opacity = '1';
        }, 180);
        thumbs.forEach(t => t.classList.remove('active'));
        this.classList.add('active');
      });
    });
    mainPhoto.style.transition = 'opacity 0.2s ease';
    if (thumbs[0]) thumbs[0].classList.add('active');
  }

  // --- Favori toggle ---
  document.querySelectorAll('.btn-favori').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const bienId = this.dataset.id;
      fetch(`/immo/client/ajax/favori.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ bien_id: bienId, csrf: document.querySelector('meta[name=csrf]')?.content })
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          this.classList.toggle('active');
          this.innerHTML = this.classList.contains('active') ? '❤️' : '🤍';
          showToast(data.message, 'success');
        } else if (data.redirect) {
          window.location.href = data.redirect;
        }
      })
      .catch(() => showToast('Erreur réseau', 'danger'));
    });
  });

  // --- Admin sidebar toggle (mobile) ---
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.querySelector('.admin-sidebar');
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
    });
  }

  // --- Preview photos upload ---
  for (let i = 1; i <= 10; i++) {
    const input = document.getElementById('photo' + i);
    if (input) {
      input.addEventListener('change', function () {
        const preview = document.getElementById('preview_photo' + i);
        if (preview && this.files[0]) {
          const reader = new FileReader();
          reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
          };
          reader.readAsDataURL(this.files[0]);
        }
      });
    }
  }

  // --- Confirm delete ---
  document.querySelectorAll('.confirm-delete').forEach(btn => {
    btn.addEventListener('click', function (e) {
      if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.')) {
        e.preventDefault();
      }
    });
  });

  // --- Prix range display ---
  const prixMin = document.getElementById('prix_min');
  const prixMax = document.getElementById('prix_max');
  if (prixMin) {
    prixMin.addEventListener('input', () => {
      const el = document.getElementById('prix_min_display');
      if (el) el.textContent = parseInt(prixMin.value).toLocaleString('fr-FR') + ' FCFA';
    });
  }

  // --- Counter animation for stats ---
  const counters = document.querySelectorAll('.stat-number[data-target]');
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });
  counters.forEach(c => observer.observe(c));

  function animateCounter(el) {
    const target = parseInt(el.dataset.target);
    const duration = 1500;
    const start = Date.now();
    const timer = setInterval(() => {
      const elapsed = Date.now() - start;
      const progress = Math.min(elapsed / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.floor(eased * target) + (el.dataset.suffix || '');
      if (progress === 1) clearInterval(timer);
    }, 16);
  }

  // --- Search form auto-submit on select change ---
  const searchForm = document.getElementById('searchForm');
  if (searchForm) {
    searchForm.querySelectorAll('select').forEach(sel => {
      sel.addEventListener('change', () => searchForm.submit());
    });
  }

  // --- Admin status change ---
  document.querySelectorAll('.select-statut').forEach(sel => {
    sel.addEventListener('change', function () {
      const bienId = this.dataset.id;
      fetch('/immo/admin/ajax/update_statut.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: bienId, statut: this.value })
      })
      .then(r => r.json())
      .then(data => {
        showToast(data.message, data.success ? 'success' : 'danger');
      });
    });
  });
});

// --- Toast notification ---
function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `flash-msg alert alert-${type} shadow`;
  toast.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>${message}`;
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(120%)';
    toast.style.transition = 'all 0.4s ease';
    setTimeout(() => toast.remove(), 400);
  }, 3500);
}
