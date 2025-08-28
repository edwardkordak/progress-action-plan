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

    // ====== Config ======
    const paketConfig = { paket1: 3, paket2: 5, paket3: 4 };
    const DEFAULTS = {
        nama: "Budi Santoso",
        jabatan: "Bendahara",
        lokasi: "Manado",
    };

    // ====== State ======
    let steps = []; // step0 + dynamic items
    let current = 0; // index step aktif
    let step0 = document.querySelector('.step[data-step="0"]');

    // ====== Helpers ======
    const isPaketSelected = () => !!(selectPaket && selectPaket.value);

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

        // set tampilan (dikunci)
        inputEl.value = value;
        inputEl.disabled = true;
        inputEl.classList.remove("is-invalid");
        inputEl.classList.add("is-valid");

        // buat/cari hidden mirror agar value terkirim saat submit
        const f = inputEl.form;
        let mirror = f.querySelector(
            `input[type="hidden"][data-mirror="${name}"]`
        );
        if (!mirror) {
            mirror = document.createElement("input");
            mirror.type = "hidden";
            mirror.name = name; // penting: name sama dengan field asli
            mirror.setAttribute("data-mirror", name);
            f.appendChild(mirror);
        }
        mirror.value = value;
    }

    function removeMirrors(names) {
        names.forEach((n) => {
            const m = form.querySelector(
                `input[type="hidden"][data-mirror="${n}"]`
            );
            if (m) m.remove();
        });
    }

    function unlockAndClearIdentityFields() {
        [namaEl, jabatanEl, lokasiEl, tanggalEl].forEach((el) => {
            if (!el) return;
            el.disabled = false;
            el.value = "";
            el.classList.remove("is-valid", "is-invalid");
        });
        removeMirrors(["nama", "jabatan", "lokasi", "tanggal"]);
    }

    function lockIdentityFields(values) {
        setLockedValue(namaEl, "nama", values.nama);
        setLockedValue(jabatanEl, "jabatan", values.jabatan);
        setLockedValue(lokasiEl, "lokasi", values.lokasi);
        setLockedValue(tanggalEl, "tanggal", values.tanggal);
    }

    function autofillFromOption(opt) {
        const nama = opt.getAttribute("data-nama") || DEFAULTS.nama;
        const jab = opt.getAttribute("data-jabatan") || DEFAULTS.jabatan;
        const lokasi = opt.getAttribute("data-lokasi") || DEFAULTS.lokasi;
        const tanggal = formatDateForInput(new Date());
        lockIdentityFields({ nama, jabatan: jab, lokasi, tanggal });
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

            // nama field agar terkirim
            node.querySelector(".item-select").name = `items[${i}][nama]`;
            node.querySelector(".vol-input").name = `items[${i}][volume]`;
            node.querySelector(".sat-select").name = `items[${i}][satuan]`;
            node.querySelector(".ket-text").name = `items[${i}][keterangan]`;

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

    function refreshNextButtonLabel() {
        if (current === 0 && !isPaketSelected()) {
            btnNext.textContent = "Pilih paket";
            return;
        }
        btnNext.textContent =
            current === steps.length - 1 ? "Submit" : "Lanjut →";
    }

    function showStep(idx) {
        document
            .querySelectorAll(".step")
            .forEach((s) => s.classList.add("d-none"));
        steps.forEach((s, i) => s.classList.toggle("d-none", i !== idx));

        Array.from(stepperEl.querySelectorAll(".step-item")).forEach(
            (el, i) => {
                el.classList.remove("active", "done");
                if (i < idx) el.classList.add("done");
                if (i === idx) el.classList.add("active");
            }
        );

        btnPrev.disabled = idx === 0;
        refreshNextButtonLabel();
        updateProgress();
    }

    function validateCurrentStep() {
        // Khusus step0: wajib pilih paket dulu
        if (current === 0 && !isPaketSelected()) {
            // tandai paket invalid & fokus
            selectPaket.classList.add("is-invalid");
            selectPaket.focus();
            return false;
        }

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
            selectPaket.classList.remove("is-invalid");

            const opt = selectPaket.selectedOptions[0];
            if (!opt || !opt.value) {
                // reset: kosongkan identitas + hapus item steps
                unlockAndClearIdentityFields();
                dynamicWrap.innerHTML = "";
                steps = [step0];
                current = 0;
                renderStepper();
                showStep(0);
                return;
            }

            // paket dipilih → autofill & lock
            autofillFromOption(opt);

            // buat ulang item steps
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

    // Next/Submit
    form.addEventListener("submit", (e) => {
        const lastIndex = steps.length - 1;

        // Belum di step terakhir → treat as Next
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

        // Step terakhir → validasi lalu submit
        if (!validateCurrentStep()) {
            e.preventDefault();
            return;
        }

        // Demo submit
        e.preventDefault();
        const data = new FormData(form);
        const obj = {};
        data.forEach((v, k) => (obj[k] = v));
        console.log("Data terkirim:", obj);

        Swal.fire({
            title: "Berhasil!",
            text: "Form berhasil terkirim",
            icon: "success",
            confirmButtonText: "OK",
            confirmButtonColor: "#3085d6",
        });
    });

    form.addEventListener("input", (e) => {
        const el = e.target;
        if (el.classList.contains("is-invalid") && el.checkValidity()) {
            el.classList.remove("is-invalid");
            el.classList.add("is-valid");
        }
    });

    // ====== INIT ======
    steps = [step0];
    current = 0;
    renderStepper();
    showStep(0);

    // Jika halaman reload dan paket sudah terisi, rebuild
    if (isPaketSelected()) {
        const opt = selectPaket.selectedOptions[0];
        autofillFromOption(opt);
        rebuildActiveSteps();
        renderStepper();
        showStep(0);
    }
})();
