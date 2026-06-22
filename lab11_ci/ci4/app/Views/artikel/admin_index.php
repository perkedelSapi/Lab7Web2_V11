<?= $this->include('template/admin_header'); ?>

<h2><?= $title; ?></h2>

<!-- Form Search -->
<div style="margin-bottom:16px;">
    <form id="search-form" style="display:flex; gap:8px; flex-wrap:wrap;">
        <input type="text" name="q" id="search-box"
               value="<?= $q; ?>"
               placeholder="Cari judul artikel..."
               style="padding:6px 10px; border:1px solid #ccc;
                      border-radius:4px; min-width:220px;">

        <select name="kategori_id" id="category-filter"
                style="padding:6px 10px; border:1px solid #ccc; border-radius:4px;">
            <option value="">Semua Kategori</option>
            <?php foreach ($kategori as $k): ?>
                <option value="<?= $k['id_kategori']; ?>"
                    <?= ($kategori_id == $k['id_kategori']) ? 'selected' : ''; ?>>
                    <?= $k['nama_kategori']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit"
                style="padding:6px 16px; background:#1e3a5f; color:#fff;
                       border:none; border-radius:4px; cursor:pointer;">
            Cari
        </button>

        <button type="button" id="btn-reset"
                style="padding:6px 16px; background:#6b7280; color:#fff;
                       border:none; border-radius:4px; cursor:pointer;">
            Reset
        </button>
    </form>
</div>

<!-- Tombol Tambah -->
<a href="<?= base_url('admin/artikel/add'); ?>"
   style="display:inline-block; margin-bottom:12px; padding:6px 16px;
          background:#16a34a; color:#fff; border-radius:4px; text-decoration:none;">
    + Tambah Artikel
</a>

<!-- Info hasil -->
<div id="result-info" style="margin-bottom:8px; font-size:14px; color:#555;"></div>

<!-- Loading -->
<div id="loading" style="display:none; padding:12px; color:#1e3a5f;">
    ⏳ Memuat data...
</div>

<!-- Tabel -->
<div id="table-container">
<table class="table" style="width:100%; border-collapse:collapse;">
    <thead>
        <tr style="background:#1e3a5f; color:#fff;">
            <!-- Header bisa diklik untuk sorting -->
            <th style="padding:10px; cursor:pointer;" class="sort-col" data-col="id">
                ID <span class="sort-icon" data-col="id">↕</span>
            </th>
            <th style="padding:10px; cursor:pointer;" class="sort-col" data-col="judul">
                Judul <span class="sort-icon" data-col="judul">↕</span>
            </th>
            <th style="padding:10px; cursor:pointer;" class="sort-col" data-col="nama_kategori">
                Kategori <span class="sort-icon" data-col="nama_kategori">↕</span>
            </th>
            <th style="padding:10px; cursor:pointer;" class="sort-col" data-col="status">
                Status <span class="sort-icon" data-col="status">↕</span>
            </th>
            <th style="padding:10px; cursor:pointer;" class="sort-col" data-col="created_at">
                Tanggal <span class="sort-icon" data-col="created_at">↕</span>
            </th>
            <th style="padding:10px;">Aksi</th>
        </tr>
    </thead>
    <tbody id="artikel-tbody">
        <?php if (count($artikel) > 0): ?>
            <?php foreach ($artikel as $row): ?>
            <tr style="border-bottom:1px solid #ddd;">
                <td style="padding:10px;"><?= $row['id']; ?></td>
                <td style="padding:10px;">
                    <b><?= $row['judul']; ?></b>
                    <p style="margin:2px 0 0; font-size:12px; color:#666;">
                        <?= substr($row['isi'], 0, 50); ?>...
                    </p>
                </td>
                <td style="padding:10px;"><?= $row['nama_kategori'] ?? '-'; ?></td>
                <td style="padding:10px;"><?= $row['status'] == 1 ? 'Publish' : 'Draft'; ?></td>
                <td style="padding:10px; font-size:12px;"><?= $row['created_at']; ?></td>
                <td style="padding:10px;">
                    <a href="<?= base_url('admin/artikel/edit/' . $row['id']); ?>"
                       style="background:#3b82f6;color:#fff;padding:4px 10px;
                              text-decoration:none;border-radius:4px;margin-right:4px;">
                        Ubah
                    </a>
                    <a href="<?= base_url('admin/artikel/delete/' . $row['id']); ?>"
                       onclick="return confirm('Yakin menghapus data?');"
                       style="background:#dc2626;color:#fff;padding:4px 10px;
                              text-decoration:none;border-radius:4px;">
                        Hapus
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="padding:12px; text-align:center;">
                    Tidak ada data.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<!-- Pagination -->
<div id="pagination-container"
     style="margin-top:16px; display:flex; gap:6px; flex-wrap:wrap;">
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
var urlAdminArtikel = "<?= base_url('admin/artikel') ?>";
var urlEdit         = "<?= base_url('admin/artikel/edit') ?>" + "/";
var urlDelete       = "<?= base_url('admin/artikel/delete') ?>" + "/";

$(document).ready(function () {

    $.ajaxSetup({
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    var currentPage  = 1;
    var currentSort  = "<?= $sort ?>";
    var currentOrder = "<?= $order ?>";

    // Set ikon sort awal
    updateSortIcons(currentSort, currentOrder);

    /* ──────────── Fetch Data ──────────── */
    function fetchData(page) {
        page = page || 1;
        currentPage = page;

        var q           = $('#search-box').val();
        var kategori_id = $('#category-filter').val();

        $('#loading').show();
        $('#table-container').css('opacity', '0.4');

        $.ajax({
            url    : urlAdminArtikel,
            method : 'GET',
            data   : {
                q           : q,
                kategori_id : kategori_id,
                page        : page,
                sort        : currentSort,
                order       : currentOrder
            },
            success: function (res) {
                $('#loading').hide();
                $('#table-container').css('opacity', '1');

                renderTable(res.artikel);
                renderPagination(res.page, res.totalPages, res.total);
                updateSortIcons(currentSort, currentOrder);

                $('#result-info').text(
                    'Menampilkan ' + res.artikel.length
                    + ' dari ' + res.total + ' data'
                    + (q ? ' — pencarian: "' + q + '"' : '')
                    + ' — diurutkan: ' + currentSort + ' (' + currentOrder + ')'
                );
            },
            error: function () {
                $('#loading').hide();
                $('#table-container').css('opacity', '1');
                alert('Gagal memuat data.');
            }
        });
    }

    /* ──────────── Render Tabel ──────────── */
    function renderTable(data) {
        if (!data || data.length === 0) {
            $('#artikel-tbody').html(
                '<tr><td colspan="6" style="padding:12px; text-align:center;">'
                + 'Tidak ada data.</td></tr>'
            );
            return;
        }

        var rows = '';
        $.each(data, function (i, row) {
            var isiPreview = row.isi ? row.isi.substring(0, 50) + '...' : '';
            var kategori   = row.nama_kategori || '-';
            var status     = row.status == 1 ? 'Publish' : 'Draft';
            var tgl        = row.created_at || '-';

            rows += '<tr style="border-bottom:1px solid #ddd;">';
            rows += '<td style="padding:10px;">' + row.id + '</td>';
            rows += '<td style="padding:10px;"><b>' + row.judul + '</b>'
                  + '<p style="margin:2px 0 0;font-size:12px;color:#666;">'
                  + isiPreview + '</p></td>';
            rows += '<td style="padding:10px;">' + kategori + '</td>';
            rows += '<td style="padding:10px;">' + status + '</td>';
            rows += '<td style="padding:10px;font-size:12px;">' + tgl + '</td>';
            rows += '<td style="padding:10px;">';
            rows += '<a href="' + urlEdit + row.id + '"'
                  + ' style="background:#3b82f6;color:#fff;padding:4px 10px;'
                  + 'text-decoration:none;border-radius:4px;margin-right:4px;">Ubah</a>';
            rows += '<a href="' + urlDelete + row.id + '"'
                  + ' onclick="return confirm(\'Yakin menghapus data?\');"'
                  + ' style="background:#dc2626;color:#fff;padding:4px 10px;'
                  + 'text-decoration:none;border-radius:4px;">Hapus</a>';
            rows += '</td></tr>';
        });

        $('#artikel-tbody').html(rows);
    }

    /* ──────────── Render Pagination ──────────── */
    function renderPagination(page, totalPages, total) {
        if (totalPages <= 1) {
            $('#pagination-container').html('');
            return;
        }

        var html = '';

        if (page > 1) {
            html += '<a href="#" class="page-btn" data-page="' + (page - 1) + '"'
                  + ' style="padding:6px 12px;border:1px solid #ccc;border-radius:4px;'
                  + 'text-decoration:none;color:#1e3a5f;">&laquo; Prev</a>';
        }

        for (var i = 1; i <= totalPages; i++) {
            var active = (i === page)
                ? 'background:#1e3a5f;color:#fff;'
                : 'background:#fff;color:#1e3a5f;';
            html += '<a href="#" class="page-btn" data-page="' + i + '"'
                  + ' style="padding:6px 12px;border:1px solid #ccc;border-radius:4px;'
                  + 'text-decoration:none;' + active + '">' + i + '</a>';
        }

        if (page < totalPages) {
            html += '<a href="#" class="page-btn" data-page="' + (page + 1) + '"'
                  + ' style="padding:6px 12px;border:1px solid #ccc;border-radius:4px;'
                  + 'text-decoration:none;color:#1e3a5f;">Next &raquo;</a>';
        }

        $('#pagination-container').html(html);
    }

    /* ──────────── Update Ikon Sort di Header ──────────── */
    function updateSortIcons(sort, order) {
        // Reset semua ikon
        $('.sort-icon').text('↕');
        $('.sort-col').css('background', '#1e3a5f');

        // Set ikon aktif
        var icon = order === 'asc' ? '↑' : '↓';
        $('.sort-icon[data-col="' + sort + '"]').text(icon);
        $('.sort-col[data-col="' + sort + '"]').css('background', '#2d5a8e');
    }

    /* ──────────── Event: Klik Header Sort ──────────── */
    $(document).on('click', '.sort-col', function () {
        var col = $(this).data('col');

        if (currentSort === col) {
            // Toggle asc/desc
            currentOrder = (currentOrder === 'asc') ? 'desc' : 'asc';
        } else {
            currentSort  = col;
            currentOrder = 'asc';
        }

        fetchData(1);
    });

    /* ──────────── Event: Klik Pagination ──────────── */
    $(document).on('click', '.page-btn', function (e) {
        e.preventDefault();
        fetchData($(this).data('page'));
    });

    /* ──────────── Event: Submit Search ──────────── */
    $('#search-form').on('submit', function (e) {
        e.preventDefault();
        fetchData(1);
    });

    /* ──────────── Event: Ganti Kategori ──────────── */
    $('#category-filter').on('change', function () {
        fetchData(1);
    });

    /* ──────────── Event: Reset ──────────── */
    $('#btn-reset').on('click', function () {
        $('#search-box').val('');
        $('#category-filter').val('');
        currentSort  = 'id';
        currentOrder = 'desc';
        fetchData(1);
    });

});
</script>

<?= $this->include('template/admin_footer'); ?>