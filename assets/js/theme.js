document.addEventListener('DOMContentLoaded', () => {
    // Récupérer le thème depuis le localStorage ou utiliser le thème par défaut
    const getTheme = () => {
        const theme = localStorage.getItem('theme');
        if (theme) {
            try {
                return JSON.parse(theme).theme;
            } catch (e) {
                return 'dark';
            }
        }
        return 'dark';
    };

    // Appliquer le thème
    const applyTheme = (theme) => {
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        localStorage.setItem('theme', JSON.stringify({ theme }));

        // Envoyer une requête AJAX pour mettre à jour les préférences utilisateur
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrfToken) {
            fetch('/profile/update-theme', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ theme })
            });
        }
    };

    // Initialiser le thème
    const currentTheme = getTheme();
    applyTheme(currentTheme);

    // Écouter les changements de thème
    window.addEventListener('theme-change', (e) => {
        applyTheme(e.detail.theme);
    });
}); 