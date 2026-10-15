import { defineConfig } from "vite";
import { resolve } from "path";

export default defineConfig({
    test: {
        environment: "jsdom",
        include: ["tests/**/*.test.js"],
        alias: {
            "./style.css": resolve(import.meta.dirname, "tests/style-mock.js"),
        },
    },
});
