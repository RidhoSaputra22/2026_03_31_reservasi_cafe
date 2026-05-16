<footer class="border-t border-coffee-200 bg-white">
  <div class="mx-auto grid max-w-7xl gap-8 px-5 py-10 lg:grid-cols-[1.2fr_.8fr_.8fr] lg:px-8">
    <div>
      <p class="text-xl font-light tracking-[0.35em] text-coffee-900">INTERLUDE</p>
      <p class="mt-1 text-xs font-black tracking-[0.28em] text-coffee-500">COFFEE &amp; TEA</p>
      <p class="mt-4 max-w-md text-sm leading-6 text-coffee-600">{{ config('cafe.tagline') }}.</p>
    </div>
    <div>
      <p class="font-black text-coffee-800">Jam Buka</p>
      <p class="mt-3 text-sm leading-7 text-coffee-600">Senin - Jumat: 08.00 - 22.00<br>Sabtu - Minggu: 09.00 - 23.00</p>
    </div>
    <div>
      <p class="font-black text-coffee-800">Kontak</p>
      <p class="mt-3 text-sm leading-7 text-coffee-600">Jl. Kopi Senja No. 8<br>hello@interlude.test<br>+62 812 0000 8888</p>
    </div>
  </div>
  <div class="border-t border-coffee-100 px-5 py-5 text-center text-xs font-semibold tracking-wide text-coffee-500">
    &copy; {{ now()->year }} Interlude Coffee &amp; Tea. Reservation UI template.
  </div>
</footer>
<script src="{{ asset('assets/js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
