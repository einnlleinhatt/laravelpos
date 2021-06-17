const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
  theme: {
    screens: {},
    extend: {},
    gradients: theme => ({
      'blue-purple': ['30deg', theme('colors.blue.700'), theme('colors.purple.700')],
      'blue-teal': ['30deg', theme('colors.blue.800'), theme('colors.teal.600')],
      'gradient-gray': ['120deg', theme('colors.gray.100'), theme('colors.gray.300'), theme('colors.gray.400')],
      'mono-circle': {
        type: 'radial',
        colors: ['circle', theme('colors.gray.700'), theme('colors.gray.900')],
      },
    }),
  },
  variants: {},
  plugins: [require('tailwindcss-plugins/gradients')],
};
