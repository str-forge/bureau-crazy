import { beforeEach, describe, expect, test, vi } from "vitest";

// Pfad zeigt sauber zurück ins src-Verzeichnis unter Umgehung der API
vi.mock("../src/api.js", () => ({
    fetchFundingProjects: vi.fn().mockResolvedValue([
        { id: "uuid-123", title: "Test Projekt", budgetInCents: 5000000 }
    ]),
    analyzeProjectText: vi.fn().mockResolvedValue({ status: "Analysis completed" })
}));

describe("Frontend Dashboard UI Grid", () => {
    beforeEach(() => {
        // Cache löschen, damit bei jedem Testdurchlauf das main.js Modul frisch geladen wird
        vi.resetModules();

        // Simuliere die exakte DOM-Struktur aus der index.html
        document.body.innerHTML = `
      <div id="project-list"></div>
      <form id="project-form">
        <input type="text" id="proj-title" />
        <input type="number" id="proj-budget" />
      </form>
      <button id="btn-reload">Reload</button>
      <form id="ki-analysis-form">
        <input type="hidden" id="ki-project-id" value="" />
        <textarea id="ki-guideline-text"></textarea>
        <button type="submit" id="ki-submit-btn" disabled>Analyse starten</button>
        <p id="ki-status-hint">Wähle ein Projekt...</p>
      </form>
    `;
    });

    test("sollte das KI-Formular aktivieren, wenn auf eine Projektkarte geklickt wird", async () => {
        // Importiert die Hauptlogik aus dem src-Verzeichnis
        await import("../src/main.js");

        // Trigger das DOMContentLoaded-Event manuell, da das DOM im Test bereits steht
        document.dispatchEvent(new Event("DOMContentLoaded"));

        // Warte explizit, bis die asynchrone API-Verbindung ausgewertet und die Karte gerendert wurde
        await vi.waitFor(() => {
            const card = document.querySelector(".project-card");
            if (!card) throw new Error("Projektkarte noch nicht gerendert");
        }, { timeout: 1000, interval: 20 });

        const card = document.querySelector(".project-card");
        const submitBtn = document.getElementById("ki-submit-btn");
        const inputId = document.getElementById("ki-project-id");

        expect(card).toBeTruthy();
        expect(submitBtn.disabled).toBe(true);

        // Klick simulieren
        card.click();

        // Assertions prüfen die Aktivierung im Formular
        expect(submitBtn.disabled).toBe(false);
        expect(inputId.value).toBe("uuid-123");
    });
});
