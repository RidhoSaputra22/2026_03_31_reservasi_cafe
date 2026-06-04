@extends('admin.layouts.app', ['title' => 'Panel Pembayaran', 'breadcrumbs' => [['label' => 'Pembayaran']]])

@php
    $paymentStatusFilterOptions = collect([['value' => '', 'label' => 'Semua status']])
        ->concat($statusOptions)
        ->all();
    $paymentMethodFilterOptions = collect([['value' => '', 'label' => 'Semua metode']])
        ->concat($methodOptions)
        ->all();
    $paymentTypeFilterOptions = collect([['value' => '', 'label' => 'Semua jenis']])
        ->concat($typeOptions)
        ->all();
    $sessionAdminPaymentSnapToken = session('admin_payment_snap_token');
    $sessionAdminPaymentOrderId = session('admin_payment_order_id');
    $sessionAdminPaymentId = session('admin_payment_id');
    $settlementReservations = collect($payments->items())
        ->map(fn ($payment) => $payment->reservation)
        ->filter()
        ->unique('id')
        ->filter(fn ($reservation) => $midtransConfigured && $reservation->canCreateSettlementPayment())
        ->values();
    $adminPendingSnapPayments = collect($payments->items())
        ->filter(fn ($payment) => $payment->canBeOpenedInAdmin())
        ->values();
    $hasAdminSnapPayment = filled($sessionAdminPaymentSnapToken)
        || ($midtransConfigured && $adminPendingSnapPayments->isNotEmpty());
@endphp

@section('header')
    <x-layouts.page-header title="Panel Pembayaran" description="Validasi DP, metode pembayaran, referensi transaksi, dan status reservasi terkait.">
        <x-slot:actions>
            <x-ui.button
                type="primary"
                size="sm"
                :isSubmit="false"
                data-payment-qr-open
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 7V5a1 1 0 011-1h2m10 0h2a1 1 0 011 1v2M4 17v2a1 1 0 001 1h2m10 0h2a1 1 0 001-1v-2M8 12h8" />
                </svg>
                Cari Pembayaran
            </x-ui.button>
            <x-ui.button type="secondary" size="sm" :isSubmit="false"
                onclick="document.getElementById('export-payments-pdf-modal').showModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 16V4m0 12-4-4m4 4 4-4M4 20h16" />
                </svg>
                Export PDF
            </x-ui.button>
            <x-ui.button :href="route('admin.reservations.index')" type="ghost" size="sm" :isSubmit="false">Buka Reservasi</x-ui.button>
        </x-slot:actions>
    </x-layouts.page-header>
@endsection

@section('content')
    <div class="space-y-6">




        <x-ui.card>
            <x-ui.data-table
                title="Daftar Pembayaran"
                :data="$payments"
                :only="['payment_code', 'type', 'amount', 'method', 'status', 'paid_at', 'verified_at']"
                :labels="['payment_code' => 'Kode', 'paid_at' => 'Dibayar', 'verified_at' => 'Diverifikasi']"
                :formats="['type' => 'badge', 'amount' => 'money', 'method' => 'badge', 'status' => 'badge', 'paid_at' => 'datetime', 'verified_at' => 'datetime']"
                :sortable="['payment_code', 'amount', 'method', 'status', 'paid_at', 'verified_at']"
                :selectable="false"
                row-actions-view="admin.payments.partials.row-actions"
                :row-actions-data="['midtransConfigured' => $midtransConfigured]"
                :delete-route="fn ($row) => route('admin.payments.destroy', $row)"
            >
                <x-slot:filters>
                    <x-ui.select name="status" :options="$statusOptions" :selected="request('status')" placeholder="Semua status" size="sm" class="min-w-48" />
                </x-slot:filters>
            </x-ui.data-table>
        </x-ui.card>

        <x-reports.export-pdf-modal
            id="export-payments-pdf-modal"
            title="Export Historis Pembayaran"
            description="Pilih filter transaksi yang ingin dimasukkan ke file PDF. Sangat cocok untuk arsip verifikasi, pembukuan, atau laporan bulanan."
            :action="route('admin.reports.payments.pdf')"
        >
            <x-ui.input name="search" label="Kata Kunci" placeholder="Kode pembayaran, kode reservasi, nama pelanggan"
                :value="request('search')" />
            <x-ui.select name="date_field" label="Basis Tanggal" :options="$dateFieldOptions"
                :selected="request('date_field', 'paid_at')" />
            <x-ui.input name="date_from" type="date" label="Dari Tanggal" :value="request('date_from')" />
            <x-ui.input name="date_until" type="date" label="Sampai Tanggal" :value="request('date_until')" />
            <x-ui.select name="status" label="Status Pembayaran" :options="$paymentStatusFilterOptions"
                :selected="request('status')" :placeholder="false" />
            <x-ui.select name="method" label="Metode Pembayaran" :options="$paymentMethodFilterOptions"
                :selected="request('method')" :placeholder="false" />
            <x-ui.select name="type" label="Jenis Pembayaran" :options="$paymentTypeFilterOptions"
                :selected="request('type')" :placeholder="false" />
            <x-ui.input name="min_amount" type="number" min="0" label="Nominal Minimum"
                placeholder="Contoh: 50000" :value="request('min_amount')" />
        </x-reports.export-pdf-modal>
    </div>
@endsection

@push('modals')
    <x-ui.modal id="admin-payment-qr-modal" title="Cari Pembayaran via QR" size="xl">
        <div class="space-y-4">
            <div class="rounded-box border border-base-200 bg-base-100 p-4 text-sm text-base-content/70">
                Arahkan webcam ke QR dari pelanggan. Hasil scan akan dibaca sebagai kode pembayaran dan langsung bisa
                dipakai untuk mencari transaksi terkait.
            </div>

            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_20rem]">
                <div class="rounded-box border border-base-200 bg-base-200/30 p-4">
                    <div
                        id="admin-payment-qr-reader"
                        class="flex min-h-[320px] items-center justify-center overflow-hidden rounded-box bg-base-100"
                    >
                        <p class="px-4 text-center text-sm text-base-content/50">
                            Kamera webcam akan muncul di sini saat scanner dibuka.
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div id="admin-payment-qr-status" class="alert alert-info text-sm">
                        <span>Tekan "Mulai Kamera" atau buka modal ini untuk mulai memindai QR pembayaran pelanggan.</span>
                    </div>

                    <div class="rounded-box border border-base-200 bg-base-100 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-base-content/50">Hasil Scan</p>
                        <p
                            id="admin-payment-qr-result"
                            class="mt-3 min-h-[88px] break-all rounded-box bg-base-200/60 px-3 py-3 font-mono text-sm text-base-content/70"
                        >
                            Belum ada hasil scan.
                        </p>
                        <p class="mt-3 text-xs text-base-content/60">
                            QR dari user berisi kode pembayaran DP, misalnya <span class="font-mono">PAY-ABCDE123</span>.
                        </p>
                    </div>

                    <div class="rounded-box border border-dashed border-base-300 bg-base-100 p-4 text-xs text-base-content/60">
                        Setelah QR berhasil terbaca, klik "Cari Sekarang" untuk langsung membuka hasilnya di daftar pembayaran.
                    </div>
                </div>
            </div>
        </div>

        <x-slot:modal-actions position="bottom-right">
            <x-ui.button type="ghost" size="sm" :isSubmit="false" data-payment-qr-start>
                Mulai Kamera
            </x-ui.button>
            <x-ui.button type="ghost" size="sm" :isSubmit="false" data-payment-qr-restart>
                Scan Ulang
            </x-ui.button>
            <x-ui.button type="primary" size="sm" :isSubmit="false" data-payment-qr-search disabled>
                Cari Sekarang
            </x-ui.button>
        </x-slot:modal-actions>
    </x-ui.modal>

    @if ($hasAdminSnapPayment && filled($midtransClientKey))
        <x-ui.modal id="admin-midtrans-loading-modal" title="Menyiapkan Pembayaran" size="sm" :closeButton="false">
            <div class="flex flex-col items-center gap-4 py-4 text-center">
                <span class="loading loading-spinner loading-lg text-primary"></span>
                <div class="space-y-1">
                    <p class="font-semibold text-base-content">Popup Midtrans akan muncul sebentar lagi.</p>
                    <p class="text-sm text-base-content/60">Mohon tunggu, kami sedang menyiapkan pembayaran sisa reservasi.</p>
                </div>
            </div>
        </x-ui.modal>
    @endif
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.7/html5-qrcode.min.js"></script>
    <script>
        window.addEventListener('load', function() {
            const modal = document.getElementById('admin-payment-qr-modal');
            const reader = document.getElementById('admin-payment-qr-reader');
            const status = document.getElementById('admin-payment-qr-status');
            const result = document.getElementById('admin-payment-qr-result');
            const openButtons = document.querySelectorAll('[data-payment-qr-open]');
            const startButton = modal?.querySelector('[data-payment-qr-start]');
            const restartButton = modal?.querySelector('[data-payment-qr-restart]');
            const searchButton = modal?.querySelector('[data-payment-qr-search]');
            const searchForm = document.querySelector('#daftar-pembayaran form[action]');
            const searchInput = searchForm?.querySelector('input[name="search"]');
            const statusClasses = {
                info: 'alert alert-info text-sm',
                success: 'alert alert-success text-sm',
                error: 'alert alert-error text-sm',
                warning: 'alert alert-warning text-sm',
            };

            let html5QrCode = null;
            let lastScanResult = '';
            let scannerActive = false;
            let scannerStarting = false;
            let scannerRequestId = 0;

            if (!modal || !reader || !status || !result) {
                return;
            }

            const renderReaderPlaceholder = (message) => {
                reader.innerHTML = `
                    <p class="px-4 text-center text-sm text-base-content/50">
                        ${message}
                    </p>
                `;
            };

            const setStatus = (message, tone = 'info') => {
                status.className = statusClasses[tone] || statusClasses.info;
                status.innerHTML = '';

                const textNode = document.createElement('span');
                textNode.textContent = message;
                status.appendChild(textNode);
            };

            const syncButtons = () => {
                if (startButton) {
                    startButton.disabled = scannerActive || scannerStarting;
                    startButton.classList.toggle('btn-disabled', startButton.disabled);
                }

                if (restartButton) {
                    restartButton.disabled = scannerStarting;
                    restartButton.classList.toggle('btn-disabled', restartButton.disabled);
                }

                if (searchButton) {
                    const disabled = lastScanResult.trim() === '';
                    searchButton.disabled = disabled;
                    searchButton.classList.toggle('btn-disabled', disabled);
                }
            };

            const setResult = (value = '') => {
                lastScanResult = value.trim();
                result.textContent = lastScanResult || 'Belum ada hasil scan.';
                syncButtons();
            };

            const stopScanner = async ({ keepPlaceholder = false } = {}) => {
                scannerRequestId += 1;
                scannerActive = false;
                scannerStarting = false;

                const scanner = html5QrCode;
                html5QrCode = null;

                if (scanner) {
                    try {
                        await scanner.stop();
                    } catch (error) {
                        //
                    }

                    try {
                        await scanner.clear();
                    } catch (error) {
                        //
                    }
                }

                if (!keepPlaceholder) {
                    renderReaderPlaceholder('Kamera dihentikan. Buka scanner lagi untuk memindai QR berikutnya.');
                }

                syncButtons();
            };

            const submitSearch = () => {
                if (lastScanResult.trim() === '') {
                    setStatus('Belum ada hasil scan untuk dicari.', 'warning');

                    return;
                }

                if (searchInput) {
                    searchInput.value = lastScanResult;
                }

                if (searchForm) {
                    modal.close();
                    searchForm.submit();

                    return;
                }

                const url = new URL(window.location.href);
                url.searchParams.set('search', lastScanResult);
                url.hash = 'daftar-pembayaran';
                modal.close();
                window.location.href = url.toString();
            };

            const startScanner = async () => {
                if (scannerActive || scannerStarting) {
                    return;
                }

                if (typeof window.Html5Qrcode === 'undefined') {
                    setStatus('Library QR scanner belum termuat. Muat ulang halaman lalu coba lagi.', 'error');

                    return;
                }

                if (!modal.open) {
                    modal.showModal();
                }

                const requestId = ++scannerRequestId;

                scannerStarting = true;
                setResult('');
                renderReaderPlaceholder('Meminta akses kamera webcam...');
                setStatus('Izinkan kamera lalu arahkan QR pelanggan ke area scanner.', 'info');
                syncButtons();

                try {
                    const cameras = await window.Html5Qrcode.getCameras();

                    if (requestId !== scannerRequestId || !modal.open) {
                        scannerStarting = false;
                        syncButtons();

                        return;
                    }

                    if (cameras.length === 0) {
                        throw new Error('Tidak ada webcam yang terdeteksi.');
                    }

                    const preferredCamera = cameras.find((camera) => /back|rear|environment/i.test(camera.label))
                        || cameras[0];

                    reader.innerHTML = '';
                    html5QrCode = new window.Html5Qrcode('admin-payment-qr-reader');

                    await html5QrCode.start(
                        preferredCamera.id,
                        {
                            fps: 10,
                            aspectRatio: 1,
                            qrbox: {
                                width: 220,
                                height: 220,
                            },
                        },
                        async (decodedText) => {
                            const paymentCode = decodedText.trim();

                            if (paymentCode === '' || paymentCode === lastScanResult) {
                                return;
                            }

                            setResult(paymentCode);
                            setStatus(`QR berhasil dibaca: ${paymentCode}`, 'success');

                            if (searchInput) {
                                searchInput.value = paymentCode;
                            }

                            await stopScanner({ keepPlaceholder: true });
                            renderReaderPlaceholder('QR sudah terbaca. Klik "Cari Sekarang" atau gunakan "Scan Ulang".');
                        },
                        () => {}
                    );

                    if (requestId !== scannerRequestId || !modal.open) {
                        await stopScanner();

                        return;
                    }

                    scannerStarting = false;
                    scannerActive = true;
                    setStatus('Kamera aktif. Arahkan QR pelanggan ke area scanner.', 'info');
                    syncButtons();
                } catch (error) {
                    scannerStarting = false;
                    scannerActive = false;

                    if (html5QrCode) {
                        try {
                            await html5QrCode.clear();
                        } catch (clearError) {
                            //
                        }

                        html5QrCode = null;
                    }

                    renderReaderPlaceholder('Kamera tidak dapat dijalankan pada perangkat ini.');
                    setStatus(
                        error instanceof Error
                            ? `Scanner gagal dibuka: ${error.message}`
                            : 'Scanner gagal dibuka. Pastikan izin kamera diberikan.',
                        'error'
                    );
                    syncButtons();
                }
            };

            openButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    if (!modal.open) {
                        modal.showModal();
                    }

                    startScanner();
                });
            });

            startButton?.addEventListener('click', () => {
                startScanner();
            });

            restartButton?.addEventListener('click', async () => {
                await stopScanner();
                startScanner();
            });

            searchButton?.addEventListener('click', () => {
                submitSearch();
            });

            modal.addEventListener('close', () => {
                stopScanner();
                setStatus('Scanner ditutup. Buka lagi saat ingin memindai QR berikutnya.', 'info');
            });

            renderReaderPlaceholder('Kamera webcam akan muncul di sini saat scanner dibuka.');
            setResult('');
            syncButtons();
        });
    </script>
@endpush

@if ($hasAdminSnapPayment && filled($midtransClientKey))
    @push('scripts')
        <script src="{{ $midtransSnapJsUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
        <script>
            window.addEventListener('load', function() {
                const sessionSnapToken = @js($sessionAdminPaymentSnapToken);
                const sessionOrderId = @js($sessionAdminPaymentOrderId);
                const sessionPaymentId = @js($sessionAdminPaymentId);
                const basePaymentsUrl = @js(request()->fullUrlWithoutQuery(['midtrans_order_id']));
                const loadingModal = document.getElementById('admin-midtrans-loading-modal');
                const minimumLoadingDuration = 700;

                const setButtonLoadingState = (button, loading) => {
                    if (!button) {
                        return;
                    }

                    const label = button.querySelector('.admin-midtrans-button__label');
                    const state = button.querySelector('.admin-midtrans-button__state');

                    button.disabled = loading;
                    button.classList.toggle('btn-disabled', loading);

                    if (label) {
                        label.classList.toggle('hidden', loading);
                    }

                    if (state) {
                        state.classList.toggle('hidden', !loading);
                        state.classList.toggle('inline-flex', loading);
                    }
                };

                const toggleLoadingModal = (open) => {
                    if (!loadingModal) {
                        return;
                    }

                    if (open) {
                        if (!loadingModal.open) {
                            loadingModal.showModal();
                        }

                        return;
                    }

                    if (loadingModal.open) {
                        loadingModal.close();
                    }
                };

                const buildPaymentsUrl = (orderId) => {
                    const url = new URL(basePaymentsUrl, window.location.origin);

                    if (orderId) {
                        url.searchParams.set('midtrans_order_id', orderId);
                    }

                    return url.toString();
                };

                const waitForSnap = (timeoutMs = 8000, intervalMs = 150) => new Promise((resolve, reject) => {
                    if (window.snap?.pay) {
                        resolve(window.snap);

                        return;
                    }

                    const startedAt = Date.now();
                    const timerId = window.setInterval(() => {
                        if (window.snap?.pay) {
                            window.clearInterval(timerId);
                            resolve(window.snap);

                            return;
                        }

                        if ((Date.now() - startedAt) >= timeoutMs) {
                            window.clearInterval(timerId);
                            reject(new Error('Midtrans Snap belum siap.'));
                        }
                    }, intervalMs);
                });

                const openSnapPayment = ({ token, fallbackOrderId, triggerButton = null }) => {
                    if (!token) {
                        return;
                    }

                    const loadingStartedAt = Date.now();

                    setButtonLoadingState(triggerButton, true);
                    toggleLoadingModal(true);

                    waitForSnap()
                        .then(() => {
                            const remainingDelay = Math.max(0, minimumLoadingDuration - (Date.now() - loadingStartedAt));

                            window.setTimeout(() => {
                                toggleLoadingModal(false);

                                try {
                                    window.snap.pay(token, {
                                        onSuccess: function(result) {
                                            window.location.href = buildPaymentsUrl(result?.order_id || fallbackOrderId);
                                        },
                                        onPending: function(result) {
                                            window.location.href = buildPaymentsUrl(result?.order_id || fallbackOrderId);
                                        },
                                        onError: function(result) {
                                            window.location.href = buildPaymentsUrl(result?.order_id || fallbackOrderId);
                                        },
                                        onClose: function() {
                                            setButtonLoadingState(triggerButton, false);
                                            toggleLoadingModal(false);
                                        },
                                    });
                                } catch (error) {
                                    window.location.href = buildPaymentsUrl(fallbackOrderId);
                                }
                            }, remainingDelay);
                        })
                        .catch(() => {
                            window.location.href = buildPaymentsUrl(fallbackOrderId);
                        });
                };

                document.querySelectorAll('[data-admin-midtrans-open]').forEach(function(button) {
                    button.addEventListener('click', function() {
                        openSnapPayment({
                            token: button.dataset.snapToken,
                            fallbackOrderId: button.dataset.orderId,
                            triggerButton: button,
                        });
                    });
                });

                if (sessionSnapToken) {
                    const triggerButton = sessionPaymentId
                        ? document.querySelector('[data-admin-midtrans-open][data-payment-id="' + sessionPaymentId + '"]')
                        : null;

                    openSnapPayment({
                        token: sessionSnapToken,
                        fallbackOrderId: sessionOrderId,
                        triggerButton,
                    });
                }
            });
        </script>
    @endpush
@endif
