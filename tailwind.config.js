import forms from '@tailwindcss/forms';

export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                graphite: {
                    950: '#050506',
                    925: '#0a0a0c',
                    900: '#111114',
                    850: '#18181b',
                    800: '#242428',
                    700: '#3a3a42',
                },
                lumoryx: {
                    purple: '#9f7aea',
                    violet: '#b794f4',
                    cyan: '#d7b21f',
                    blue: '#334155',
                    navy: '#0f0f12',
                },
            },
            boxShadow: {
                glow: '0 14px 34px rgba(215, 178, 31, 0.10)',
                panel: '0 16px 42px rgba(0, 0, 0, 0.34)',
            },
        },
    },
    plugins: [forms],
};
