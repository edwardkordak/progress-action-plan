(function () {
    // ====== DOM refs ======
    const form = document.getElementById("wizardForm");
    const progressBar = document.getElementById("progressBar");
    const btnPrev = document.getElementById("btnPrev");
    const btnNext = document.getElementById("btnNext");
    const stepperEl = document.getElementById("stepper");
    const selectPaket = document.getElementById("paket_pekerjaan");

    const dynamicWrap = document.getElementById("dynamicSteps");
    const itemTpl = document.getElementById("item-template");

    // Auto-fill refs
    const namaEl = document.getElementById("nama");
    const jabatanEl = document.getElementById("jabatan");
    const lokasiEl = document.getElementById("lokasi");
    const tanggalEl = document.getElementById("tanggal");

    // ====== Default config ======
    const paketConfig = { paket1: 3, paket2: 5, paket3: 4 };

    const DEFAULTS = {
        nama: "Budi Santoso",
        jabatan: "Bendahara",
        lokasi: "Manado",
    };

    // ====== State ======
    let steps = []; // array of active step sections (step0 + dynamic items)
    let current = 0; // active step index
    let step0 = document.querySelector('.step[data-step="0"]');

    // ====== Helpers ======
    function formatDateForInput(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, "0");
        const d = String(date.getDate()).padStart(2, "0");
        return `${y}-${m}-${d}`;
    }

    function getItemsCountFromSelect() {
        const opt = selectPaket?.selectedOptions?.[0];
        if (!opt) return 0;
        const dataItems = opt.getAttribute("data-items");
        if (dataItems && !isNaN(parseInt(dataItems)))
            return parseInt(dataItems);
        const val = selectPaket.value;
        if (paketConfig[val]) return paketConfig[val];
        const m = (opt.textContent || "").match(/(\d+)\s*item/i);
        if (m) return parseInt(m[1]);
        return 0;
    }

    // --- Utility: lock + mirror agar tetap terkirim ---
    function setLockedValue(inputEl, name, value) {
        if (!inputEl) return;
        // set tampilan
        inputEl.value = value;
        inputEl.disabled = true; // benar-benar tidak bisa diubah
        inputEl.classList.remove("is-invalid");
        inputEl.classList.add("is-valid");

        // cari / buat hidden mirror agar value terkirim
        const form = inputEl.form;
        let mirror = form.querySelector(
            `input[type="hidden"][data-mirror="${name}"]`
        );
        if (!mirror) {
            mirror = document.createElement("input");
            mirror.type = "hidden";
            mirror.setAttribute("data-mirror", name);
            // penting: name harus sama dengan field aslinya
            mirror.name = name;
            form.appendChild(mirror);
        }
        mirror.value = value;
    }

    function autofillFromOption(opt) {
        const DEFAULTS = {
            nama: "Budi Santoso",
            jabatan: "Bendahara",
            lokasi: "Manado",
        };
        const nama = opt.getAttribute("data-nama") || DEFAULTS.nama;
        const jab = opt.getAttribute("data-jabatan") || DEFAULTS.jabatan;
        const lokasi = opt.getAttribute("data-lokasi") || DEFAULTS.lokasi;

        // format tanggal hari ini (YYYY-MM-DD)
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, "0");
        const d = String(now.getDate()).padStart(2, "0");
        const tanggal = `${y}-${m}-${d}`;

        // kunci + mirror (disabled utk UI, hidden utk submit)
        lockIdentityFields({ nama, jabatan: jab, lokasi, tanggal });
    }

    // panggil ini setelah autofill paket
    function lockIdentityFields(values) {
        // values: { nama, jabatan, lokasi, tanggal }
        setLockedValue(document.getElementById("nama"), "nama", values.nama);
        setLockedValue(
            document.getElementById("jabatan"),
            "jabatan",
            values.jabatan
        );
        setLockedValue(
            document.getElementById("lokasi"),
            "lokasi",
            values.lokasi
        );
        setLockedValue(
            document.getElementById("tanggal"),
            "tanggal",
            values.tanggal
        );
    }

    // Buat ulang N item step dari <template>, beri name agar FormData rapi
    function buildDynamicItems(n) {
        dynamicWrap.innerHTML = "";
        const created = [];
        for (let i = 0; i < n; i++) {
            const node = itemTpl.content.firstElementChild.cloneNode(true);
            node.dataset.step = String(i + 1); // 1..N sesudah step0
            node.classList.add("d-none");
            node.querySelector(".item-no").textContent = i + 1;

            // Beri name untuk setiap input/select supaya ikut terkirim
            const selItem = node.querySelector(".item-select");
            const vol = node.querySelector(".vol-input");
            const sat = node.querySelector(".sat-select");
            const ket = node.querySelector(".ket-text");

            if (selItem) selItem.name = `items[${i}][nama]`;
            if (vol) vol.name = `items[${i}][volume]`;
            if (sat) sat.name = `items[${i}][satuan]`;
            if (ket) ket.name = `items[${i}][keterangan]`;

            dynamicWrap.appendChild(node);
            created.push(node);
        }
        return created;
    }

    function rebuildActiveSteps() {
        const count = getItemsCountFromSelect();
        const items = buildDynamicItems(count);
        steps = [step0, ...items];
    }

    function renderStepper() {
        const total = steps.length || 1;
        const html = [];

        html.push(`
      <div class="step-item ${current === 0 ? "active" : ""}" data-step="0">
        <div class="step-dot">1</div>
        <div class="label mt-2">Data Awal</div>
      </div>
    `);

        for (let i = 1; i < total; i++) {
            const active = current === i ? "active" : "";
            const done = current > i ? "done" : "";
            html.push(`
        <div class="step-item ${active} ${done}" data-step="${i}">
          <div class="step-dot">${i + 1}</div>
          <div class="label mt-2">Item ${i}</div>
        </div>
      `);
        }
        stepperEl.innerHTML = html.join("");
    }

    function updateProgress() {
        const total = steps.length || 1;
        const pct = ((current + 1) / total) * 100;
        progressBar.style.width = pct + "%";
        progressBar.textContent = `Langkah ${current + 1} dari ${total}`;
        progressBar.classList.remove("d-none");
    }

    function showStep(idx) {
        // Sembunyikan semua
        document
            .querySelectorAll(".step")
            .forEach((s) => s.classList.add("d-none"));
        // Tampilkan hanya steps aktif
        steps.forEach((s, i) => s.classList.toggle("d-none", i !== idx));

        // Update stepper states
        Array.from(stepperEl.querySelectorAll(".step-item")).forEach(
            (el, i) => {
                el.classList.remove("active", "done");
                if (i < idx) el.classList.add("done");
                if (i === idx) el.classList.add("active");
            }
        );

        // Tombol
        btnPrev.disabled = idx === 0;
        btnNext.textContent = idx === steps.length - 1 ? "Submit" : "Lanjut →";

        updateProgress();
    }

    function validateCurrentStep() {
        const active = steps[current];
        const inputs = Array.from(
            active.querySelectorAll("input, select, textarea")
        ).filter((el) => !el.disabled);
        let valid = true;
        inputs.forEach((el) => {
            if (!el.checkValidity()) {
                el.classList.add("is-invalid");
                valid = false;
            } else {
                el.classList.remove("is-invalid");
                el.classList.add("is-valid");
            }
        });
        return valid;
    }

    // ====== Events ======
    if (selectPaket) {
        selectPaket.addEventListener("change", () => {
            const opt = selectPaket.selectedOptions[0];
            if (!opt || !opt.value) {
                // reset ke 0 item kalau "-- Pilih --"
                dynamicWrap.innerHTML = "";
                steps = [step0];
                current = 0;
                renderStepper();
                showStep(0);
                return;
            }
            // autofill (sesuai permintaanmu)
            autofillFromOption(opt);

            // Rebuild steps berdasarkan paket
            rebuildActiveSteps();
            current = 0;
            renderStepper();
            showStep(current);
        });
    }

    btnPrev.addEventListener("click", () => {
        if (current > 0) {
            current--;
            showStep(current);
        }
    });

    // Gunakan submit untuk "Next" & submit beneran di akhir
    form.addEventListener("submit", (e) => {
        const lastIndex = steps.length - 1;

        // Kalau belum di step terakhir, treat as Next
        if (current < lastIndex) {
            e.preventDefault();
            if (validateCurrentStep()) {
                current++;
                showStep(current);
                const first = steps[current].querySelector(
                    "input, select, textarea"
                );
                if (first) first.focus();
            }
            return;
        }

        // Step terakhir = validasi & submit
        if (!validateCurrentStep()) {
            e.preventDefault();
            return;
        }

        // Demo submit (silakan hapus preventDefault saat ready POST ke server)
        e.preventDefault();
        const data = new FormData(form);
        const obj = {};
        data.forEach((v, k) => (obj[k] = v));
        console.log("Data terkirim:", obj);
        alert(
            "Form terkirim! (cek console). Ganti handler submit untuk POST ke server."
        );
    });

    form.addEventListener("input", (e) => {
        const el = e.target;
        if (el.classList.contains("is-invalid") && el.checkValidity()) {
            el.classList.remove("is-invalid");
            el.classList.add("is-valid");
        }
    });

    // ====== INIT ======
    // Awal: belum pilih paket → hanya step0
    steps = [step0];
    current = 0;
    renderStepper();
    showStep(0);

    // Kalau user sudah memilih paket sebelumnya (mis. saat reload)
    if (selectPaket && selectPaket.value) {
        const opt = selectPaket.selectedOptions[0];
        autofillFromOption(opt);
        rebuildActiveSteps();
        renderStepper();
        showStep(0);
    }
})();
