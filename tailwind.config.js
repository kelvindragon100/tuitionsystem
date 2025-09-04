/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class', // 启用 class 模式暗黑主题
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {
      colors: {
        sidebar: {
          light: '#f8fafc', // 浅色模式背景
          dark: '#1f2937',  // 深色模式背景
        }
      },
      transitionProperty: {
        'spacing': 'margin, padding',
      }
    },
  },
  plugins: [],
}
