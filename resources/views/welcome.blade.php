<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Pemantauan — Wizard 5 Langkah</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">


    <style>

    </style>
</head>

<body class="bg-light">
    <div class="container my-2 my-md-3">

        <!-- Banner -->


        <div class="card shadow-sm mx-auto" style="max-width: 720px; ">
            <div class="banner"></div>
            <div class="card-body p-3 p-md-4">

                <!-- Stepper -->
                <div class="stepper mb-4" id="stepper"></div>

                <!-- Progress -->
                <div class="progress mb-4" role="progressbar" aria-label="Kemajuan" aria-valuemin="0"
                    aria-valuemax="100">
                    <div class="progress-bar d-none d-sm-flex" id="progressBar" style="width:20%;">Langkah 1 dari 1
                    </div>
                </div>

                <form id="wizardForm" novalidate>
                    <!-- STEP 0: Data Awal (tetap) -->
                    <section class="step" data-step="0" id="step-awal">
                        <!-- Satuan Kerja -->
                        <div class="mb-3">
                            <label for="satker" class="form-label">Satuan Kerja</label>
                            <select class="form-select" id="satker" name="satker" required>
                                <option value="">-- Pilih --</option>
                                <option>Satuan Kerja Balai Wilayah Sungai</option>
                                <option>SNVT PJPA</option>
                                <option>SNVT PJSA</option>
                                <option>Satuan Kerja Operasi dan Pemeliharaan</option>
                                <option>SNVT Pembangunan Bendungan</option>
                            </select>
                            <div class="invalid-feedback">Pilih Satuan Kerja.</div>
                        </div>

                        <!-- PPK -->
                        <div class="mb-3">
                            <label for="ppk" class="form-label">PPK</label>
                            <select class="form-select" id="ppk" name="ppk" required>
                                <option value="">-- Pilih --</option>
                                <option>Irigasi dan Rawa</option>
                                <option>ATAB I</option>
                                <option>ATAB II</option>
                            </select>
                            <div class="invalid-feedback">Pilih PPK.</div>
                        </div>

                        <!-- Paket Pekerjaan (TRIGGER DINAMIS) -->
                        <div class="mb-3">
                            <label for="paket_pekerjaan" class="form-label">Paket Pekerjaan</label>
                            <select class="form-select" id="paket_pekerjaan" name="paket_pekerjaan" required>
                                <option value="">-- Pilih --</option>
                                <option value="paket1" data-items="3" data-nama="Budi Santoso" data-jabatan="Bendahara"
                                    data-lokasi="Manado">
                                    Paket 1 (3 item)
                                </option>
                                <option value="paket2" data-items="5" data-nama="Budi Santoso" data-jabatan="Bendahara"
                                    data-lokasi="Manado">
                                    Paket 2 (5 item)
                                </option>
                                <option value="paket3" data-items="4" data-nama="Budi Santoso" data-jabatan="Bendahara"
                                    data-lokasi="Manado">
                                    Paket 3 (4 item)
                                </option>
                            </select>

                            <div class="invalid-feedback">Pilih Paket Pekerjaan.</div>
                        </div>

                        <!-- Nama & Jabatan -->
                        <div class="mb-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="nama" class="form-label">Nama</label>
                                    <input type="text" class="form-control" id="nama" name="nama"
                                       disabled>

                                </div>
                                <div class="col-md-6">
                                    <label for="jabatan" class="form-label">Jabatan</label>
                                    <input type="text" class="form-control" id="jabatan" name="jabatan"
                                       disabled>

                                </div>
                            </div>
                        </div>

                        <!-- Lokasi & Tanggal -->
                        <div class="mb-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="lokasi" class="form-label">Lokasi</label>
                                    <input type="text" class="form-control" id="lokasi" name="lokasi"
                                        disabled>
                                </div>
                                <div class="col-md-6">
                                    <label for="tanggal" class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" id="tanggal" value="2022-02-22"
                                        disabled>

                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Placeholder untuk langkah dinamis -->
                    <div id="dynamicSteps"></div>

                    <!-- Navigasi -->
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary" id="btnPrev" disabled>←
                            Kembali</button>
                        <button type="submit" class="btn btn-primary" id="btnNext">Lanjut →</button>
                    </div>
                </form>

                <!-- Template 1 Item Pekerjaan -->
                <template id="item-template">
                    <section class="step d-none" data-step="">
                        <h6 class="mb-3">Item Pekerjaan <span class="item-no"></span></h6>

                        <div class="mb-3">
                            <label class="form-label">Item Pekerjaan</label>
                            <select class="form-select item-select" required>
                                <option value="">-- Pilih --</option>
                                <option>Pembersihan Saluran</option>
                                <option>Penggalian</option>
                                <option>Pengecoran</option>
                                <option>Pemasangan Bronjong</option>
                                <option>Pekerjaan Finishing</option>
                            </select>
                            <div class="invalid-feedback">Pilih Item Pekerjaan.</div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Volume</label>
                                <input type="number" min="0" step="0.01" class="form-control vol-input"
                                    required>
                                <div class="invalid-feedback">Isi volume.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Satuan</label>
                                <select class="form-select sat-select" required>
                                    <option value="">-- Pilih --</option>
                                    <option>m</option>
                                    <option>m²</option>
                                    <option>m³</option>
                                    <option>unit</option>
                                    <option>paket</option>
                                </select>
                                <div class="invalid-feedback">Pilih satuan.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea rows="3" class="form-control ket-text"></textarea>
                            <div class="invalid-feedback">Keterangan Wajib Diisi.</div>
                        </div>
                    </section>
                </template>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>
