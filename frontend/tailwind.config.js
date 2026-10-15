/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      screens: {
        'mobile': '320px', // Optimize for narrow smartphones
        'tablet': '768px', // Portrait tablets
        'desktop': '1024px', // Standard Desktop-Monitore
      },
    },
  },
  plugins: [],
}
