/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      screens: {
        'mobile': '320px',   // Optimierung für schmale Smartphones
        'tablet': '768px',   // Tablets im Hochformat
        'desktop': '1024px', // Standard Desktop-Monitore
      },
    },
  },
  plugins: [],
}
