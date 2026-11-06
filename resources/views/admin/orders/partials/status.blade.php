<div class="admin-card p-6">

    <div class="mb-6">

        <h3 class="text-base font-bold text-[var(--admin-text)]">
            وضعیت سفارش
        </h3>

        <p class="mt-1 text-xs text-[var(--admin-muted)]">
            بعد از بررسی پرداخت و آماده‌سازی، مرحله درست را انتخاب کنید. سفارش پرداخت‌نشده را ارسال‌شده یا تحویل‌شده ثبت نکنید.
        </p>

    </div>


    <form
        action="{{ route('admin.orders.status', $order) }}"
        method="POST"
    >

        @csrf
        @method('PATCH')


        <label
            for="status"
            class="admin-label"
        >
            سفارش اکنون در چه مرحله‌ای است؟
        </label>


        <select
            id="status"
            name="status"
            class="admin-select"
            required
        >
            <option
                value="pending"
                @selected(old('status', $order->status) === 'pending')
            >
            در انتظار
            </option>

            <option
                value="processing"
                @selected(old('status', $order->status) === 'processing')
            >
            در حال پردازش
            </option>

            <option
                value="shipped"
                @selected(old('status', $order->status) === 'shipped')
            >
            ارسال شده
            </option>

            <option
                value="delivered"
                @selected(old('status', $order->status) === 'delivered')
            >
            تحویل شده
            </option>

            <option
                value="cancelled"
                @selected(old('status', $order->status) === 'cancelled')
            >
            لغو شده
            </option>
        </select>


        <p class="mt-3 text-xs leading-6 text-[var(--admin-muted)]">در انتظار: تازه ثبت شده · در حال پردازش: در حال آماده‌سازی · ارسال شده: تحویل پست یا پیک · تحویل شده: به مشتری رسیده است.</p>

        @error('status')
        <p class="mt-2 text-xs text-[var(--admin-danger)]">
            {{ $message }}
        </p>
        @enderror


        <button
            type="submit"
            class="admin-btn admin-btn-primary mt-5 w-full"
        >
            ثبت وضعیت جدید
        </button>

    </form>

</div>
