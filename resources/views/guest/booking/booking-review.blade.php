<div class="space-y-5" id="reviews">
    <div class="space-y-1">
        <h2 class="text-2xl font-semibold">Ulasan</h2>
        <p class="text-sm font-light">
            @if ($reviewCount > 0)
                Rata-rata: {{ number_format($reviewAverage, 1) }} / 5 ({{ $reviewCount }} ulasan)
            @else
                Belum ada ulasan untuk paket ini.
            @endif
        </p>
    </div>

    <div class="space-y-4 rounded-lg border border-gray-200 p-4">
        <h3 class="text-lg font-semibold">Tulis Ulasan</h3>

        <form method="POST" action="{{ route('booking.reviews.store', ['slug' => $package['slug']]) }}"
            class="space-y-4">
            @csrf

            @guest
                <div class="space-y-2">
                    <label class="text-sm font-medium text-primary">Nama</label>
                    <input type="text" name="guest_name" value="{{ old('guest_name') }}"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3"
                        placeholder="Nama kamu">
                </div>
            @else
                <div class="rounded-md border border-gray-200 bg-gray-50 p-4 text-sm font-light text-gray-600">
                    Ulasan akan dikirim sebagai <span class="font-semibold text-primary">{{ auth()->user()->name }}</span>.
                </div>
            @endguest

            <div class="space-y-2">
                <label class="text-sm font-medium text-primary">Rating</label>
                <select name="rating" class="w-full rounded-xl border border-gray-200 px-4 py-3">
                    @foreach (range(5, 1) as $rating)
                        <option value="{{ $rating }}" @selected((int) old('rating', 5) === $rating)>
                            {{ $rating }} / 5
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-primary">Komentar</label>
                <textarea name="comment" rows="4" class="w-full rounded-xl border border-gray-200 px-4 py-3"
                    placeholder="Bagikan pengalamanmu setelah berkunjung ke Cafe Amiko.">{{ old('comment') }}</textarea>
            </div>

            <button type="submit"
                class="rounded-xl bg-primary px-4 py-2 font-semibold text-white transition hover:bg-primary/90">
                Kirim Ulasan
            </button>
        </form>
    </div>

    @if ($reviews->isEmpty())
        <div class="rounded-lg border border-dashed border-gray-200 bg-gray-50 p-6 text-sm font-light text-gray-500">
            Belum ada ulasan tersimpan. Jadilah yang pertama membagikan pengalamanmu.
        </div>
    @else
        <div class="space-y-3">
            @foreach ($reviews as $review)
                <div class="space-y-2 rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold">
                            {{ $review->guest_name }}
                        </div>
                        <div class="text-sm font-light text-gray-600">
                            {{ $review->created_at?->format('d-m-Y') }}
                        </div>
                    </div>

                    <div class="text-sm">
                        <span class="font-semibold">{{ $review->rating }}/5</span>
                        <span class="font-light text-gray-600">
                            {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                        </span>
                    </div>

                    <p class="text-sm font-light">{{ $review->comment }}</p>
                </div>
            @endforeach
        </div>
    @endif
</div>
