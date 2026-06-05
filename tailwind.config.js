import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['"Plus Jakarta Sans"', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Corail signature (CTA)
                brand: {
                    50:  '#FFF3EF',
                    100: '#FFE3D9',
                    200: '#FFC4B0',
                    300: '#FF9D7E',
                    400: '#FB7551',
                    500: '#F05537',
                    600: '#DB3D1B',
                    700: '#B62F13',
                    800: '#902815',
                    900: '#762616',
                },
                // Aubergine profond (sections sombres, titres) — signature Eventbrite
                ink: {
                    50:  '#F5F3F8',
                    100: '#E9E5F0',
                    200: '#CFC7DD',
                    300: '#A99CC0',
                    400: '#7C6B9B',
                    500: '#574A77',
                    600: '#3D3159',
                    700: '#2B2046',
                    800: '#241638',
                    900: '#1E0A3C',
                },
            },
            boxShadow: {
                card: '0 1px 2px rgba(30,10,60,0.04), 0 8px 24px -12px rgba(30,10,60,0.18)',
                'card-hover': '0 4px 12px rgba(30,10,60,0.08), 0 16px 40px -16px rgba(30,10,60,0.28)',
                pop: '0 12px 48px -12px rgba(30,10,60,0.30)',
            },
            borderRadius: {
                xl: '0.875rem',
                '2xl': '1.25rem',
            },
        },
    },

    plugins: [forms],
};
