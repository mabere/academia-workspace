const sidebarToggles = document.querySelectorAll('#sidebar-toggle, #sidebar-toggle-mobile');
const mobileSidebarToggle = document.getElementById('sidebar-toggle-mobile');
const sidebarBackdrop = document.getElementById('sidebar-backdrop');
const sidebar = document.getElementById('workspace-sidebar');

if (sidebarToggles.length) {
    const mobileSidebar = window.matchMedia('(max-width: 767px)');
    const updateSidebarState = () => {
        const expanded = mobileSidebar.matches
            ? document.body.classList.contains('sidebar-open')
            : !document.body.classList.contains('sidebar-collapsed');

        sidebarToggles.forEach(toggle => {
            toggle.setAttribute('aria-expanded', String(expanded));
            toggle.setAttribute('aria-label', expanded ? 'Ciutkan menu samping' : 'Buka menu samping');

            const showCloseIcon = mobileSidebar.matches ? expanded : !expanded;
            toggle.querySelector('[data-sidebar-icon="menu"]')?.classList.toggle('hidden', showCloseIcon);
            toggle.querySelector('[data-sidebar-icon="close"]')?.classList.toggle('hidden', !showCloseIcon);
        });

        if (sidebar && mobileSidebar.matches) {
            sidebar.inert = !expanded;
            sidebar.setAttribute('aria-hidden', String(!expanded));
        } else if (sidebar) {
            sidebar.inert = false;
            sidebar.removeAttribute('aria-hidden');
        }
    };

    sidebarToggles.forEach(toggle => {
        toggle.addEventListener('click', () => {
            document.body.classList.toggle(mobileSidebar.matches ? 'sidebar-open' : 'sidebar-collapsed');
            updateSidebarState();
        });
    });

    sidebarBackdrop?.addEventListener('click', () => {
        document.body.classList.remove('sidebar-open');
        updateSidebarState();
        mobileSidebarToggle?.focus();
    });

    mobileSidebar.addEventListener('change', () => {
        document.body.classList.remove('sidebar-open');
        updateSidebarState();
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            const wasOpen = document.body.classList.contains('sidebar-open');
            document.body.classList.remove('sidebar-open');
            updateSidebarState();

            if (wasOpen) mobileSidebarToggle?.focus();
        }
    });

    updateSidebarState();
}
