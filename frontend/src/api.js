const API_BASE_URL = "http://localhost:8080/api/v1";

export async function fetchFundingProjects() {
    try {
        // Corrected to the actual Symfony path /projects
        const response = await fetch(`${API_BASE_URL}/projects`, {
            method: "GET",
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json"
            }
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return await response.json();
    } catch (error) {
        console.error("[API] fetch failed:", error);
        throw error;
    }
}

export async function createFundingProject(projectData) {
    try {
        // Corrected to the actual Symfony path /projects
        const response = await fetch(`${API_BASE_URL}/projects`, {
            method: "POST",
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json"
            },
            body: JSON.stringify(projectData)
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return await response.json();
    } catch (error) {
        console.error("[API] store failed:", error);
        throw error;
    }
}

export async function analyzeProjectText(projectId, textPayload) {
    const response = await fetch(`/api/v1/projects/${projectId}/analyze`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ text: textPayload }),
    });

    if (!response.ok) {
        throw new Error('Fehler bei der KI-Analyse des Projekts.');
    }

    return response.json();
}
