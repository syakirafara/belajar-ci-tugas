<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;

class PembelianController extends BaseController
{
    protected $transactionModel;
    protected $transactionDetailModel;

    public function __construct()
    {
        helper(['number']);
        $this->transactionModel = new TransactionModel();
        $this->transactionDetailModel = new TransactionDetailModel();
    }

    public function index()
    {
        // Seluruh data pembelian dari semua user (khusus admin)
        $transactions = $this->transactionModel->orderBy('created_at', 'DESC')->findAll();
        $transactionIds = array_column($transactions, 'id');

        $products = $this->transactionDetailModel->getProductsByTransactionIds($transactionIds);

        return view('v_pembelian', [
            'transactions' => $transactions,
            'products'     => $products,
        ]);
    }

    public function status($id)
    {
        $trx = $this->transactionModel->find($id);

        if (!$trx) {
            return redirect()->to('pembelian')->with('failed', 'Transaksi tidak ditemukan');
        }

        // Toggle status: 0 = Belum Selesai, 1 = Sudah Selesai
        $statusBaru = ((int) $trx['status'] === 1) ? 0 : 1;

        $this->transactionModel->update($id, ['status' => $statusBaru]);

        return redirect()->to('pembelian')->with('success', 'Status pesanan berhasil diperbarui');
    }
}
