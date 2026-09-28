{{-- =========================================================
     GLOBAL ADMIN SCRIPTS
========================================================= --}}

@vite([
'resources/js/app.js'
])


{{-- =========================================================
     FLASH MESSAGE AUTO DISMISS
========================================================= --}}

<script>
    document.addEventListener('DOMContentLoaded', () => {

        document
            .querySelectorAll('[data-auto-dismiss]')
            .forEach((element) => {

                const delay =
                    Number(
                        element.dataset.autoDismiss
                    ) || 4000;

                setTimeout(() => {

                    element.style.opacity = '0';

                    element.style.transform =
                        'translateY(-4px)';

                    element.style.transition =
                        'opacity 250ms ease, transform 250ms ease';

                    setTimeout(() => {
                        element.remove();
                    }, 250);

                }, delay);

            });

    });
</script>


{{-- =========================================================
     CONFIRM ACTIONS
========================================================= --}}

<script>
    document.addEventListener('click', (event) => {

        const element =
            event.target.closest('[data-confirm]');

        if (!element) {
            return;
        }

        const message =
            element.dataset.confirm;

        if (
            message
            && !window.confirm(message)
        ) {
            event.preventDefault();
        }

    });
</script>


{{-- =========================================================
     NUMBER FORMAT
========================================================= --}}

<script>
    window.LivoraAdmin = {

        formatNumber(value) {

            return new Intl.NumberFormat(
                'fa-IR'
            ).format(
                Number(value) || 0
            );

        },

        formatPrice(value) {

            return new Intl.NumberFormat(
                'fa-IR'
            ).format(
                Number(value) || 0
            ) + ' تومان';

        }

    };
</script>


{{-- =========================================================
     ESCAPE MODALS / DROPDOWNS
========================================================= --}}

<script>
    document.addEventListener('keydown', (event) => {

        if (event.key !== 'Escape') {
            return;
        }

        window.dispatchEvent(
            new CustomEvent('admin:escape')
        );

    });
</script>


{{-- =========================================================
     SHARED IMAGE PREVIEW
========================================================= --}}

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document
            .querySelectorAll('[data-image-preview-target]')
            .forEach((input) => {
                const targetId = input.dataset.imagePreviewTarget;
                const placeholderId = input.dataset.imagePreviewPlaceholder;

                const preview = targetId
                    ? document.getElementById(targetId)
                    : null;

                const placeholder = placeholderId
                    ? document.getElementById(placeholderId)
                    : null;

                if (!preview) {
                    return;
                }

                input.addEventListener('change', () => {
                    const file = input.files?.[0];

                    if (!file || !file.type.startsWith('image/')) {
                        return;
                    }

                    const objectUrl = URL.createObjectURL(file);

                    preview.src = objectUrl;
                    preview.classList.remove('hidden');

                    if (placeholder) {
                        placeholder.classList.add('hidden');
                        placeholder.classList.remove('flex');
                    }

                    preview.onload = () => URL.revokeObjectURL(objectUrl);
                });
            });
    });
</script>

{{-- =========================================================
     PAGE SPECIFIC SCRIPTS
========================================================= --}}

