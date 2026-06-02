<div>
    <section class="mx-auto min-h-screen w-full max-w-7xl space-y-14 px-6 py-20 text-primary md:p-24">
        <div class="space-y-5 flex-1 pt-8 text-center" data-aos="fade-up">
            <h1 class="text-6xl/tight font-semibold">Frequently Asked Question</h1>
            <p class="text-md/loose font-light ">Berikut pertanyaan yang paling sering ditanyai</p>

        </div>
        <div class="w-full divide-y divide-outline text-on-surface dark:divide-outline-dark dark:text-on-surface-dark"
            data-aos="fade-up">
            <div x-data="{ isExpanded: false }">
                <button id="controlsAccordionItemOne" type="button"
                    class="flex w-full items-center justify-between gap-4 py-4 text-left underline-offset-2 focus-visible:underline focus-visible:outline-hidden text-xl"
                    aria-controls="accordionItemOne" x-on:click="isExpanded = ! isExpanded" x-bind:class="isExpanded ? 'text-on-surface-strong dark:text-on-surface-dark-strong font-semibold' :
                        'text-on-surface dark:text-on-surface-dark font-medium'"
                    x-bind:aria-expanded="isExpanded ? 'true' : 'false'">
                    Bagaimana cara melakukan reservasi di Cafe Amiko?
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2"
                        stroke="currentColor" class="size-5 shrink-0 transition" aria-hidden="true"
                        x-bind:class="isExpanded ? 'rotate-180' : ''">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
                <div x-cloak x-show="isExpanded" id="accordionItemOne" role="region"
                    aria-labelledby="controlsAccordionItemOne" x-collapse>
                    <div class="pb-4 text-sm sm:text-base text-pretty ">
                        Reservasi dilakukan melalui website dengan memilih paket atau area yang diinginkan, lalu
                        menentukan tanggal dan slot kunjungan. Setelah permintaan dikirim, detail reservasi bisa
                        dikonfirmasi kembali oleh tim sesuai ketersediaan meja.
                    </div>
                </div>
            </div>
            <div x-data="{ isExpanded: false }">
                <button id="controlsAccordionItemOne" type="button"
                    class="flex w-full items-center justify-between gap-4 py-4 text-left underline-offset-2 focus-visible:underline focus-visible:outline-hidden text-xl"
                    aria-controls="accordionItemOne" x-on:click="isExpanded = ! isExpanded" x-bind:class="isExpanded ? 'text-on-surface-strong dark:text-on-surface-dark-strong font-semibold' :
                        'text-on-surface dark:text-on-surface-dark font-medium'"
                    x-bind:aria-expanded="isExpanded ? 'true' : 'false'">
                    Apakah jadwal reservasi dapat diubah atau dibatalkan?
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2"
                        stroke="currentColor" class="size-5 shrink-0 transition" aria-hidden="true"
                        x-bind:class="isExpanded ? 'rotate-180' : ''">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
                <div x-cloak x-show="isExpanded" id="accordionItemOne" role="region"
                    aria-labelledby="controlsAccordionItemOne" x-collapse>
                    <div class="pb-4 text-sm sm:text-base text-pretty ">
                        Perubahan atau pembatalan sebaiknya diinformasikan secepat mungkin agar slot bisa disesuaikan.
                        Untuk saat ini fitur reschedule masih statis, jadi penyesuaian reservasi tetap perlu
                        dikonfirmasi manual oleh tim.
                    </div>
                </div>
            </div>
            <div x-data="{ isExpanded: false }">
                <button id="controlsAccordionItemOne" type="button"
                    class="flex w-full items-center justify-between gap-4 py-4 text-left underline-offset-2 focus-visible:underline focus-visible:outline-hidden text-xl"
                    aria-controls="accordionItemOne" x-on:click="isExpanded = ! isExpanded" x-bind:class="isExpanded ? 'text-on-surface-strong dark:text-on-surface-dark-strong font-semibold' :
                        'text-on-surface dark:text-on-surface-dark font-medium'"
                    x-bind:aria-expanded="isExpanded ? 'true' : 'false'">
                    Apa yang membuat Cafe Amiko berbeda?
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2"
                        stroke="currentColor" class="size-5 shrink-0 transition" aria-hidden="true"
                        x-bind:class="isExpanded ? 'rotate-180' : ''">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
                <div x-cloak x-show="isExpanded" id="accordionItemOne" role="region"
                    aria-labelledby="controlsAccordionItemOne" x-collapse>
                    <div class="pb-4 text-sm sm:text-base text-pretty ">
                        Dari informasi online yang tersedia, Amiko dikenal sebagai creative coffee space 24 jam yang
                        menggabungkan budaya kopi, musik, dan pengalaman komunitas. Jadi selain datang untuk minum
                        kopi, tamu juga bisa menikmati suasana yang lebih hidup untuk ngobrol, kerja, atau ikut agenda
                        hiburan.
                    </div>
                </div>
            </div>
            <div x-data="{ isExpanded: false }">
                <button id="controlsAccordionItemOne" type="button"
                    class="flex w-full items-center justify-between gap-4 py-4 text-left underline-offset-2 focus-visible:underline focus-visible:outline-hidden text-xl"
                    aria-controls="accordionItemOne" x-on:click="isExpanded = ! isExpanded" x-bind:class="isExpanded ? 'text-on-surface-strong dark:text-on-surface-dark-strong font-semibold' :
                        'text-on-surface dark:text-on-surface-dark font-medium'"
                    x-bind:aria-expanded="isExpanded ? 'true' : 'false'">
                    Fasilitas seperti apa yang tersedia saat reservasi?
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2"
                        stroke="currentColor" class="size-5 shrink-0 transition" aria-hidden="true"
                        x-bind:class="isExpanded ? 'rotate-180' : ''">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
                <div x-cloak x-show="isExpanded" id="accordionItemOne" role="region"
                    aria-labelledby="controlsAccordionItemOne" x-collapse>
                    <div class="pb-4 text-sm sm:text-base text-pretty ">
                        Setiap paket reservasi dapat mencakup pilihan meja, durasi kunjungan, minuman, snack, dan
                        kebutuhan area tertentu. Detail fasilitas bisa dilihat langsung pada halaman paket sebelum
                        melakukan booking.
                    </div>
                </div>
            </div>
            <div x-data="{ isExpanded: false }">
                <button id="controlsAccordionItemOne" type="button"
                    class="flex w-full items-center justify-between gap-4 py-4 text-left underline-offset-2 focus-visible:underline focus-visible:outline-hidden text-xl"
                    aria-controls="accordionItemOne" x-on:click="isExpanded = ! isExpanded" x-bind:class="isExpanded ? 'text-on-surface-strong dark:text-on-surface-dark-strong font-semibold' :
                        'text-on-surface dark:text-on-surface-dark font-medium'"
                    x-bind:aria-expanded="isExpanded ? 'true' : 'false'">
                    Bagaimana cara menemukan lokasi Cafe Amiko?
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2"
                        stroke="currentColor" class="size-5 shrink-0 transition" aria-hidden="true"
                        x-bind:class="isExpanded ? 'rotate-180' : ''">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
                <div x-cloak x-show="isExpanded" id="accordionItemOne" role="region"
                    aria-labelledby="controlsAccordionItemOne" x-collapse>
                    <div class="pb-4 text-sm sm:text-base text-pretty ">
                        Cafe Amiko berada di Jl. Meranti No.215, Paropo, Kec. Panakkukang, Kota Makassar. Anda bisa
                        membuka halaman About untuk melihat alamat lengkap dan langsung menuju Google Maps.
                    </div>
                </div>
            </div>

        </div>


    </section>

</div>
