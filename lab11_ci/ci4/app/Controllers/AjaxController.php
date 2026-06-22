<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\ArtikelModel;

class AjaxController extends Controller
{
    public function index()
    {
        $data = ['title' => 'Data Artikel AJAX'];
        return view('ajax/index', $data);
    }

    public function getData()
    {
        $model = new ArtikelModel();
        $data  = $model->findAll();
        return $this->response->setJSON($data);
    }

    public function getOne($id)
    {
        $model = new ArtikelModel();
        $data  = $model->find($id);

        if ($data) {
            return $this->response->setJSON($data);
        }

        return $this->response->setStatusCode(404)
                               ->setJSON(['status' => 'NOT FOUND']);
    }

    public function store()
    {
        $model = new ArtikelModel();
        $judul = $this->request->getPost('judul');
        $isi   = $this->request->getPost('isi');

        if (empty($judul)) {
            return $this->response->setJSON([
                'status'  => 'ERROR',
                'message' => 'Judul tidak boleh kosong.'
            ]);
        }

        $model->insert([
            'judul' => $judul,
            'isi'   => $isi,
            'slug'  => url_title($judul, '-', true),
        ]);

        return $this->response->setJSON(['status' => 'OK']);
    }

    public function update($id)
    {
        $model = new ArtikelModel();
        $judul = $this->request->getPost('judul');
        $isi   = $this->request->getPost('isi');

        if (empty($judul)) {
            return $this->response->setJSON([
                'status'  => 'ERROR',
                'message' => 'Judul tidak boleh kosong.'
            ]);
        }

        $model->update($id, [
            'judul' => $judul,
            'isi'   => $isi,
            'slug'  => url_title($judul, '-', true),
        ]);

        return $this->response->setJSON(['status' => 'OK']);
    }

    public function delete($id)
    {
        $model = new ArtikelModel();
        $model->delete($id);
        return $this->response->setJSON(['status' => 'OK']);
    }
}