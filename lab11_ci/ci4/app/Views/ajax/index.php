<?= $this->include('template/header'); ?>

<h1>Data Artikel (AJAX)</h1>

<button id="btn-tambah" class="btn" style="margin-bottom:12px;">+ Tambah Artikel</button>

<!-- Modal Form -->
<div id="modal-overlay" style="display:none; position:fixed; top:0; left:0;
     width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999;">
    <div style="background:#fff; width:520px; margin:80px auto;
                padding:28px; border-radius:6px; position:relative;">
        <span id="modal-close" style="position:absolute; top:10px; right:16px;
              font-size:22px; cursor:pointer; color:#555;">&times;</span>

        <h2 id="modal-title">Tambah Artikel</h2>

        <form id="form-artikel">
            <input type="hidden" id="artikel-id" value="">
            <p>
                <label>Judul</label><br>
                <input type="text" id="input-judul"
                       style="width:100%; padding:6px; box-sizing:border-box;"
                       placeholder="Judul artikel" required>
            </p>
            <p>
                <label>Isi</label><br>
                <textarea id="input-isi" rows="6"
                          style="width:100%; padding:6px; box-sizing:border-box;"
                          placeholder="Isi artikel"></textarea>
            </p>
            <p id="form-error" style="color:red; display:none;"></p>
            <button type="submit" class="btn" style="background:#3b82f6; color:#fff;">Simpan</button>
            <button type="button" id="btn-batal" class="btn">Batal</button>
        </form>
    </div>
</div>

<!-- Tabel -->
<table id="artikelTable" style="width:100%; border-collapse:collapse; margin-top:10px;">
    <thead>
        <tr style="background:#1e3a5f; color:#fff;">
            <th style="padding:8px;">ID</th>
            <th style="padding:8px;">Judul</th>
            <th style="padding:8px;">Status</th>
            <th style="padding:8px;">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <tr><td colspan="4" style="padding:10px;">Memuat data...</td></tr>
    </tbody>
</table>

<script src="<?= base_url('assets/js/jquery-3.6.0.min.js') ?>"></script>
<script>
$(document).ready(function () {

    $.ajaxSetup({
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    /* ──────────── Modal ──────────── */
    function openModal(title, id, judul, isi) {
        $('#modal-title').text(title);
        $('#artikel-id').val(id    || '');
        $('#input-judul').val(judul || '');
        $('#input-isi').val(isi    || '');
        $('#form-error').hide();
        $('#modal-overlay').fadeIn(200);
    }
    function closeModal() { $('#modal-overlay').fadeOut(200); }

    $('#btn-tambah').on('click', function () { openModal('Tambah Artikel'); });
    $('#modal-close, #btn-batal').on('click', closeModal);

    /* ──────────── Load Data ──────────── */
    function loadData() {
        $('#artikelTable tbody').html(
            '<tr><td colspan="4" style="padding:10px;">Loading...</td></tr>'
        );
        $.ajax({
            url      : "<?= base_url('ajax/getData') ?>",
            method   : 'GET',
            dataType : 'json',
            success  : function (data) {
                if (!data || data.length === 0) {
                    $('#artikelTable tbody').html(
                        '<tr><td colspan="4" style="padding:10px;">Belum ada data.</td></tr>'
                    );
                    return;
                }
                var rows = '';
                $.each(data, function (i, row) {
                    rows += '<tr style="border-bottom:1px solid #ddd;">';
                    rows += '<td style="padding:8px;">' + row.id + '</td>';
                    rows += '<td style="padding:8px;">' + row.judul + '</td>';
                    rows += '<td style="padding:8px;">'
                          + (row.status == 1 ? 'Publish' : 'Draft') + '</td>';
                    rows += '<td style="padding:8px;">';
                    rows += '<a href="#" class="btn-edit" data-id="' + row.id + '"'
                          + ' style="background:#3b82f6;color:#fff;padding:4px 10px;'
                          + 'text-decoration:none;border-radius:4px;margin-right:4px;">Edit</a>';
                    rows += '<a href="#" class="btn-delete" data-id="' + row.id + '"'
                          + ' style="background:#dc2626;color:#fff;padding:4px 10px;'
                          + 'text-decoration:none;border-radius:4px;">Hapus</a>';
                    rows += '</td></tr>';
                });
                $('#artikelTable tbody').html(rows);
            },
            error: function (xhr) {
                $('#artikelTable tbody').html(
                    '<tr><td colspan="4" style="color:red;padding:10px;">'
                    + 'Gagal memuat data. Status: ' + xhr.status + '</td></tr>'
                );
            }
        });
    }

    loadData();

    /* ──────────── Submit Form (Tambah / Edit) ──────────── */
    $('#form-artikel').on('submit', function (e) {
        e.preventDefault();
        var id    = $('#artikel-id').val();
        var judul = $('#input-judul').val().trim();
        var isi   = $('#input-isi').val().trim();

        if (judul === '') {
            $('#form-error').text('Judul tidak boleh kosong.').show();
            return;
        }

        var urlStore  = "<?= base_url('ajax/store') ?>";
        var urlUpdate = "<?= base_url('ajax/update') ?>" + "/";
        var url = id ? urlUpdate + id : urlStore;

        $.ajax({
            url      : url,
            method   : 'POST',
            data     : { judul: judul, isi: isi },
            dataType : 'json',
            success  : function (res) {
                if (res.status === 'OK') {
                    closeModal();
                    loadData();
                } else {
                    $('#form-error').text(res.message || 'Error.').show();
                }
            },
            error: function (xhr) {
                $('#form-error').text('Server error: ' + xhr.status).show();
            }
        });
    });

    /* ──────────── Tombol Edit ──────────── */
    var urlGetOne = "<?= base_url('ajax/getOne') ?>" + "/";
    $(document).on('click', '.btn-edit', function (e) {
        e.preventDefault();
        var id = $(this).data('id');

        $.ajax({
            url      : urlGetOne + id,
            method   : 'GET',
            dataType : 'json',
            success  : function (data) {
                if (!data || !data.judul) {
                    alert('Data tidak ditemukan.');
                    return;
                }
                openModal('Edit Artikel', data.id, data.judul, data.isi);
            },
            error: function (xhr) {
                alert('Gagal ambil data. Status: ' + xhr.status);
            }
        });
    });

    /* ──────────── Tombol Hapus ──────────── */
    var urlDelete = "<?= base_url('ajax/delete') ?>" + "/";
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var id = $(this).data('id');

        if (!confirm('Yakin ingin menghapus artikel ini?')) return;

        $.ajax({
            url      : urlDelete + id,
            method   : 'POST',
            dataType : 'json',
            success  : function (res) {
                if (res.status === 'OK') {
                    loadData();
                } else {
                    alert('Gagal menghapus.');
                }
            },
            error: function (xhr) {
                alert('Error hapus: ' + xhr.status);
            }
        });
    });

});
</script>

<?= $this->include('template/footer'); ?>