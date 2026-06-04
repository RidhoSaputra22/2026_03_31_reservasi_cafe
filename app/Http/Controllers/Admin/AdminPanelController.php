<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\ReservationStatus;
use App\Enums\TableStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\CafeProfile;
use App\Models\CafeTable;
use App\Models\MenuItem;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\ReservationPackage;
use App\Models\ReservationSlot;
use App\Models\User;
use App\Services\CafeReservation\CafePaymentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class AdminPanelController extends Controller
{
    public function dashboard(): View
    {
        $today = now()->toDateString();

        $stats = [
            'reservations_today' => Reservation::query()->whereDate('reservation_date', $today)->count(),
            'awaiting_confirmation' => Reservation::query()->where('status', ReservationStatus::AwaitingConfirmation)->count(),
            'active_tables' => CafeTable::query()->where('is_active', true)->count(),
            'revenue_paid' => Payment::query()->where('status', PaymentStatus::Paid)->sum('amount'),
            'menu_available' => MenuItem::query()->where('is_available', true)->count(),
            'pending_payments' => Payment::query()->whereIn('status', [
                PaymentStatus::Pending,
                PaymentStatus::AwaitingVerification,
            ])->count(),
        ];

        $todayReservations = Reservation::query()
            ->with(['cafeTable', 'payments'])
            ->whereDate('reservation_date', $today)
            ->orderBy('start_time')
            ->limit(6)
            ->get();

        $upcomingReservations = Reservation::query()
            ->with(['cafeTable', 'payments'])
            ->whereDate('reservation_date', '>=', $today)
            ->whereNotIn('status', [ReservationStatus::Cancelled, ReservationStatus::Completed])
            ->orderBy('reservation_date')
            ->orderBy('start_time')
            ->limit(8)
            ->get();

        $recentPayments = Payment::query()
            ->with('reservation')
            ->latest()
            ->limit(5)
            ->get();

        $tableStatusCounts = CafeTable::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $calendarEvents = Reservation::query()
            ->with('cafeTable')
            ->whereDate('reservation_date', '>=', now()->subDays(10)->toDateString())
            ->whereDate('reservation_date', '<=', now()->addDays(45)->toDateString())
            ->get()
            ->map(fn (Reservation $reservation): array => [
                'id' => $reservation->id,
                'title' => $reservation->customer_name.' - '.$reservation->reservation_code,
                'start' => $reservation->reservation_date?->format('Y-m-d').'T'.Str::substr($reservation->start_time, 0, 8),
                'end' => $reservation->reservation_date?->format('Y-m-d').'T'.Str::substr($reservation->end_time ?: $reservation->start_time, 0, 8),
                'color' => $this->reservationColor($reservation->status),
                'extendedProps' => [
                    'type' => 'custom',
                    'eventId' => $reservation->id,
                    'location' => $reservation->cafeTable?->name ?? 'Belum ada meja',
                    'description' => $reservation->status->label().' - '.$reservation->guest_count.' tamu',
                    'color' => $this->reservationColor($reservation->status),
                ],
            ])
            ->values();

        return view('admin.dashboard', [
            'stats' => $stats,
            'todayReservations' => $todayReservations,
            'upcomingReservations' => $upcomingReservations,
            'recentPayments' => $recentPayments,
            'tableStatusCounts' => $tableStatusCounts,
            'calendarEvents' => $calendarEvents,
            'panels' => $this->managementPanels(),
        ]);
    }

    public function reservations(Request $request): View
    {
        $query = Reservation::query()->with(['user', 'cafeTable', 'reservationSlot', 'payments']);

        $this->applySearch($query, $request->string('search')->toString(), [
            'reservation_code',
            'customer_name',
            'customer_phone',
            'notes',
        ]);

        $query
            ->when($request->filled('status'), fn (Builder $builder) => $builder->where('status', $request->string('status')))
            ->when($request->filled('date'), fn (Builder $builder) => $builder->whereDate('reservation_date', $request->date('date')));

        $this->applySort($query, ['reservation_date', 'start_time', 'guest_count', 'amount_due', 'status'], 'reservation_date', 'asc');

        return view('admin.reservations', [
            'reservations' => $query->paginate(10),
            'statusOptions' => $this->enumOptions(ReservationStatus::cases()),
            'tableOptions' => CafeTable::query()
                ->orderBy('name')
                ->get()
                ->map(fn (CafeTable $table): array => [
                    'value' => $table->id,
                    'label' => trim($table->code.' · '.$table->name.($table->location ? ' ('.$table->location.')' : '')),
                ])
                ->values()
                ->all(),
        ]);
    }

    public function updateReservationStatus(Request $request, Reservation $reservation): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in($this->enumValues(ReservationStatus::cases()))],
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $status = ReservationStatus::from($validated['status']);
        $attributes = [
            'status' => $status,
        ];

        match ($status) {
            ReservationStatus::Confirmed => $attributes['confirmed_at'] = $reservation->confirmed_at ?? now(),
            ReservationStatus::CheckedIn => $attributes['checked_in_at'] = $reservation->checked_in_at ?? now(),
            ReservationStatus::Completed => $attributes['completed_at'] = $reservation->completed_at ?? now(),
            ReservationStatus::Cancelled => $attributes = array_merge($attributes, [
                'cancelled_at' => $reservation->cancelled_at ?? now(),
                'cancellation_reason' => $validated['cancellation_reason'] ?? $reservation->cancellation_reason,
            ]),
            default => null,
        };

        $reservation->forceFill($attributes)->save();
        $this->refreshTableStatus($reservation->cafeTable()->first());

        return back()->with('success', 'Status reservasi berhasil diperbarui.');
    }

    public function destroyReservation(Reservation $reservation): RedirectResponse
    {
        $table = $reservation->cafeTable()->first();
        $reservation->delete();
        $this->refreshTableStatus($table);

        return back()->with('success', 'Reservasi berhasil dihapus.');
    }

    public function menu(Request $request): View
    {
        $query = MenuItem::query()->with('cafeProfile');
        $this->applySearch($query, $request->string('search')->toString(), ['name', 'category', 'description']);
        $this->applySort($query, ['name', 'category', 'price', 'is_available'], 'name');

        $editingMenuItem = $request->filled('edit')
            ? MenuItem::query()->find($request->integer('edit'))
            : null;

        return view('admin.menu', [
            'menuItems' => $query->paginate(10),
            'editingMenuItem' => $editingMenuItem,
            'profile' => $this->ensureProfile(),
            'categories' => MenuItem::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function storeMenu(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        MenuItem::query()->create([
            ...$validated,
            'cafe_profile_id' => $this->ensureProfile()->id,
            'is_available' => $request->boolean('is_available', true),
        ]);

        return back()->with('success', 'Menu baru berhasil ditambahkan.');
    }

    public function updateMenu(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $menuItem->update([
            ...$validated,
            'is_available' => $request->boolean('is_available'),
        ]);

        return redirect()
            ->route('admin.menu.index')
            ->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroyMenu(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->delete();

        return back()->with('success', 'Menu berhasil dihapus.');
    }

    public function packages(Request $request): View
    {
        $query = ReservationPackage::query();
        $this->applySearch($query, $request->string('search')->toString(), ['name', 'category', 'summary', 'slug']);
        $query->when(
            $request->filled('status'),
            fn (Builder $builder) => $builder->where('is_active', $request->string('status')->toString() === 'active'),
        );
        $this->applySort($query, ['name', 'category', 'base_price', 'included_hours', 'extra_hour_price', 'sort_order'], 'sort_order');

        $editingPackage = $request->filled('edit')
            ? ReservationPackage::query()->find($request->integer('edit'))
            : null;

        return view('admin.packages', [
            'packages' => $query->get(),
            'editingPackage' => $editingPackage,
            'categoryOptions' => ReservationPackage::query()
                ->whereNotNull('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category')
                ->map(fn (string $category): array => ['value' => $category, 'label' => $category])
                ->values()
                ->all(),
            'statusOptions' => [
                ['value' => 'active', 'label' => 'Aktif'],
                ['value' => 'inactive', 'label' => 'Nonaktif'],
            ],
            'activeCount' => ReservationPackage::query()->where('is_active', true)->count(),
            'featuredCount' => ReservationPackage::query()->where('is_featured', true)->count(),
        ]);
    }

    public function storePackage(Request $request): RedirectResponse
    {
        $validated = $this->validatePackage($request);

        ReservationPackage::query()->create($validated);

        return redirect()
            ->route('admin.packages.index')
            ->with('success', 'Paket reservasi baru berhasil ditambahkan.');
    }

    public function updatePackage(Request $request, ReservationPackage $reservationPackage): RedirectResponse
    {
        $validated = $this->validatePackage($request, $reservationPackage);

        $reservationPackage->update($validated);

        return redirect()
            ->route('admin.packages.index')
            ->with('success', 'Paket reservasi berhasil diperbarui.');
    }

    public function destroyPackage(ReservationPackage $reservationPackage): RedirectResponse
    {
        if ($reservationPackage->reservations()->exists()) {
            $reservationPackage->update(['is_active' => false]);

            return back()->with('warning', 'Paket sudah punya riwayat reservasi, jadi dinonaktifkan alih-alih dihapus.');
        }

        $reservationPackage->delete();

        return back()->with('success', 'Paket reservasi berhasil dihapus.');
    }

    public function tables(Request $request): View
    {
        $query = CafeTable::query();
        $this->applySearch($query, $request->string('search')->toString(), ['code', 'name', 'location', 'description']);
        $query->when($request->filled('status'), fn (Builder $builder) => $builder->where('status', $request->string('status')));
        $this->applySort($query, ['code', 'name', 'capacity', 'status', 'location', 'is_active'], 'code');

        $editingTable = $request->filled('edit')
            ? CafeTable::query()->find($request->integer('edit'))
            : null;

        return view('admin.tables', [
            'tables' => $query->paginate(10),
            'editingTable' => $editingTable,
            'statusOptions' => $this->enumOptions(TableStatus::cases()),
        ]);
    }

    public function storeTable(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:cafe_tables,code'],
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', Rule::in($this->enumValues(TableStatus::cases()))],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        CafeTable::query()->create([
            ...$validated,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Meja baru berhasil ditambahkan.');
    }

    public function updateTable(Request $request, CafeTable $cafeTable): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('cafe_tables', 'code')->ignore($cafeTable)],
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', Rule::in($this->enumValues(TableStatus::cases()))],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $cafeTable->update([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.tables.index')
            ->with('success', 'Meja berhasil diperbarui.');
    }

    public function destroyTable(CafeTable $cafeTable): RedirectResponse
    {
        if ($cafeTable->reservations()->exists()) {
            $cafeTable->update(['is_active' => false]);

            return back()->with('warning', 'Meja memiliki riwayat reservasi, jadi dinonaktifkan alih-alih dihapus.');
        }

        $cafeTable->delete();

        return back()->with('success', 'Meja berhasil dihapus.');
    }

    public function slots(Request $request): View
    {
        $query = ReservationSlot::query();
        $this->applySearch($query, $request->string('search')->toString(), ['name']);
        $query->when($request->filled('day_of_week'), fn (Builder $builder) => $builder->where('day_of_week', $request->integer('day_of_week')));
        $this->applySort($query, ['day_of_week', 'start_time', 'end_time', 'is_active'], 'day_of_week');

        $editingSlot = $request->filled('edit')
            ? ReservationSlot::query()->find($request->integer('edit'))
            : null;

        return view('admin.slots', [
            'slots' => $query->paginate(10),
            'editingSlot' => $editingSlot,
            'dayOptions' => $this->dayOptions(),
        ]);
    }

    public function storeSlot(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ReservationSlot::query()->create([
            ...$validated,
            'start_time' => $validated['start_time'].':00',
            'end_time' => $validated['end_time'].':00',
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Rentang jam reservasi baru berhasil ditambahkan.');
    }

    public function updateSlot(Request $request, ReservationSlot $reservationSlot): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $reservationSlot->update([
            ...$validated,
            'start_time' => $validated['start_time'].':00',
            'end_time' => $validated['end_time'].':00',
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.slots.index')
            ->with('success', 'Rentang jam reservasi berhasil diperbarui.');
    }

    public function destroySlot(ReservationSlot $reservationSlot): RedirectResponse
    {
        if ($reservationSlot->reservations()->exists()) {
            $reservationSlot->update(['is_active' => false]);

            return back()->with('warning', 'Rentang jam memiliki riwayat reservasi, jadi dinonaktifkan alih-alih dihapus.');
        }

        $reservationSlot->delete();

        return back()->with('success', 'Rentang jam reservasi berhasil dihapus.');
    }

    public function payments(
        Request $request,
        CafePaymentService $paymentService,
    ): View|RedirectResponse
    {
        $midtransOrderId = trim($request->string('midtrans_order_id')->toString());

        if ($midtransOrderId !== '') {
            try {
                $payment = $paymentService->syncPaymentFromMidtransOrderId($midtransOrderId);
            } catch (RuntimeException $exception) {
                return redirect()
                    ->route('admin.payments.index', collect($request->query())->except('midtrans_order_id')->all())
                    ->with('error', $exception->getMessage());
            }

            [$flashType, $flashMessage] = match ($payment->status) {
                PaymentStatus::Paid => ['success', 'Pembayaran sisa berhasil dikonfirmasi.'],
                PaymentStatus::AwaitingVerification => ['info', 'Pembayaran sisa sudah diterima dan sedang menunggu verifikasi.'],
                PaymentStatus::Failed => ['error', 'Pembayaran sisa belum berhasil. Kamu bisa buka lagi atau buat ulang transaksi Midtrans.'],
                PaymentStatus::Refunded => ['warning', 'Pembayaran sisa ini telah direfund.'],
                default => ['info', 'Pembayaran sisa masih diproses Midtrans.'],
            };

            return redirect()
                ->route('admin.payments.index', collect($request->query())->except('midtrans_order_id')->all())
                ->with($flashType, $flashMessage);
        }

        $query = Payment::query()->with(['reservation.payments', 'verifiedBy', 'parentPayment']);
        $this->applyPaymentSearch($query, $request->string('search')->toString());
        $query->when($request->filled('status'), fn (Builder $builder) => $builder->where('status', $request->string('status')));
        $this->applySort($query, ['payment_code', 'amount', 'method', 'status', 'paid_at', 'verified_at'], 'created_at', 'desc');

        return view('admin.payments', [
            'payments' => $query->paginate(10),
            'statusOptions' => $this->enumOptions(PaymentStatus::cases()),
            'methodOptions' => $this->enumOptions(PaymentMethod::cases()),
            'typeOptions' => $this->enumOptions(PaymentType::cases()),
            'midtransConfigured' => app(CafePaymentService::class)->isMidtransConfigured(),
            'midtransClientKey' => config('services.midtrans.client_key'),
            'midtransSnapJsUrl' => config('services.midtrans.is_production', false)
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js',
            'dateFieldOptions' => [
                ['value' => 'paid_at', 'label' => 'Tanggal dibayar'],
                ['value' => 'verified_at', 'label' => 'Tanggal diverifikasi'],
                ['value' => 'created_at', 'label' => 'Tanggal dibuat'],
            ],
        ]);
    }

    public function createSettlementPayment(
        Request $request,
        Reservation $reservation,
        CafePaymentService $paymentService,
    ): RedirectResponse {
        $validated = $request->validate([
            'method' => ['nullable', Rule::in([
                PaymentMethod::BankTransfer->value,
                PaymentMethod::Qris->value,
                PaymentMethod::Card->value,
            ])],
        ]);

        try {
            $payment = $paymentService->createSettlementPaymentForReservation($reservation, [
                'method' => $validated['method'] ?? PaymentMethod::Qris->value,
            ]);
        } catch (RuntimeException $exception) {
            return back()->with('warning', $exception->getMessage());
        }

        return back()->with(
            'success',
            'Pembayaran sisa berhasil dibuat via Midtrans sebesar Rp '
                .number_format((float) $payment->amount, 0, ',', '.')
                .'. Popup Midtrans akan dibuka dari panel admin.',
        )
            ->with('admin_payment_snap_token', $payment->snap_token)
            ->with('admin_payment_order_id', $payment->midtrans_order_id ?: $payment->transaction_reference)
            ->with('admin_payment_id', $payment->id);
    }

    public function updatePaymentStatus(
        Request $request,
        Payment $payment,
        CafePaymentService $paymentService,
    ): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in($this->enumValues(PaymentStatus::cases()))],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $status = PaymentStatus::from($validated['status']);
        $payment->forceFill([
            'status' => $status,
            'notes' => $validated['notes'] ?? $payment->notes,
            'paid_at' => in_array($status, [PaymentStatus::AwaitingVerification, PaymentStatus::Paid, PaymentStatus::Refunded], true)
                ? ($payment->paid_at ?? now())
                : null,
            'verified_at' => $status === PaymentStatus::Paid ? ($payment->verified_at ?? now()) : null,
        ])->save();

        $reservation = $payment->reservation;

        if ($reservation instanceof Reservation) {
            $paymentService->applyReservationPaymentStatus($reservation);
        }

        return back()->with('success', 'Status pembayaran berhasil diperbarui.');
    }

    public function destroyPayment(Payment $payment): RedirectResponse
    {
        $payment->delete();

        return back()->with('success', 'Pembayaran berhasil dihapus.');
    }

    public function profile(): View
    {
        return view('admin.profile', [
            'profile' => $this->ensureProfile(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1500'],
            'address' => ['required', 'string', 'max:1000'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'opening_time' => ['required', 'date_format:H:i'],
            'closing_time' => ['required', 'date_format:H:i', 'after:opening_time'],
            'facilities' => ['nullable', 'string', 'max:1000'],
            'reservation_rules' => ['nullable', 'string', 'max:1500'],
            'down_payment_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $this->ensureProfile()->update([
            ...$validated,
            'opening_time' => $validated['opening_time'].':00',
            'closing_time' => $validated['closing_time'].':00',
            'facilities' => collect(preg_split('/\r\n|\r|\n/', $validated['facilities'] ?? ''))
                ->map(fn (string $facility): string => trim($facility))
                ->filter()
                ->values()
                ->all(),
        ]);

        return back()->with('success', 'Profil cafe berhasil diperbarui.');
    }

    public function users(Request $request): View
    {
        $query = User::query();
        $this->applySearch($query, $request->string('search')->toString(), ['name', 'username', 'email', 'phone_number']);
        $query->when($request->filled('role'), fn (Builder $builder) => $builder->where('role', $request->string('role')));
        $this->applySort($query, ['name', 'email', 'role'], 'name');

        $editingUser = $request->filled('edit')
            ? User::query()->find($request->integer('edit'))
            : null;

        return view('admin.users', [
            'users' => $query->paginate(10),
            'editingUser' => $editingUser,
            'roleOptions' => $this->enumOptions(UserRole::cases()),
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:30', 'unique:users,phone_number'],
            'role' => ['required', Rule::in($this->enumValues(UserRole::cases()))],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        User::query()->create([
            ...$validated,
            'password' => Hash::make($validated['password'] ?? 'password'),
        ]);

        return back()->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'phone_number' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone_number')->ignore($user)],
            'role' => ['required', Rule::in($this->enumValues(UserRole::cases()))],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if (! filled($validated['password'] ?? null)) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroyUser(User $user): RedirectResponse
    {
        if ($user->reservations()->exists()) {
            return back()->with('warning', 'Pengguna ini memiliki riwayat reservasi sehingga tidak dapat dihapus.');
        }

        $user->delete();

        return back()->with('success', 'Pengguna berhasil dihapus.');
    }

    public function globalSearch(Request $request): JsonResponse
    {
        $query = trim($request->string('q')->toString());

        if (Str::length($query) < 2) {
            return response()->json(['results' => []]);
        }

        $reservations = Reservation::query()
            ->where('reservation_code', 'like', "%{$query}%")
            ->orWhere('customer_name', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn (Reservation $reservation): array => [
                'title' => $reservation->reservation_code.' - '.$reservation->customer_name,
                'subtitle' => $reservation->reservation_date?->format('d M Y').' '.$reservation->start_time,
                'category' => 'Reservasi',
                'icon' => 'calendar',
                'url' => route('admin.reservations.index', ['search' => $reservation->reservation_code]),
            ]);

        $menu = MenuItem::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('category', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn (MenuItem $item): array => [
                'title' => $item->name,
                'subtitle' => ($item->category ?: 'Menu').' - Rp '.number_format((float) $item->price, 0, ',', '.'),
                'category' => 'Menu',
                'icon' => 'menu',
                'url' => route('admin.menu.index', ['search' => $item->name]),
            ]);

        $tables = CafeTable::query()
            ->where('code', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")
            ->orWhere('location', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn (CafeTable $table): array => [
                'title' => $table->code.' - '.$table->name,
                'subtitle' => $table->capacity.' kursi, '.($table->location ?: 'Tanpa lokasi'),
                'category' => 'Meja',
                'icon' => 'dashboard',
                'url' => route('admin.tables.index', ['search' => $table->code]),
            ]);

        $packages = ReservationPackage::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('category', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn (ReservationPackage $package): array => [
                'title' => $package->name,
                'subtitle' => ($package->category ?: 'Paket').' - Rp '.number_format((float) $package->base_price, 0, ',', '.'),
                'category' => 'Paket Reservasi',
                'icon' => 'store',
                'url' => route('admin.packages.index', ['search' => $package->name]),
            ]);

        $payments = Payment::query()
            ->where('payment_code', 'like', "%{$query}%")
            ->orWhere('transaction_reference', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn (Payment $payment): array => [
                'title' => $payment->payment_code,
                'subtitle' => $payment->status->label().' - Rp '.number_format((float) $payment->amount, 0, ',', '.'),
                'category' => 'Pembayaran',
                'icon' => 'payment',
                'url' => route('admin.payments.index', ['search' => $payment->payment_code]),
            ]);

        return response()->json([
            'results' => $reservations
                ->concat($menu)
                ->concat($tables)
                ->concat($packages)
                ->concat($payments)
                ->take(12)
                ->values(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatePackage(Request $request, ?ReservationPackage $reservationPackage = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('reservation_packages', 'slug')->ignore($reservationPackage),
            ],
            'category' => ['required', 'string', 'max:255'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'summary' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string', 'max:2000'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'included_hours' => ['required', 'integer', 'min:1', 'max:24'],
            'extra_hour_price' => ['required', 'numeric', 'min:0'],
            'aliases_text' => ['nullable', 'string', 'max:1000'],
            'facilities_text' => ['nullable', 'string', 'max:2000'],
            'notes_text' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'slug' => $this->resolvePackageSlug(
                $validated['slug'] ?? null,
                $validated['name'],
                $reservationPackage,
            ),
            'name' => trim($validated['name']),
            'category' => trim($validated['category']),
            'image_path' => filled($validated['image_path'] ?? null)
                ? trim((string) $validated['image_path'])
                : null,
            'summary' => trim($validated['summary']),
            'description' => trim($validated['description']),
            'base_price' => $validated['base_price'],
            'included_hours' => (int) $validated['included_hours'],
            'extra_hour_price' => $validated['extra_hour_price'],
            'aliases' => $this->parsePackageList($validated['aliases_text'] ?? null),
            'facilities' => $this->parsePackageList($validated['facilities_text'] ?? null),
            'notes' => $this->parsePackageList($validated['notes_text'] ?? null),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    protected function ensureProfile(): CafeProfile
    {
        return CafeProfile::query()->firstOrCreate(
            ['name' => 'Cafe Amikospace'],
            [
                'description' => 'Cafe modern untuk nongkrong, meeting, dan reservasi meja terjadwal.',
                'address' => 'Jl. Perintis Kemerdekaan No. 88, Makassar',
                'phone_number' => '0411-889900',
                'opening_time' => '08:00:00',
                'closing_time' => '17:00:00',
                'facilities' => ['WiFi Cepat', 'AC', 'Stop Kontak'],
                'reservation_rules' => 'DP minimal mengikuti nominal yang aktif di panel admin.',
                'down_payment_amount' => 50000,
            ],
        );
    }

    protected function applySearch(Builder $query, ?string $keyword, array $columns): void
    {
        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return;
        }

        $query->where(function (Builder $builder) use ($columns, $keyword): void {
            foreach ($columns as $column) {
                $builder->orWhere($column, 'like', "%{$keyword}%");
            }
        });
    }

    protected function applyPaymentSearch(Builder $query, ?string $keyword): void
    {
        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return;
        }

        $query->where(function (Builder $builder) use ($keyword): void {
            $builder
                ->where('payment_code', 'like', "%{$keyword}%")
                ->orWhere('transaction_reference', 'like', "%{$keyword}%")
                ->orWhere('midtrans_order_id', 'like', "%{$keyword}%")
                ->orWhere('notes', 'like', "%{$keyword}%")
                ->orWhereHas('reservation', function (Builder $reservationQuery) use ($keyword): void {
                    $reservationQuery
                        ->where('reservation_code', 'like', "%{$keyword}%")
                        ->orWhere('customer_name', 'like', "%{$keyword}%")
                        ->orWhere('customer_phone', 'like', "%{$keyword}%")
                        ->orWhere('package_name', 'like', "%{$keyword}%");
                });
        });
    }

    protected function applySort(Builder $query, array $allowedColumns, string $defaultColumn, string $defaultDirection = 'asc'): void
    {
        $sort = request('sort', $defaultColumn);
        $direction = request('direction', $defaultDirection) === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, $allowedColumns, true)) {
            $sort = $defaultColumn;
        }

        $query->orderBy($sort, $direction);
    }

    /**
     * @param array<int, \BackedEnum> $cases
     * @return array<int, string>
     */
    protected function enumValues(array $cases): array
    {
        return collect($cases)->map(fn ($case) => $case->value)->all();
    }

    /**
     * @param array<int, \BackedEnum> $cases
     * @return array<int, array{value: string, label: string}>
     */
    protected function enumOptions(array $cases): array
    {
        return collect($cases)
            ->map(fn ($case): array => [
                'value' => $case->value,
                'label' => method_exists($case, 'label') ? $case->label() : Str::title($case->name),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    protected function dayOptions(): array
    {
        return [
            ['value' => 0, 'label' => 'Minggu'],
            ['value' => 1, 'label' => 'Senin'],
            ['value' => 2, 'label' => 'Selasa'],
            ['value' => 3, 'label' => 'Rabu'],
            ['value' => 4, 'label' => 'Kamis'],
            ['value' => 5, 'label' => 'Jumat'],
            ['value' => 6, 'label' => 'Sabtu'],
        ];
    }

    protected function dayLabel(int $day): string
    {
        return collect($this->dayOptions())->firstWhere('value', $day)['label'] ?? 'Hari '.$day;
    }

    protected function refreshTableStatus(?CafeTable $table): void
    {
        if (! $table instanceof CafeTable) {
            return;
        }

        $activeReservation = Reservation::query()
            ->where('cafe_table_id', $table->id)
            ->whereNotIn('status', [ReservationStatus::Cancelled, ReservationStatus::Completed])
            ->orderByRaw("CASE WHEN status = ? THEN 0 WHEN status = ? THEN 1 ELSE 2 END", [
                ReservationStatus::CheckedIn->value,
                ReservationStatus::Confirmed->value,
            ])
            ->first();

        $table->forceFill([
            'status' => match ($activeReservation?->status) {
                ReservationStatus::CheckedIn => TableStatus::Occupied,
                ReservationStatus::Confirmed, ReservationStatus::AwaitingConfirmation, ReservationStatus::PendingPayment => TableStatus::Reserved,
                default => TableStatus::Available,
            },
        ])->save();
    }

    protected function reservationColor(ReservationStatus $status): string
    {
        return match ($status) {
            ReservationStatus::PendingPayment => 'warning',
            ReservationStatus::AwaitingConfirmation => 'info',
            ReservationStatus::Confirmed => 'primary',
            ReservationStatus::CheckedIn => 'success',
            ReservationStatus::Completed => 'neutral',
            ReservationStatus::Cancelled => 'error',
        };
    }

    protected function managementPanels(): array
    {
        return [
            [
                'title' => 'Reservasi',
                'description' => 'Konfirmasi, check-in, pembatalan, dan monitoring booking pelanggan.',
                'href' => route('admin.reservations.index'),
                'icon' => 'calendar',
                'tone' => 'primary',
            ],
            [
                'title' => 'Menu Cafe',
                'description' => 'Tambah menu, atur kategori, harga, dan ketersediaan.',
                'href' => route('admin.menu.index'),
                'icon' => 'menu',
                'tone' => 'success',
            ],
            [
                'title' => 'Meja & Area',
                'description' => 'Kelola kode meja, kapasitas, lokasi, dan status operasional.',
                'href' => route('admin.tables.index'),
                'icon' => 'table',
                'tone' => 'accent',
            ],
            [
                'title' => 'Paket Reservasi',
                'description' => 'Susun paket sendiri, harga dasar, jam termasuk, dan tarif tambah per jam.',
                'href' => route('admin.packages.index'),
                'icon' => 'store',
                'tone' => 'secondary',
            ],
            [
                'title' => 'Slot Reservasi',
                'description' => 'Atur rentang jam operasional tempat reservasi berjalan setiap hari.',
                'href' => route('admin.slots.index'),
                'icon' => 'clock',
                'tone' => 'info',
            ],
            [
                'title' => 'Pembayaran',
                'description' => 'Validasi DP, status transfer, QRIS, tunai, dan catatan transaksi.',
                'href' => route('admin.payments.index'),
                'icon' => 'payment',
                'tone' => 'warning',
            ],
            [
                'title' => 'Profil Cafe',
                'description' => 'Ubah alamat, jam buka, fasilitas, aturan, dan nominal DP.',
                'href' => route('admin.profile.index'),
                'icon' => 'store',
                'tone' => 'secondary',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function parsePackageList(?string $value): array
    {
        return collect(preg_split('/[\r\n,]+/', (string) $value))
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    protected function resolvePackageSlug(
        ?string $requestedSlug,
        string $name,
        ?ReservationPackage $reservationPackage = null,
    ): string {
        $baseSlug = filled($requestedSlug)
            ? Str::slug((string) $requestedSlug)
            : ($reservationPackage?->slug ?: Str::slug($name));

        if ($baseSlug === '') {
            $baseSlug = 'paket-reservasi';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            ReservationPackage::query()
                ->when(
                    $reservationPackage instanceof ReservationPackage,
                    fn (Builder $builder) => $builder->whereKeyNot($reservationPackage->id),
                )
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
