/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          light: '#0891b2', // cyan-600
          DEFAULT: '#0e7490', // cyan-700
          dark: '#155e75', // cyan-800
        },
        secondary: {
          DEFAULT: '#475569', // slate-600
        },
        clinical: {
          soft: '#f0f9ff', // sky-50
          border: '#e0f2fe', // sky-100
        }
      }
    },
  },
  plugins: [],
}
