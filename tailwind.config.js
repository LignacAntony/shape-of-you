/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          '50': '#f5f8f6',
          '100': '#e0e7e4',
          '200': '#c0cfc7',
          '300': '#9db2a8',
          '400': '#738e81',
          '500': '#597368',
          '600': '#465b52',
          '700': '#3a4b44',
          '800': '#313e39',
          '900': '#2c3531',
          '950': '#161d1b',
        },
      },},
  },
  plugins: [],
}

