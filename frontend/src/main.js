import "./style.css";
import { fetchFundingProjects, createFundingProject } from "./api.js";

document.addEventListener("DOMContentLoaded", () => {
    const listContainer = document.getElementById("project-list");
    const projectForm = document.getElementById("project-form");
    const titleInput = document.getElementById("proj-title");
    const budgetInput = document.getElementById("proj-budget");
    const btnReload = document.getElementById("btn-reload");

    btnReload.addEventListener("click", loadDashboardData);
    projectForm.addEventListener("submit", handleFormSubmit);

    loadDashboardData();

    async function loadDashboardData() {
        try {
            listContainer.innerHTML = "<div class font-mono>🔄 Verbinde mit API...</div>";
            const projects = await fetchFundingProjects();
            if (projects.length === 0) {
                listContainer.innerHTML = "<p class=\"text-slate-400 italic\">Keine Projekte vorhanden. Lege links eins an!</p>";
                return;
            }
            listContainer.innerHTML = projects.map(p => `
                <div class="p-4 border border-slate-200 rounded-xl bg-slate-50 flex justify-between items-center shadow-sm">
                    <div>
                        <h3 class="font-semibold text-slate-900">${p.title}</h3>
                        <p class="text-xs text-slate-400 font-mono">${p.id}</p>
                    </div>
                    <span class="text-sm font-semibold bg-emerald-50 text-emerald-700 px-2 py-1 rounded-lg">
                        ${(p.budgetInCents / 100).toFixed(2)} EUR
                    </span>
                </div>
            `).join("");
        } catch (e) {
            listContainer.innerHTML = "<div class=\"p-4 bg-red-50 text-red-700 rounded-xl text-xs\">🚨 API nicht erreichbar</div>";
        }
    }

    async function handleFormSubmit(e) {
        e.preventDefault();
        const title = titleInput.value.trim();
        const budgetEuro = parseFloat(budgetInput.value);
        if (!title || isNaN(budgetEuro)) return;

        try {
            await createFundingProject({ title, budgetInCents: Math.round(budgetEuro * 100) });
            titleInput.value = "";
            budgetInput.value = "";
            await loadDashboardData();
        } catch (err) {
            alert("Fehler: " + err.message);
        }
    }
});
