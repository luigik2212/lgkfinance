const toggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
if (toggle && sidebar) {
  toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
}
if (window.dashboardCharts) {
  const f = document.getElementById('chartFluxo');
  const c = document.getElementById('chartCategoria');
  if (f) {
    new Chart(f, {type: 'bar', data: {labels: ['Entradas','Saídas'], datasets: [{data: window.dashboardCharts.fluxo, backgroundColor: ['#1f8f5f','#dc2626']}]}});
  }
  if (c) {
    new Chart(c, {type: 'doughnut', data: {labels: window.dashboardCharts.categorias, datasets: [{data: window.dashboardCharts.valores, backgroundColor: ['#1f8f5f','#34d399','#86efac','#16a34a','#15803d','#22c55e','#65a30d','#84cc16']} ]}});
  }
}
