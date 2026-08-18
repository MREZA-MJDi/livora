<footer class="border-t border-[var(--admin-border)] bg-[var(--admin-sidebar)]">

    <div class="flex flex-col items-center justify-between gap-2 px-4 py-5 text-center sm:flex-row sm:px-6 lg:px-8">

        <p class="text-xs text-[var(--admin-muted)]">
            © {{ now()->year }} {{ config('app.name', 'Livora') }}.
            تمامی حقوق محفوظ است.
        </p>

        <p class="text-xs text-[var(--admin-muted)]">
            پنل مدیریت LIVORA
        </p>

    </div>

</footer>
