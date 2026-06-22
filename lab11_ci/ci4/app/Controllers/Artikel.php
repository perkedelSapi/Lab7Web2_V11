<?php

namespace App\Controllers;

use App\Models\ArtikelModel;
use App\Models\KategoriModel;          // ← TAMBAH INI

class Artikel extends BaseController
{
    public function index()
    {
        $model   = new ArtikelModel();
        $artikel = $model->getArtikelDenganKategori();  // ← pakai method join baru
        return view('artikel/index', [
            'title'   => 'Daftar Artikel',
            'artikel' => $artikel,
        ]);
    }

    public function view($slug)
    {
        $model   = new ArtikelModel();
        $artikel = $model->where('slug', $slug)->first();

        if (!$artikel) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Ambil data kategori untuk ditampilkan di detail
        $kategoriModel = new KategoriModel();
        $kategori      = $kategoriModel->find($artikel['id_kategori']);

        $title = $artikel['judul'];
        return view('artikel/detail', compact('artikel', 'title', 'kategori'));
    }

public function admin_index()
{
    $title       = 'Daftar Artikel (Admin)';
    $model       = new ArtikelModel();
    $q           = $this->request->getVar('q') ?? '';
    $kategori_id = $this->request->getVar('kategori_id') ?? '';
    $page        = $this->request->getVar('page') ?? 1;
    $sort        = $this->request->getVar('sort') ?? 'id';
    $order       = $this->request->getVar('order') ?? 'desc';

    // Whitelist kolom yang boleh di-sort (keamanan)
    $allowedSort = ['id', 'judul', 'status', 'created_at', 'nama_kategori'];
    if (!in_array($sort, $allowedSort)) $sort = 'id';
    if (!in_array($order, ['asc', 'desc'])) $order = 'desc';

    $builder = $model->db->table('artikel')
                ->select('artikel.*, kategori.nama_kategori')
                ->join('kategori', 'kategori.id_kategori = artikel.id_kategori', 'left');

    if ($q != '') {
        $builder->like('artikel.judul', $q);
    }

    if ($kategori_id != '') {
        $builder->where('artikel.id_kategori', $kategori_id);
    }

    // Sort — nama_kategori dari tabel kategori, lainnya dari artikel
    $sortCol = ($sort === 'nama_kategori') ? 'kategori.nama_kategori' : 'artikel.' . $sort;
    $builder->orderBy($sortCol, $order);

    $total      = $builder->countAllResults(false);
    $perPage    = 10;
    $offset     = ((int)$page - 1) * $perPage;
    $artikel    = $builder->limit($perPage, $offset)->get()->getResultArray();
    $totalPages = ceil($total / $perPage);

    $data = [
        'title'       => $title,
        'q'           => $q,
        'kategori_id' => $kategori_id,
        'artikel'     => $artikel,
        'total'       => $total,
        'perPage'     => $perPage,
        'page'        => (int)$page,
        'totalPages'  => $totalPages,
        'sort'        => $sort,
        'order'       => $order,
    ];

    if ($this->request->isAJAX()) {
        return $this->response->setJSON($data);
    }

    $kategoriModel    = new \App\Models\KategoriModel();
    $data['kategori'] = $kategoriModel->findAll();

    return view('artikel/admin_index', $data);
}

public function add()
{
    $kategoriModel = new KategoriModel();

    $validation = \Config\Services::validation();
    $validation->setRules(['judul' => 'required']);
    $isDataValid = $validation->withRequest($this->request)->run();

    if ($isDataValid) {
        $file      = $this->request->getFile('gambar');
        $gambarNama = '';

        // Proses upload jika ada file yang dikirim
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $file->move(ROOTPATH . 'public/gambar');
            $gambarNama = $file->getName();
        }

        $model = new ArtikelModel();
        $model->insert([
            'judul'       => $this->request->getPost('judul'),
            'isi'         => $this->request->getPost('isi'),
            'slug'        => url_title($this->request->getPost('judul')),
            'id_kategori' => $this->request->getPost('id_kategori'),
            'gambar'      => $gambarNama,
        ]);
        return redirect()->to('/admin/artikel');
    }

    return view('artikel/form_add', [
        'title'    => 'Tambah Artikel',
        'kategori' => $kategoriModel->findAll(),
    ]);
}

    public function edit($id)
    {
        $model         = new ArtikelModel();
        $kategoriModel = new KategoriModel();

        if ($this->request->getMethod() === 'post' && $this->validate([
            'judul'       => 'required',
            'id_kategori' => 'required|integer',
        ])) {
            $model->update($id, [
                'judul'       => $this->request->getPost('judul'),
                'isi'         => $this->request->getPost('isi'),
                'id_kategori' => $this->request->getPost('id_kategori'),
            ]);
            return redirect()->to('/admin/artikel');
        }

        return view('artikel/form_edit', [
            'title'    => 'Edit Artikel',
            'artikel'  => $model->find($id),
            'kategori' => $kategoriModel->findAll(),
        ]);
    }

    public function delete($id)
    {
        $artikel = new ArtikelModel();
        $artikel->delete($id);
        return redirect('admin/artikel');
    }
}