import './style.css';

document.addEventListener('DOMContentLoaded', () => {
  console.log('🚀 BureauCrazy SPA erfolgreich initialisiert!');
  const listContainer = document.getElementById('project-list');
  
  // Test-Rendering, um zu sehen, ob das JavaScript die UI manipulieren kann
  listContainer.innerHTML = `
    <div class="p-4 border border-dashed border-slate-300 rounded-lg text-center bg-slate-50">
      <p class="text-sm text-slate-600 font-medium">Frontend UI steht bereit. 🐋</p>
      <p class="text-xs text-slate-400 mt-1">Nächster Schritt: Asynchroner Fetch gegen die Symfony 7 REST-API.</p>
    </div>
  `;
});
