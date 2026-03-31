const toggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
if (toggle && sidebar) {
  toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
}

const openModal = (modal) => {
  if (!modal) return;
  modal.classList.add('open');
  modal.setAttribute('aria-hidden', 'false');
};

const closeModal = (modal) => {
  if (!modal) return;
  modal.classList.remove('open');
  modal.setAttribute('aria-hidden', 'true');
};

document.querySelectorAll('[data-modal-target]').forEach((button) => {
  button.addEventListener('click', () => {
    openModal(document.getElementById(button.dataset.modalTarget));
  });
});

document.querySelectorAll('[data-modal-close]').forEach((button) => {
  button.addEventListener('click', () => closeModal(button.closest('.modal')));
});

document.querySelectorAll('.modal').forEach((modal) => {
  modal.addEventListener('click', (event) => {
    if (event.target === modal) closeModal(modal);
  });
});

window.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') {
    document.querySelectorAll('.modal.open').forEach(closeModal);
  }
});

const updateToggleTarget = (checkbox) => {
  const targetId = checkbox.dataset.toggleTarget;
  if (!targetId) return;
  const target = document.querySelector(`[data-toggle-id="${targetId}"]`);
  if (!target) return;
  target.classList.toggle('hidden', !checkbox.checked);
};

document.querySelectorAll('[data-toggle-target]').forEach((checkbox) => {
  updateToggleTarget(checkbox);
  checkbox.addEventListener('change', () => updateToggleTarget(checkbox));
});

if (window.dashboardCharts) {
  const f = document.getElementById('chartFluxo');
  const c = document.getElementById('chartCategoria');
  if (f) {
    new Chart(f, {type: 'bar', data: {labels: ['Entradas', 'Saídas'], datasets: [{data: window.dashboardCharts.fluxo, backgroundColor: ['#1f8f5f', '#dc2626']} ]}});
  }
  if (c) {
    new Chart(c, {type: 'doughnut', data: {labels: window.dashboardCharts.categorias, datasets: [{data: window.dashboardCharts.valores, backgroundColor: ['#1f8f5f', '#34d399', '#86efac', '#16a34a', '#15803d', '#22c55e', '#65a30d', '#84cc16']} ]}});
  }
}
