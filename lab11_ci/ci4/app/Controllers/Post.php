<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ArtikelModel;

class Post extends ResourceController
{
    use ResponseTrait;

    // GET /post — Tampilkan semua data
    public function index()
    {
        $model = new ArtikelModel();
        $data['artikel'] = $model->orderBy('id', 'DESC')->findAll();
        return $this->respond($data);
    }

    // POST /post — Tambah data baru
    public function create()
    {
        $model = new ArtikelModel();
        $data  = [
            'judul' => $this->request->getVar('judul'),
            'isi'   => $this->request->getVar('isi'),
            'slug'  => url_title($this->request->getVar('judul'), '-', true),
        ];

        if (empty($data['judul'])) {
            return $this->fail('Judul tidak boleh kosong.', 400);
        }

        $model->insert($data);

        return $this->respondCreated([
            'status'   => 201,
            'error'    => null,
            'messages' => ['success' => 'Data artikel berhasil ditambahkan.']
        ]);
    }

    // GET /post/{id} — Tampilkan data spesifik
    public function show($id = null)
    {
        $model = new ArtikelModel();
        $data  = $model->where('id', $id)->first();

        if ($data) {
            return $this->respond($data);
        }

        return $this->failNotFound('Data tidak ditemukan.');
    }

    // PUT /post/{id} — Update data
    public function update($id = null)
    {
        $model = new ArtikelModel();

        $cek = $model->where('id', $id)->first();
        if (!$cek) {
            return $this->failNotFound('Data tidak ditemukan.');
        }

        $data = [
            'judul' => $this->request->getVar('judul'),
            'isi'   => $this->request->getVar('isi'),
        ];

        if (!empty($data['judul'])) {
            $data['slug'] = url_title($data['judul'], '-', true);
        }

        $model->update($id, $data);

        return $this->respond([
            'status'   => 200,
            'error'    => null,
            'messages' => ['success' => 'Data artikel berhasil diubah.']
        ]);
    }

    // DELETE /post/{id} — Hapus data
    public function delete($id = null)
    {
        $model = new ArtikelModel();
        $data  = $model->where('id', $id)->first();

        if ($data) {
            $model->delete($id);
            return $this->respondDeleted([
                'status'   => 200,
                'error'    => null,
                'messages' => ['success' => 'Data artikel berhasil dihapus.']
            ]);
        }

        return $this->failNotFound('Data tidak ditemukan.');
    }
}