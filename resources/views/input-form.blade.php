<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Form Action Plan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logopu.png" type="image/png"
        style="width: 50px; aspect-ratio: 1/1; object-fit: contain;">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* ===== Card + Banner ===== */
        .card.hero {
            overflow: hidden;
        }

        /* potong isi mengikuti radius card */

        /* Rasio banner: atur tinggi di sini */
        .ratio-hero {
            --bs-aspect-ratio: 40%;
        }

        /* ~21:8 (lebih tinggi -> angka lebih besar) */
        @media (min-width: 992px) {
            .ratio-hero {
                --bs-aspect-ratio: 32%;
            }

            /* lebih pipih di layar lebar */
        }

        /* Gambar mengisi penuh area banner */
        .hero-img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            /* isi penuh, boleh ter-crop */
            object-position: left center;
            /* fokus ke kiri; ubah ke 'center center' jika perlu */
        }

        /* Alternatif TANPA crop (akan ada ruang kosong/letterbox):
       <img class="hero-img contain"> */
        .hero-img.contain {
            object-fit: contain;
            background: #f1f5f9;
        }

        .stepper {
            display: flex;
            gap: 1rem;
            justify-content: space-between;
            margin-bottom: .75rem
        }

        .step {
            flex: 1;
            text-align: center
        }

        .step .bubble {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid #cbd5e1;
            color: #64748b;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600
        }

        .step .label {
            display: block;
            margin-top: .35rem;
            font-size: .9rem;
            color: #475569;
            min-height: 1.2em
        }

        .step.active .bubble {
            border-color: #2563eb;
            color: #2563eb
        }

        .step.done .bubble {
            border-color: #16a34a;
            background: #16a34a;
            color: #fff
        }

        .progressbar {
            height: 10px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
            margin-bottom: 1rem
        }

        .progressbar>div {
            height: 100%;
            background: #2563eb;
            width: 0%;
            transition: width .25s ease
        }

        /* item cards (row ke bawah) */
        .item-list .item-card {
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            background: #fff;
            padding: .75rem
        }

        .item-list .item-title {
            font-weight: 600;
            margin-bottom: .35rem
        }

        .item-list .form-label {
            font-size: .85rem;
            color: #6b7280
        }
    </style>
</head>

<body class="bg-light">
    <div class="container py-4" style="max-width:720px;">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm hero">
            <div class="ratio ratio-hero">
                <!-- HAPUS 'contain' jika ingin crop agar benar-benar penuh -->
                <img src="{{ asset('images/pemantauan.png') }}" alt="Banner" class="hero-img" />
            </div>
            <div class="p-4">

                {{-- STEPPER --}}
                <div class="stepper" id="stepper">
                    <div class="step active" data-step="1">
                        <span class="bubble">1</span>
                        <span class="label">Data Awal</span>
                    </div>
                </div>
                <div class="progressbar">
                    <div id="progress"></div>
                </div>
                <div class="text-muted small mb-3" id="stepHint">Langkah 1 dari 1</div>

                <form method="POST" action="{{ route('input.store') }}" id="formMain">
                    @csrf

                    {{-- STEP 1: Data Awal (vertikal) --}}
                    <section data-page="1">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Satuan Kerja</label>
                                <select class="form-select" id="satker" name="satker_id" required>
                                    <option value="">-- Pilih --</option>
                                    @foreach ($satkers as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">PPK</label>
                                <select class="form-select" id="ppk" name="ppk_id" required disabled>
                                    <option value="">-- Pilih Satker dulu --</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Paket Pekerjaan</label>
                                <select class="form-select" id="paket" name="package_id" required disabled>
                                    <option value="">-- Pilih Satker & PPK --</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Penyedia Jasa</label>
                                <input class="form-control" id="penyedia_jasa_auto" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Nama Pengawas</label>
                                <input class="form-control" id="nama" name="nama" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Jabatan</label>
                                <input class="form-control" id="jabatan" name="jabatan" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Lokasi</label>
                                <input class="form-control" id="lokasi_auto" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Tanggal</label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal"
                                    value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required>
                            </div>

                        </div>
                    </section>


                    {{-- FOOTER NAV --}}
                    <div class="mt-3 d-flex justify-content-between">
                        <button class="btn btn-outline-secondary" type="button" id="btnPrev" disabled>←
                            Kembali</button>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" type="button" id="btnNext">Lanjut →</button>
                            <button class="btn btn-success d-none" type="submit" id="btnSubmit">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
(() => {
    /* helpers */
    const qs = s => document.querySelector(s);
    const qsa = s => Array.from(document.querySelectorAll(s));
    const show = el => el.classList.remove('d-none');
    const hide = el => el.classList.add('d-none');

    let current = 1;
    let total = 1;
    const progressEl = qs('#progress'),
          stepHint = qs('#stepHint');

    // satuan (server → js)
    const units = @json($units->map(fn($u) => ['id' => $u->id, 'label' => $u->symbol ? "{$u->name} ({$u->symbol})" : $u->name]));
    const unitMap = Object.fromEntries(units.map(u => [String(u.id), u.label])); // id → label

    /* ===============================
       FUNGSI NAVIGASI STEP
    =============================== */
    function goto(step) {
        current = step;
        qsa('section[data-page]').forEach(sec =>
            Number(sec.dataset.page) === step ? show(sec) : hide(sec)
        );
        qsa('.step').forEach(s => {
            const n = Number(s.dataset.step);
            s.classList.toggle('active', n === step);
            s.classList.toggle('done', n < step);
            s.classList.remove('text-danger');
        });

        // update progress bar
        progressEl.style.width = total > 1 ? `${(step - 1) / (total - 1) * 100}%` : '0%';
        stepHint.textContent = `Langkah ${step} dari ${total}`;

        // tombol navigasi
        qs('#btnPrev').disabled = (step === 1);
        if (step === total) {
            hide(qs('#btnNext'));
            show(qs('#btnSubmit'));
        } else {
            show(qs('#btnNext'));
            hide(qs('#btnSubmit'));
        }
    }

    /* ===============================
       VALIDASI PER STEP
    =============================== */
    function validateStep(step) {
        const sec = document.querySelector(`section[data-page="${step}"]`);
        if (!sec) return true;
        const req = Array.from(sec.querySelectorAll('[required]'));
        let ok = true, first = null;

        req.forEach(el => {
            el.classList.remove('is-invalid');
            if (el.readOnly || el.disabled) return; // lewati readonly/disabled
            const empty = (el.tagName === 'SELECT') ? (el.value === '') :
                (el.type === 'number') ? (el.value === '' || isNaN(+el.value)) :
                (el.value.trim() === '');

            // validasi tanggal
            if (el.id === 'tanggal') {
                const today = new Date().toISOString().split('T')[0];
                if (el.value > today) {
                    ok = false;
                    first ??= el;
                    el.classList.add('is-invalid');
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal tidak valid',
                        text: 'Tanggal tidak boleh melebihi hari ini.'
                    });
                }
            }

            if (empty) {
                ok = false;
                first ??= el;
                el.classList.add('is-invalid');
            }
        });

        if (!ok && first) {
            first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            first.focus({ preventScroll: true });
        }

        // tandai step error
        const stepEl = document.querySelector(`.step[data-step="${step}"]`);
        if (stepEl) stepEl.classList.toggle('text-danger', !ok);
        return ok;
    }

    /* ===============================
       CASCADING SELECTS
    =============================== */
    const satkerEl = qs('#satker'),
          ppkEl = qs('#ppk'),
          paketEl = qs('#paket'),
          lokasiEl = qs('#lokasi_auto'),
          penyediaEl = qs('#penyedia_jasa_auto');

    const setPenyedia = (val = '') => {
        if (penyediaEl) penyediaEl.value = val || '';
        const h = qs('#penyedia_jasa_hidden');
        if (h) h.value = penyediaEl?.value || '';
    };

    const clearSelect = (el, ph, dis = true) => {
        el.innerHTML = `<option value="">${ph}</option>`;
        el.disabled = !!dis;
    };
    const fetchJson = async (url) => {
        const r = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!r.ok) throw new Error(url);
        return r.json();
    };
    const fillPaketSelect = (el, list) => {
        clearSelect(el, '-- Pilih Paket --', false);
        list.forEach(it => {
            const o = document.createElement('option');
            o.value = it.id;
            o.textContent = it.nama_paket;
            o.dataset.lokasi = it.lokasi || '';
            o.dataset.penyediaJasa = it.penyedia_jasa || '';
            el.appendChild(o);
        });
    };
    const fillPPKSelect = (el, list) => {
        clearSelect(el, '-- Pilih PPK --', false);
        list.forEach(it => {
            const o = document.createElement('option');
            o.value = it.id;
            o.textContent = it.name;
            el.appendChild(o);
        });
    };

    satkerEl.addEventListener('change', async () => {
        clearSelect(ppkEl, '-- Memuat PPK ...', true);
        clearSelect(paketEl, '-- Pilih Satker & PPK --', true);
        lokasiEl.value = '';
        setPenyedia('');
        resetStepsAfterPackage();

        const sid = satkerEl.value;
        if (!sid) {
            clearSelect(ppkEl, '-- Pilih Satker dulu --', true);
            return;
        }

        try {
            const ppks = await fetchJson(`/api/ppks?satker_id=${sid}`);
            fillPPKSelect(ppkEl, ppks);
        } catch {
            clearSelect(ppkEl, '⚠️ Gagal memuat PPK', true);
        }
    });

    ppkEl.addEventListener('change', async () => {
        clearSelect(paketEl, '-- Memuat Paket ...', true);
        lokasiEl.value = '';
        setPenyedia('');
        resetStepsAfterPackage();

        const sid = satkerEl.value, pid = ppkEl.value;
        if (!sid || !pid) {
            clearSelect(paketEl, '-- Pilih Satker & PPK --', true);
            return;
        }

        try {
            const pkgs = await fetchJson(`/api/packages?satker_id=${sid}&ppk_id=${pid}`);
            fillPaketSelect(paketEl, pkgs);
        } catch {
            clearSelect(paketEl, '⚠️ Gagal memuat Paket', true);
        }
    });

    paketEl.addEventListener('change', async () => {
        lokasiEl.value = '';
        setPenyedia('');
        resetStepsAfterPackage(); // reset step lama

        const pkg = paketEl.value;
        if (!pkg) return;

        const opt = paketEl.selectedOptions[0];
        if (opt) {
            lokasiEl.value = opt.dataset.lokasi || '';
            setPenyedia(opt.dataset.penyediaJasa || '');
        }

        try {
            const cats = await fetchJson(`/api/job-cats?package_id=${pkg}`);
            renderStepsForCategories(cats, pkg);
        } catch (e) {
            console.error(e);
        }
    });

    /* ===============================
       STEP DINAMIS
    =============================== */
    function resetStepsAfterPackage() {
        const stepper = qs('#stepper');
        stepper.innerHTML = `
            <div class="step active" data-step="1">
                <span class="bubble">1</span>
                <span class="label">Data Awal</span>
            </div>
        `;
        qsa('section[data-page]').forEach((s, i) => { if (i > 0) s.remove(); });
        total = 1;
        goto(1);
    }

    function renderStepsForCategories(cats, pkgId) {
        if (!cats.length) {
            Swal.fire({
                icon: 'info',
                title: 'Tidak ada kategori',
                text: 'Paket ini belum memiliki kategori pekerjaan.'
            });
            return;
        }

        const stepper = qs('#stepper');
        const form = qs('#formMain');
        total = 1 + cats.length;

        cats.forEach((cat, i) => {
            const stepNum = i + 2;
            const step = document.createElement('div');
            step.className = 'step';
            step.dataset.step = stepNum;
            step.innerHTML = `
                <span class="bubble">${stepNum}</span>
                <span class="label">${cat.name}</span>
            `;
            stepper.appendChild(step);

            const sec = document.createElement('section');
            sec.dataset.page = stepNum;
            sec.classList.add('d-none');
            sec.innerHTML = `<div id="bodyStep${stepNum}"></div>`;
            form.insertBefore(sec, qs('.mt-3'));
        });

        cats.forEach((cat, i) => {
            const body = qs(`#bodyStep${i + 2}`);
            renderCategoryFixed(body, cat, i, pkgId);
        });

        goto(1);
    }

    function renderCategoryFixed(container, cat, idx, pkgId) {
        container.innerHTML = `
            <div class="mb-2"><h2 class="h6 mb-2">Jenis: ${cat.name}</h2></div>
            <input type="hidden" name="details[${idx}][category_id]" value="${cat.id}">
            <div class="item-list" data-list></div>
            <div class="alert alert-info d-none mt-2" data-empty>Belum ada item pekerjaan untuk jenis ini.</div>
        `;
        const list = container.querySelector('[data-list]'),
              empty = container.querySelector('[data-empty]');

        fetchJson(`/api/items?package_id=${pkgId}&job_category_id=${cat.id}`)
            .then(items => {
                if (!items.length) {
                    empty.classList.remove('d-none');
                    return;
                }
                items.forEach((it, rowIdx) => {
                    const unitId = it.default_unit_id ? String(it.default_unit_id) : "";
                    const unitText = unitId ? (unitMap[unitId] ?? "") : "";
                    const card = document.createElement('div');
                    card.className = 'item-card mb-2';
                    card.innerHTML = `
                        <div class="item-title">${it.name}</div>
                        <input type="hidden" name="details[${idx}][rows][${rowIdx}][item_id]" value="${it.id}">
                        <div class="row g-2">
                          <div class="col-12 col-md-4">
                            <label class="form-label">Volume</label>
                            <input type="number" step="0.0001" min="0" class="form-control"
                                   name="details[${idx}][rows][${rowIdx}][volume]" required>
                          </div>
                          <div class="col-12 col-md-4 col-lg-4">
                            <label class="form-label">Satuan</label>
                            <input class="form-control" name="details[${idx}][rows][${rowIdx}][satuan_label]"
                                   value="${unitText}" readonly required>
                            <input type="hidden" name="details[${idx}][rows][${rowIdx}][satuan_id]"
                                   value="${unitId}">
                            ${unitId ? "" : `<div class="text-danger small mt-1">Satuan default belum diset.</div>`}
                          </div>
                          <div class="col-12 col-md-5 col-lg-4">
                            <label class="form-label">Keterangan</label>
                            <input class="form-control" name="details[${idx}][rows][${rowIdx}][keterangan]" placeholder="Opsional">
                          </div>
                        </div>`;
                    list.appendChild(card);
                });
            })
            .catch(e => {
                console.error(e);
                empty.textContent = 'Gagal memuat item';
                empty.classList.remove('d-none');
            });
    }

    /* ===============================
       EVENT NAVIGASI
    =============================== */
    document.getElementById('btnPrev').addEventListener('click', () => goto(Math.max(1, current - 1)));
    document.getElementById('btnNext').addEventListener('click', () => {
        if (!validateStep(current)) return;
        goto(Math.min(total, current + 1));
    });

    // step click (delegasi agar dynamic step juga aktif)
    document.getElementById('stepper').addEventListener('click', (e) => {
        const stepEl = e.target.closest('.step');
        if (!stepEl) return;
        const t = Number(stepEl.dataset.step);
        if (isNaN(t)) return;
        if (t < current) goto(t);
        else if (t > current && current === 1) document.getElementById('btnNext').click();
    });

    // validasi akhir submit
    document.getElementById('formMain').addEventListener('submit', (e) => {
        for (let s = 1; s <= total; s++) {
            if (!validateStep(s)) {
                e.preventDefault();
                goto(s);
                Swal.fire({
                    icon: 'warning',
                    title: 'Form belum lengkap',
                    text: `Periksa kembali langkah ${s}.`
                });
                return;
            }
        }
        if (document.querySelectorAll('.item-card').length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Belum ada Item',
                text: 'Paket ini belum memiliki item pekerjaan.'
            });
        }
    });

    // init
    goto(1);

    @if (session('status'))
        Swal.fire({
            icon: 'success',
            title: 'Sukses',
            text: @json(session('status'))
        });
    @endif
})();
</script>


</body>

</html>
