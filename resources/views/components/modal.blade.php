@props(['maxWidth' => 'max-w-lg'])


<div x-cloak x-show="isOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/50" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" @click="isOpen = false"></div>

    <!-- Modal Box -->
    <div class="relative bg-white rounded-lg shadow-lg w-full {{ $maxWidth ?? 'max-w-lg' }} p-6"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
        <div class="flex">
            <h2 class="text-xl font-semibold mb-4 flex-1">
                {{ $title }}
            </h2>
            <button @click="isOpen = false" class="px-4 py-2 border rounded">
                Tutup
            </button>
        </div>

        <p class="text-gray-600 mb-6">
            {{ $slot }}
        </p>
    </div>
</div>
