
<div>
    <section class="flex w-full min-h-screen flex-col gap-10 px-6 py-20 text-primary md:flex-row md:p-24">
        <div class="space-y-5 flex-1 pt-8" data-aos="fade-up">
            <h1 class="text-6xl/tight font-semibold">Apa itu Cafe Amiko?</h1>
            <p class="text-md/loose font-light">
                {{ $profile->description ?? 'Cafe Amiko adalah creative coffee space 24 jam di Makassar yang menggabungkan budaya kopi, musik, dan pengalaman berbasis komunitas. Nama "amiko" sendiri berarti teman, sehingga tempat ini dibangun sebagai ruang yang terasa hangat, inklusif, dan mudah didekati. Bukan sekadar tempat minum kopi, Cafe Amiko hadir sebagai ruang singgah untuk mengobrol, bekerja, berkumpul, menikmati hiburan, dan membangun koneksi dalam suasana yang nyaman.' }}
            </p>
        </div>
        <div class="flex-1 md:-mt-14" data-aos="fade-up">
            <img src="{{ asset('assets/images/about.png') }}" alt="Suasana Cafe Amiko"
                class="h-full w-full rounded-md object-cover shadow-2xl">
        </div>
    </section>


</div>
