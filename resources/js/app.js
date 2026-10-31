import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.richTextEditor = (uploadUrl) => ({
    wordCount: 0,
    message: '',
    init() {
        this.sync();
        this.$refs.editor.addEventListener('blur', () => this.sync());
        this.$root.closest('form')?.addEventListener('submit', () => this.sync());
    },
    sync() {
        this.$refs.output.value = this.$refs.editor.innerHTML.trim();
        const text = this.$refs.editor.innerText.trim();
        this.wordCount = text ? text.split(/\s+/u).filter(Boolean).length : 0;
    },
    command(name, value = null) {
        this.$refs.editor.focus();
        document.execCommand(name, false, value);
        this.sync();
    },
    addLink() {
        const url = window.prompt('آدرس لینک را وارد کنید');
        if (!url) return;
        try {
            const parsed = new URL(url, window.location.origin);
            if (!['http:', 'https:'].includes(parsed.protocol) && !url.startsWith('/')) throw new Error();
            this.command('createLink', url);
        } catch (_) {
            this.message = 'آدرس لینک معتبر نیست.';
        }
    },
    onKeydown(event) {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {
            event.preventDefault();
            this.$root.closest('form')?.requestSubmit();
        }
    },
    pastePlain(event) {
        event.preventDefault();
        document.execCommand('insertText', false, event.clipboardData?.getData('text/plain') || '');
        this.sync();
    },
    async uploadImage(event) {
        const file = event.target.files?.[0];
        event.target.value = '';
        if (!file) return;
        if (!/^image\/(jpeg|png|webp)$/i.test(file.type) || file.size > 10 * 1024 * 1024) {
            this.message = 'فقط تصویر JPG، PNG یا WEBP تا ۱۰ مگابایت مجاز است.';
            return;
        }
        const data = new FormData();
        data.append('image', file);
        this.message = 'در حال بارگذاری تصویر…';
        try {
            const response = await fetch(uploadUrl, {
                method: 'POST',
                body: data,
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                },
            });
            const result = await response.json();
            if (!response.ok || !result.url) throw new Error(result.message || 'بارگذاری تصویر انجام نشد.');
            this.$refs.editor.focus();
            const safeUrl = result.url.replace(/&/g, '&amp;').replace(/"/g, '&quot;');
            document.execCommand('insertHTML', false, '<figure><img src="' + safeUrl + '" alt="" loading="lazy" decoding="async"><figcaption>توضیح تصویر</figcaption></figure><p></p>');
            this.message = 'تصویر اضافه شد.';
            this.sync();
        } catch (error) {
            this.message = error.message || 'خطا در بارگذاری تصویر.';
        }
    },
});

Alpine.start();

if (document.querySelector('[data-sg-intro-track]')) {
    import('./home-intro.js').catch(() => {
        document.querySelector('[data-sg-intro-track]')?.remove();
        window.dispatchEvent(new Event('sg:intro:complete'));
    });
}

