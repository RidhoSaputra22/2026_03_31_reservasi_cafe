{{--
    Toast Container Component
    For showing notifications/toasts
--}}

<div id="toast-container" class="toast toast-end toast-bottom z-[9999]">
    @if (session('success'))
        <div class="alert alert-success" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <ul class="list-disc ml-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @if (session('new_user_credential'))
        <div class="alert alert-success max-w-sm" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 15000)">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
            <div>
                <div class="font-semibold text-xs mb-1">Akun RT/RW Berhasil Dibuat</div>
                <span class="text-xs font-mono">{{ session('new_user_credential') }}</span>
                <div class="text-xs opacity-70 mt-1">Catat password sementara ini. Notifikasi hilang dalam 15 detik.
                </div>
            </div>
        </div>
    @endif
</div>
