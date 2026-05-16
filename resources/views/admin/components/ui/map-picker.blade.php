{{--
    Reusable Map Picker Component

    Menampilkan peta interaktif untuk memilih lokasi (latitude/longitude).
    User bisa klik peta atau drag marker, dan koordinat otomatis terisi.
    Mendukung pencarian alamat dan tombol "Lokasi Saya".

    @param string $latitude - Nama field latitude (default: 'latitude')
    @param string $longitude - Nama field longitude (default: 'longitude')
    @param float|null $latitudeValue - Nilai latitude saat ini
    @param float|null $longitudeValue - Nilai longitude saat ini
    @param string $label - Label section (default: 'Pilih Lokasi di Peta')
    @param string $height - Tinggi peta (default: '400px')
    @param array $center - Koordinat pusat default [lat, lng]
    @param int $zoom - Zoom level (default: 15)

    @param string|null $addressField - Nama field alamat di form untuk auto-fill (default: null)
    @param string|null $addressValue - Nilai alamat saat ini

    Usage:
    <x-ui.map-picker />

    <x-ui.map-picker
        latitude="latitude"
        longitude="longitude"
        :latitudeValue="old('latitude', $model->latitude)"
        :longitudeValue="old('longitude', $model->longitude)"
        label="Lokasi Sekolah"
        addressField="alamat"
        :addressValue="old('alamat', $model->alamat)"
    />
--}}

@props([
    'latitude' => 'latitude',
    'longitude' => 'longitude',
    'latitudeValue' => null,
    'longitudeValue' => null,
    'label' => 'Pilih Lokasi di Peta',
    'height' => '400px',
    'center' => [-5.1476, 119.4934],
    'zoom' => 15,
    'addressField' => null,
    'addressValue' => null,
])

@php
    $mapId = 'map-picker-' . uniqid();
    $initLat = old($latitude, $latitudeValue) ?: $center[0];
    $initLng = old($longitude, $longitudeValue) ?: $center[1];
    $hasInitial = old($latitude, $latitudeValue) && old($longitude, $longitudeValue);
    $initAddress = $addressField ? old($addressField, $addressValue) : '';
@endphp

@once
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <style>
            .map-picker-container {
                position: relative;
            }

            .map-picker-container .leaflet-container {
                z-index: 1;
                border-radius: 0.5rem;
            }

            .map-picker-search {
                position: absolute;
                top: 10px;
                left: 50px;
                right: 50px;
                z-index: 1000;
            }

            .map-picker-search input {
                width: 100%;
                padding: 8px 12px;
                border: 2px solid rgba(0, 0, 0, .2);
                border-radius: 6px;
                font-size: 14px;
                background: white;
                box-shadow: 0 2px 6px rgba(0, 0, 0, .15);
            }

            .map-picker-search input:focus {
                outline: none;
                border-color: #3b82f6;
            }

            .map-picker-search .search-results {
                background: white;
                border-radius: 6px;
                margin-top: 4px;
                max-height: 200px;
                overflow-y: auto;
                box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
            }

            .map-picker-search .search-results div {
                padding: 8px 12px;
                cursor: pointer;
                font-size: 13px;
                border-bottom: 1px solid #f0f0f0;
            }

            .map-picker-search .search-results div:hover {
                background: #f0f9ff;
            }

            .map-picker-locate {
                position: absolute;
                bottom: 24px;
                right: 10px;
                z-index: 1000;
                background: white;
                border: 2px solid rgba(0, 0, 0, .2);
                border-radius: 6px;
                width: 36px;
                height: 36px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                box-shadow: 0 2px 6px rgba(0, 0, 0, .15);
            }

            .map-picker-locate:hover {
                background: #f0f9ff;
            }

            .map-picker-rw-label {
                background: none !important;
                border: none !important;
                box-shadow: none !important;
                font-size: 11px;
                font-weight: 700;
                color: #1e293b;
                text-shadow: 1px 1px 2px #fff, -1px -1px 2px #fff, 1px -1px 2px #fff, -1px 1px 2px #fff;
                white-space: nowrap;
                pointer-events: none;
            }

            .map-picker-rw-toggle {
                position: absolute;
                bottom: 24px;
                left: 10px;
                z-index: 1000;
                background: white;
                border: 2px solid rgba(0, 0, 0, .2);
                border-radius: 6px;
                padding: 4px 8px;
                cursor: pointer;
                font-size: 12px;
                font-weight: 600;
                box-shadow: 0 2px 6px rgba(0, 0, 0, .15);
                display: flex;
                align-items: center;
                gap: 4px;
                user-select: none;
            }

            .map-picker-rw-toggle:hover {
                background: #f0f9ff;
            }

            .map-picker-rw-toggle .dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: #6366f1;
            }
        </style>
    @endpush
@endonce

<div x-data="mapPicker_{{ str_replace('-', '_', $mapId) }}()" class="form-control w-full">
    {{-- Label --}}
    <h3 class="text-lg font-semibold mb-4 border-b pb-2">
        <span class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {{ $label }}
        </span>
    </h3>

    {{-- Info text --}}
    <p class="text-sm text-base-content/60 mb-3">
        Klik pada peta untuk memilih lokasi, atau gunakan pencarian alamat. Marker bisa di-drag untuk menyesuaikan
        posisi.
    </p>

    {{-- Map container --}}
    <div class="map-picker-container mb-4" style="height: {{ $height }};">




        {{-- RW layer toggle --}}
        <div class="map-picker-rw-toggle" @click="toggleRwLayer()"
            :title="showRw ? 'Sembunyikan batas RW' : 'Tampilkan batas RW'">
            <span class="dot" :style="{ background: showRw ? '#6366f1' : '#94a3b8' }"></span>
            <span x-text="showRw ? 'RW On' : 'RW Off'"></span>
        </div>

        {{-- Leaflet map --}}
        <div id="{{ $mapId }}" class="w-full h-full rounded-lg"></div>
    </div>

    {{-- Address from reverse geocoding --}}
    <div class="form-control w-full mb-4" x-show="lat && lng" x-cloak>
        <label class="label">
            <span class="label-text font-medium">Alamat dari Peta</span>
            <span class="label-text-alt">
                <span x-show="reverseLoading" class="loading loading-spinner loading-xs"></span>
                <span x-show="!reverseLoading && resolvedAddress" class="text-success text-xs">Otomatis terdeteksi</span>
            </span>
        </label>
        <div class="flex gap-2">
            <x-ui.input type="text" size="sm" x-model="resolvedAddress" readonly
                class="bg-base-200 cursor-not-allowed" placeholder="Mendeteksi alamat..." />
            @if($addressField)
            <x-ui.button type="primary" size="sm" :outline="true" :isSubmit="false" class="gap-1 whitespace-nowrap"
                x-show="resolvedAddress" x-cloak
                @click="applyAddress()"
                :class="{ 'btn-success': addressApplied }">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span x-text="addressApplied ? 'Tersalin' : 'Isi ke Alamat'"></span>
            </x-ui.button>
            @endif
        </div>
        <label class="label" x-show="resolvedAddress && !addressApplied" x-cloak>
            <span class="label-text-alt text-info">@if($addressField)Klik "Isi ke Alamat" untuk menyalin ke form alamat, atau edit manual.@else Alamat berdasarkan koordinat.@endif</span>
        </label>
    </div>

    {{-- Coordinate inputs --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-ui.input type="text" size="sm" name="{{ $latitude }}" label="Latitude" x-model="lat" readonly
            class="bg-base-200 cursor-not-allowed" placeholder="Klik peta untuk mengisi"
            :error="$errors->first($latitude)" />
        <x-ui.input type="text" size="sm" name="{{ $longitude }}" label="Longitude" x-model="lng" readonly
            class="bg-base-200 cursor-not-allowed" placeholder="Klik peta untuk mengisi"
            :error="$errors->first($longitude)" />
    </div>

    {{-- Clear button --}}
    <div class="mt-2" x-show="lat && lng" x-cloak>
        <x-ui.button type="ghost" size="xs" :isSubmit="false" class="text-error gap-1" @click="clearLocation()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Hapus Lokasi
        </x-ui.button>
    </div>
</div>

@push('scripts')
    <script>
        function mapPicker_{{ str_replace('-', '_', $mapId) }}() {
            return {
                map: null,
                marker: null,
                lat: '{{ $hasInitial ? $initLat : '' }}',
                lng: '{{ $hasInitial ? $initLng : '' }}',
                searchQuery: '',
                searchResults: [],
                searchTimeout: null,
                rwLayer: null,
                rwLabelLayer: null,
                showRw: true,
                resolvedAddress: '',
                reverseLoading: false,
                reverseTimeout: null,
                addressApplied: false,
                addressFieldName: '{{ $addressField ?? '' }}',

                init() {
                    this.$nextTick(() => {
                        // Guard against double initialization
                        if (this.map) return;

                        // Initialize map
                        this.map = L.map('{{ $mapId }}', {
                            center: [{{ $initLat }}, {{ $initLng }}],
                            zoom: {{ $hasInitial ? 17 : $zoom }},
                            zoomControl: true,
                        });

                        // Base layers
                        const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors',
                            maxZoom: 19,
                        });

                        const satellite = L.tileLayer(
                            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                                attribution: '&copy; Esri',
                                maxZoom: 19,
                            });

                        osm.addTo(this.map);
                        L.control.layers({
                            'Peta': osm,
                            'Satelit': satellite
                        }, {}, {
                            position: 'topright'
                        }).addTo(this.map);

                        // Load RW polygon overlay
                        this.loadRwLayer();

                        // Place initial marker if we have coordinates
                        @if ($hasInitial)
                            this.placeMarker({{ $initLat }}, {{ $initLng }});
                        @endif

                        // Map click handler
                        this.map.on('click', (e) => {
                            this.placeMarker(e.latlng.lat, e.latlng.lng);
                        });
                    });
                },

                async loadRwLayer() {
                    try {
                        const url = '{{ route('peta.geojson.rw') }}';
                        const resp = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin',
                        });
                        if (!resp.ok) return;

                        const geojson = await resp.json();
                        if (!geojson.features || geojson.features.length === 0) return;

                        // RW polygon layer
                        this.rwLayer = L.geoJSON(geojson, {
                            style: (feature) => ({
                                color: feature.properties.warna || '#6366f1',
                                weight: 2,
                                opacity: 0.8,
                                fillColor: feature.properties.warna || '#6366f1',
                                fillOpacity: 0.10,
                                dashArray: '5, 5',
                            }),
                            onEachFeature: (feature, layer) => {
                                const p = feature.properties;

                                layer.on('mouseover', () => {
                                    layer.setStyle({
                                        fillOpacity: 0.25,
                                        weight: 3
                                    });
                                });
                                layer.on('mouseout', () => {
                                    layer.setStyle({
                                        fillOpacity: 0.10,
                                        weight: 2
                                    });
                                });
                            },
                        }).addTo(this.map);

                        // RW labels layer (permanent)
                        this.rwLabelLayer = L.layerGroup().addTo(this.map);
                        geojson.features.forEach(f => {
                            if (!f.geometry) return;
                            const bounds = L.geoJSON(f).getBounds();
                            const center = bounds.getCenter();
                            L.marker(center, {
                                icon: L.divIcon({
                                    className: 'map-picker-rw-label',
                                    html: f.properties.RW || '',
                                    iconSize: [50, 16],
                                    iconAnchor: [25, 8],
                                }),
                                interactive: false,
                            }).addTo(this.rwLabelLayer);
                        });

                        // Fit map to RW bounds if no initial marker
                        @if (!$hasInitial)
                            this.map.fitBounds(this.rwLayer.getBounds(), {
                                padding: [20, 20]
                            });
                        @endif
                    } catch (e) {
                        console.warn('Map picker: gagal memuat batas RW', e);
                    }
                },

                toggleRwLayer() {
                    this.showRw = !this.showRw;
                    if (this.rwLayer) {
                        this.showRw ? this.rwLayer.addTo(this.map) : this.map.removeLayer(this.rwLayer);
                    }
                    if (this.rwLabelLayer) {
                        this.showRw ? this.rwLabelLayer.addTo(this.map) : this.map.removeLayer(this.rwLabelLayer);
                    }
                },

                placeMarker(lat, lng) {
                    this.lat = parseFloat(lat).toFixed(7);
                    this.lng = parseFloat(lng).toFixed(7);
                    this.addressApplied = false;

                    if (this.marker) {
                        this.marker.setLatLng([lat, lng]);
                    } else {
                        this.marker = L.marker([lat, lng], {
                            draggable: true,
                            autoPan: true,
                        }).addTo(this.map);

                        // Drag handler
                        this.marker.on('dragend', (e) => {
                            const pos = e.target.getLatLng();
                            this.lat = parseFloat(pos.lat).toFixed(7);
                            this.lng = parseFloat(pos.lng).toFixed(7);
                            this.addressApplied = false;
                            this.reverseGeocode(pos.lat, pos.lng);
                        });
                    }

                    // Update popup and run reverse geocoding
                    this.updatePopup();
                    this.reverseGeocode(lat, lng);
                },

                updatePopup() {
                    if (!this.marker) return;
                    const addrHtml = this.resolvedAddress
                        ? `<br><span style="color:#666">${this.resolvedAddress}</span>`
                        : '';
                    this.marker.bindPopup(
                        `<div class="text-sm"><strong>Lokasi Terpilih</strong><br>Lat: ${this.lat}<br>Lng: ${this.lng}${addrHtml}</div>`
                    ).openPopup();
                },

                reverseGeocode(lat, lng) {
                    // Debounce to avoid too many API calls
                    if (this.reverseTimeout) clearTimeout(this.reverseTimeout);

                    this.reverseLoading = true;
                    this.resolvedAddress = '';

                    this.reverseTimeout = setTimeout(async () => {
                        try {
                            const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1&accept-language=id`;

                            const resp = await fetch(url, {
                                headers: { 'Accept': 'application/json' }
                            });
                            if (!resp.ok) throw new Error('Nominatim error');

                            const data = await resp.json();
                            if (data && data.display_name) {
                                // Build a cleaner address from address parts
                                const addr = data.address || {};
                                const parts = [];
                                if (addr.road) parts.push(addr.road);
                                if (addr.house_number) parts[parts.length - 1] = (parts[parts.length - 1] || '') + ' No. ' + addr.house_number;
                                if (addr.neighbourhood) parts.push(addr.neighbourhood);
                                if (addr.suburb || addr.village) parts.push(addr.suburb || addr.village);
                                if (addr.city_district || addr.county) parts.push(addr.city_district || addr.county);
                                if (addr.city || addr.town) parts.push(addr.city || addr.town);
                                if (addr.state) parts.push(addr.state);

                                this.resolvedAddress = parts.length > 0 ? parts.join(', ') : data.display_name;
                                this.updatePopup();

                                // Auto-apply to the form field if addressField is set
                                if (this.addressFieldName) {
                                    this.applyAddress();
                                }
                            }
                        } catch (e) {
                            console.warn('Reverse geocoding gagal:', e);
                            this.resolvedAddress = '';
                        } finally {
                            this.reverseLoading = false;
                        }
                    }, 600);
                },

                applyAddress() {
                    if (!this.addressFieldName || !this.resolvedAddress) return;

                    // Find the form input by name attribute
                    const form = this.$el.closest('form');
                    if (form) {
                        const input = form.querySelector(`[name="${this.addressFieldName}"]`);
                        if (input) {
                            input.value = this.resolvedAddress;
                            // Trigger input event for Alpine/other frameworks
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                            this.addressApplied = true;

                            // Reset the applied state after 3 seconds
                            setTimeout(() => { this.addressApplied = false; }, 3000);
                        }
                    }
                },

                clearLocation() {
                    this.lat = '';
                    this.lng = '';
                    this.resolvedAddress = '';
                    this.addressApplied = false;
                    if (this.marker) {
                        this.map.removeLayer(this.marker);
                        this.marker = null;
                    }
                },


            };
        }
    </script>
@endpush
