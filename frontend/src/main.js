import "./style.css";
import { fetchFundingProjects, createFundingProject, analyzeProjectText } from "./api.js";

document.addEventListener("DOMContentLoaded", () => {
    const listContainer = document.getElementById("project-list");
    const projectForm = document.getElementById("project-form");
    const titleInput = document.getElementById("proj-title");
    const budgetInput = document.getElementById("proj-budget");
    const btnReload = document.getElementById("btn-reload");

    const kiForm = document.getElementById("ki-analysis-form");
    const kiInputId = document.getElementById("ki-project-id");
    const kiTextArea = document.getElementById("ki-guideline-text");
    const kiSubmitBtn = document.getElementById("ki-submit-btn");
    const kiStatusHint = document.getElementById("ki-status-hint");

    if (btnReload) btnReload.addEventListener("click", loadDashboardData);
    if (projectForm) projectForm.addEventListener("submit", handleFormSubmit);
    if (kiForm) kiForm.addEventListener("submit", handleKiSubmit);

    loadDashboardData();

    async function loadDashboardData() {
        try {
            if (listContainer) listContainer.innerHTML = "<div class=\"font-mono\">🔄 Verbinde mit API...</div>";
            const projects = await fetchFundingProjects();
            if (!listContainer) return;

            if (projects.length === 0) {
                listContainer.innerHTML = "<p class=\"text-slate-400 italic\">Keine Projekte vorhanden. Lege links eins an!</p>";
                return;
            }

            listContainer.innerHTML = projects.map(p => `
                <div data-id="${p.id}" class="project-card p-4 border border-slate-200 rounded-xl bg-slate-50 flex flex-col shadow-sm cursor-pointer hover:border-slate-400 transition-all">
                    <div class="flex justify-between items-center w-full">
                        <div class="flex-1">
                            <h3 class="font-semibold text-slate-900">${p.title}</h3>
                            <p class="text-xs text-slate-400 font-mono mb-2">${p.id}</p>
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
                    <div class="deadlines-extension w-full"></div>
                </div>
            `).join("");

            initProjectCardEvents();
        } catch (e) {
            if (listContainer) listContainer.innerHTML = "<div class=\"p-4 bg-red-50 text-red-700 rounded-xl text-xs\">🚨 API nicht erreichbar</div>";
        }
    }

    function initProjectCardEvents() {
        if (!listContainer) return;
        const cards = listContainer.querySelectorAll(".project-card");
        cards.forEach(card => {
            card.addEventListener("click", async () => {
                cards.forEach(c => {
                    c.classList.remove("border-slate-900", "bg-slate-100");
                    const ext = c.querySelector(".deadlines-extension");
                    if (ext) ext.innerHTML = "";
                });
                card.classList.add("border-slate-900", "bg-slate-100");

                const projectId = card.getAttribute("data-id") || "";

                if (kiInputId && kiSubmitBtn && kiStatusHint) {
                    kiInputId.value = projectId;
                    kiSubmitBtn.disabled = false;
                    kiStatusHint.textContent = `Aktiv für Projekt: ${card.querySelector("h3")?.textContent}`;
                    kiStatusHint.classList.replace("text-slate-400", "text-emerald-600");
                }

                try {
                    const response = await fetch(`http://localhost:8080/api/v1/projects/${projectId}`);
                    if (!response.ok) return;
                    const data = await response.json();

                    const extensionContainer = card.querySelector(".deadlines-extension");
                    if (!extensionContainer || !data.deadlines || data.deadlines.length === 0) return;

                    extensionContainer.innerHTML = renderDeadlinesHtml(data.deadlines);
                } catch (err) {
                    console.error("Failed to load deadlines", err);
                }
            });
        });

        listContainer.querySelectorAll(".stop-propagation").forEach(link => {
            link.addEventListener("click", (e) => e.stopPropagation());
        });
    }

    function renderDeadlinesHtml(deadlines) {
        let html = '<div class="mt-4 pt-3 border-t border-slate-200 space-y-2">';
        html += '<h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Extrahierte Fristen & Risiken:</h4>';

        deadlines.forEach(d => {
            let badgeColor = "bg-slate-100 text-slate-700";
            if (d.risk_level === "high") badgeColor = "bg-red-50 text-red-700 border border-red-200";
            if (d.risk_level === "medium") badgeColor = "bg-amber-50 text-amber-700 border border-amber-200";

            const dateStr = new Date(d.due_date).toLocaleDateString("de-DE");

            html += '<div class="flex justify-between items-center text-xs p-2 bg-white rounded-lg border border-slate-100">' +
                '<span class="font-medium text-slate-800">' + d.title + '</span>' +
                '<div class="flex items-center gap-2">' +
                '<span class="text-slate-500 font-mono">' + dateStr + '</span>' +
                '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase ' + badgeColor + '">' + d.risk_level + '</span>' +
                '</div>' +
                '</div>';
        });

        html += '</div>';
        return html;
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
        if (!titleInput || !budgetInput) return;
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
