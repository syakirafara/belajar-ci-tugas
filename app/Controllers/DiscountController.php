<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DiscountModel;

class DiscountController extends BaseController
{
    protected $discountModel;

    public function __construct()
    {
        helper(['form', 'number']);
        $this->discountModel = new DiscountModel();
    }

    public function index()
    {
        return view('diskon/index', [
            'discounts' => $this->discountModel->orderBy('tanggal', 'ASC')->findAll()
        ]);
    }

    public function create()
    {
        // Validation: tidak boleh menambahkan diskon untuk tanggal yang sama
        $rules = [
            'tanggal' => [
                'label' => 'tanggal',
                'rules' => 'required|valid_date[Y-m-d]|is_unique[discount.tanggal]'
            ],
            'nominal' => [
                'label' => 'nominal',
                'rules' => 'required|numeric'
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('diskon')->with('errors', $this->validator->getErrors());
        }

        $this->discountModel->insert([
            'tanggal' => $this->request->getPost('tanggal'),
            'nominal' => $this->request->getPost('nominal'),
        ]);

        return redirect()->to('diskon')->with('success', 'Data diskon berhasil ditambahkan');
    }

    public function update($id)
    {
        $diskon = $this->discountModel->find($id);

        if (!$diskon) {
            return redirect()->to('diskon')->with('failed', 'Data diskon tidak ditemukan');
        }

        // Tanggal dibuat readonly di form -> hanya nominal yang boleh diubah
        $rules = [
            'nominal' => [
                'label' => 'nominal',
                'rules' => 'required|numeric'
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('diskon')->with('errors', $this->validator->getErrors());
        }

        $this->discountModel->update($id, [
            'nominal' => $this->request->getPost('nominal'),
        ]);

        return redirect()->to('diskon')->with('success', 'Data diskon berhasil diubah');
    }

    public function delete($id)
    {
        $diskon = $this->discountModel->find($id);

        if (!$diskon) {
            return redirect()->to('diskon')->with('failed', 'Data diskon tidak ditemukan');
        }

        $this->discountModel->delete($id);

        return redirect()->to('diskon')->with('success', 'Data diskon berhasil dihapus');
    }
}
