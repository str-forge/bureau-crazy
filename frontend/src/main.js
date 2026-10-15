import "./style.css";
import { fetchFundingProjects, createFundingProject, analyzeProjectText } from "./api.js";

document.addEventListener("DOMContentLoaded", () => {
    const listContainer = document.getElementById("project-list");
    const projectForm = document.getElementById("project-form");
    const titleInput = document.getElementById("proj-title");
    const budgetInput = document.getElementById("proj-budget");
    const btnReload = document.getElementById("btn-reload");

    // KI-Formular DOM-Elemente
    const kiForm = document.getElementById("ki-analysis-form");
    const kiInputId = document.getElementById("ki-project-id");
    const kiTextArea = document.getElementById("ki-guideline-text");
    const kiSubmitBtn = document.getElementById("ki-submit-btn");
    const kiStatusHint = document.getElementById("ki-status-hint");

    btnReload.addEventListener("click", loadDashboardData);
    projectForm.addEventListener("submit", handleFormSubmit);
    if (kiForm) kiForm.addEventListener("submit", handleKiSubmit);

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
                <div data-id="${p.id}" class="project-card p-4 border border-slate-200 rounded-xl bg-slate-50 flex justify-between items-center shadow-sm cursor-pointer hover:border-slate-400 transition-all">
                    <div class="flex-1">
                        <h3 class="font-semibold text-slate-900">${p.title}</h3>
                        <p class="text-xs text-slate-400 font-mono mb-2">${p.id}</p>
                        <!-- iCal Download Link -->
                        <a href="http://localhost:8080/api/v1/projects/${p.id}/ical" 
                           class="inline-flex items-center gap-1 text-xs font-medium text-slate-700 hover:text-slate-900 bg-white hover:bg-slate-100 border border-slate-200 rounded-md px-2 py-1 shadow-sm transition-colors stop-propagation" 
                           download>
                           📅 iCal Export
                        </a>
                    </div>
                    <span class="text-sm font-semibold bg-emerald-50 text-emerald-700 px-2 py-1 rounded-lg self-start">
                        ${(p.budgetInCents / 100).toFixed(2)} EUR
                    </span>
                </div>
            `).join("");


            initProjectCardEvents();
        } catch (e) {
            listContainer.innerHTML = "<div class=\"p-4 bg-red-50 text-red-700 rounded-xl text-xs\">🚨 API nicht erreichbar</div>";
        }
    }

    function initProjectCardEvents() {
        const cards = listContainer.querySelectorAll(".project-card");
        cards.forEach(card => {
            card.addEventListener("click", () => {
                // Selektion visuell hervorheben
                cards.forEach(c => c.classList.remove("border-slate-900", "bg-slate-100"));
                card.classList.add("border-slate-900", "bg-slate-100");

                // KI-Formular für dieses Projekt freischalten
                const projectId = card.getAttribute("data-id") || "";
                if (kiInputId && kiSubmitBtn && kiStatusHint) {
                    kiInputId.value = projectId;
                    kiSubmitBtn.disabled = false;
                    kiStatusHint.textContent = `Aktiv für Projekt: ${card.querySelector("h3")?.textContent}`;
                    kiStatusHint.classList.replace("text-slate-400", "text-emerald-600");
                }

                // Verhindert, dass der iCal-Download den Zeilenklick für das KI-Formular auslöst
                listContainer.querySelectorAll(".stop-propagation").forEach(link => {
                    link.addEventListener("click", (e) => e.stopPropagation());
                });
            });
        });
    }

    async function handleKiSubmit(e) {
        e.preventDefault();
        if (!kiSubmitBtn || !kiInputId || !kiTextArea) return;

        kiSubmitBtn.disabled = true;
        kiSubmitBtn.textContent = "Analysiere Text...";

        try {
            await analyzeProjectText(kiInputId.value, kiTextArea.value);
            alert("KI-Analyse erfolgreich abgeschlossen. Fristen wurden extrahiert!");
            kiTextArea.value = "";
            window.location.reload();
        } catch (err) {
            alert(err.message);
            kiSubmitBtn.disabled = false;
            kiSubmitBtn.textContent = "Analyse starten";
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
