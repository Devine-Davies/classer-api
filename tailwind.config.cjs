/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/**/*.php",
  ],
  theme: {
    extend: {
      colors: {
        "admin-ink": "#00a6ff",
        "admin-muted": "#66717a",
        "admin-stroke": "#dde4ea",
        "admin-primary": "#0d7f78",
        "admin-primary-soft": "#e5f4f3",
        'admin-secondary': "#fafafa",
        'classer-cream': "#F6F4F1",
        "classer-warm": "#f7f3ee",
        "classer-light": "#f2f2f2",
      },
    },
  },
  plugins: [],
};