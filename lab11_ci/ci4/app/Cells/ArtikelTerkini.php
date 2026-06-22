<?php

namespace App\Cells;

use App\Models\ArtikelModel;

class ArtikelTerkini
{
    public function latest(): string
    {
        $model   = new ArtikelModel();
        $artikel = $model->where('status', 1)
                         ->orderBy('created_at', 'DESC')
                         ->limit(5)
                         ->findAll();

        return view('components/artikel_terkini', ['artikel' => $artikel]);
    }
}