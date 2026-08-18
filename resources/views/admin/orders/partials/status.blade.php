<div class="admin-card p-6">

    <div class="mb-6">

        <h3 class="text-base font-bold text-[var(--admin-text)]">
            وضعیت سفارش
        </h3>

        <p class="mt-1 text-xs text-[var(--admin-muted)]">
            وضعیت سفارش را از اینجا بروزرسانی کنید.
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
            وضعیت جدید
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


        @error('status')
        <p class="mt-2 text-xs text-[var(--admin-danger)]">
            {{ $message }}
        </p>
        @enderror


        <button
            type="submit"
            class="admin-btn admin-btn-primary mt-5 w-full"
        >
            بروزرسانی وضعیت
        </button>

    </form>

</div>
