/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        'admin-ink': '#162127',
        'admin-muted': '#66717a',
        'admin-stroke': '#dde4ea',
        'admin-primary': '#0d7f78',
        'admin-primary-soft': '#e5f4f3',
      },
    },
  },
  plugins: [],
}