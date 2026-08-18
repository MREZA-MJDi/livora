<script>
    document.addEventListener('DOMContentLoaded', () => {

        /*
        |--------------------------------------------------------------------------
        | Mobile Sidebar
        |--------------------------------------------------------------------------
        */

        const sidebar = document.getElementById('admin-sidebar');
        const sidebarButton = document.getElementById('admin-mobile-menu-button');
        const sidebarOverlay = document.getElementById('admin-sidebar-overlay');

        const openSidebar = () => {
            if (!sidebar || !sidebarOverlay) {
                return;
            }

            sidebar.classList.remove('translate-x-full');
            sidebarOverlay.classList.remove('hidden');

            if (sidebarButton) {
                sidebarButton.setAttribute('aria-expanded', 'true');
            }

            document.body.classList.add('overflow-hidden');
        };


        const closeSidebar = () => {
            if (!sidebar || !sidebarOverlay) {
                return;
            }

            sidebar.classList.add('translate-x-full');
            sidebarOverlay.classList.add('hidden');

            if (sidebarButton) {
                sidebarButton.setAttribute('aria-expanded', 'false');
            }

            document.body.classList.remove('overflow-hidden');
        };


        if (sidebarButton) {
            sidebarButton.addEventListener('click', (event) => {

                event.stopPropagation();

                if (sidebar.classList.contains('translate-x-full')) {
                    openSidebar();
                } else {
                    closeSidebar();
                }

            });
        }


        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => {
                closeSidebar();
            });
        }


        /*
        |--------------------------------------------------------------------------
        | Close Sidebar After Clicking Navigation Link On Mobile
        |--------------------------------------------------------------------------
        */

        if (sidebar) {

            const sidebarLinks = sidebar.querySelectorAll('a');

            sidebarLinks.forEach((link) => {

                link.addEventListener('click', () => {

                    if (window.innerWidth < 1024) {
                        closeSidebar();
                    }

                });

            });

        }


        /*
        |--------------------------------------------------------------------------
        | Handle Resize
        |--------------------------------------------------------------------------
        */

        window.addEventListener('resize', () => {

            if (window.innerWidth >= 1024) {
                closeSidebar();
            }

        });


        /*
        |--------------------------------------------------------------------------
        | Admin User Dropdown
        |--------------------------------------------------------------------------
        */

        const userButton = document.getElementById('admin-user-menu-button');
        const userMenu = document.getElementById('admin-user-menu');


        const openUserMenu = () => {

            if (!userMenu || !userButton) {
                return;
            }

            userMenu.classList.remove('hidden');

            userButton.setAttribute(
                'aria-expanded',
                'true'
            );

        };


        const closeUserMenu = () => {

            if (!userMenu || !userButton) {
                return;
            }

            userMenu.classList.add('hidden');

            userButton.setAttribute(
                'aria-expanded',
                'false'
            );

        };


        const toggleUserMenu = () => {

            if (!userMenu || !userButton) {
                return;
            }

            if (userMenu.classList.contains('hidden')) {
                openUserMenu();
            } else {
                closeUserMenu();
            }

        };


        if (userButton) {

            userButton.addEventListener('click', (event) => {

                event.stopPropagation();

                toggleUserMenu();

            });

        }


        if (userMenu) {

            userMenu.addEventListener('click', (event) => {
                event.stopPropagation();
            });

        }


        /*
        |--------------------------------------------------------------------------
        | Close Dropdown On Outside Click
        |--------------------------------------------------------------------------
        */

        document.addEventListener('click', () => {
            closeUserMenu();
        });


        /*
        |--------------------------------------------------------------------------
        | Escape Key
        |--------------------------------------------------------------------------
        */

        document.addEventListener('keydown', (event) => {

            if (event.key !== 'Escape') {
                return;
            }

            closeUserMenu();
            closeSidebar();

        });


        /*
        |--------------------------------------------------------------------------
        | Prevent Body Scroll When Mobile Sidebar Is Open
        |--------------------------------------------------------------------------
        */

        const observer = new MutationObserver(() => {

            if (!sidebar) {
                return;
            }

            if (
                window.innerWidth < 1024 &&
                !sidebar.classList.contains('translate-x-full')
            ) {
                document.body.classList.add('overflow-hidden');
            } else {
                document.body.classList.remove('overflow-hidden');
            }

        });


        if (sidebar) {

            observer.observe(sidebar, {
                attributes: true,
                attributeFilter: ['class']
            });

        }


        /*
        |--------------------------------------------------------------------------
        | Initial State
        |--------------------------------------------------------------------------
        */

        if (window.innerWidth < 1024) {
            closeSidebar();
        }

    });
</script>
