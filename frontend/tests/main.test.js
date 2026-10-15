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
        vi.resetModules();

        // Deterministische Mock-Antwort für das Nachladen der Fristen
        const mockFetch = vi.fn().mockResolvedValue({
            ok: true,
            json: () => Promise.resolve({
                id: "uuid-123",
                title: "Test Projekt",
                budget_in_cents: 5000000,
                deadlines: [
                    { id: "dl-1", title: "Frist 1", due_date: "2026-12-31T00:00:00Z", risk_level: "high" }
                ]
            })
        });

        // Best Practice: Beide Scopes (Node & JSDOM Window) lückenlos abdecken
        global.fetch = mockFetch;
        window.fetch = mockFetch;

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
        await import("../src/main.js");
        document.dispatchEvent(new Event("DOMContentLoaded"));

        await vi.waitFor(() => {
            const card = document.querySelector(".project-card");
            if (!card) throw new Error("Projektkarte noch nicht gerendert");
        }, { timeout: 1000, interval: 20 });

        const card = document.querySelector(".project-card");
        const submitBtn = document.getElementById("ki-submit-btn");
        const inputId = document.getElementById("ki-project-id");

        expect(card).toBeTruthy();
        expect(submitBtn.disabled).toBe(true);

        // Klick simulieren -> Nutzt jetzt den sauberen window.fetch-Stub
        card.click();

        expect(submitBtn.disabled).toBe(false);
        expect(inputId.value).toBe("uuid-123");

        // Validieren, dass die Fristen-Erweiterung im DOM erfolgreich gerendert wurde
        await vi.waitFor(() => {
            const extension = card.querySelector(".deadlines-extension");
            expect(extension.innerHTML).toContain("Frist 1");
        });
    });
});
