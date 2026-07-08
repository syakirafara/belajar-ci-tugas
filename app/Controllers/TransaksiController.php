<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\RajaOngkirService;

use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;

class TransaksiController extends BaseController
{
    protected $cart;
    protected $transactionModel;
    protected $transactionDetailModel;

    public function __construct()
    {
        helper(['number', 'form', 'diskon']);
        $this->cart = service('cart');
        $this->transactionModel = new TransactionModel();
        $this->transactionDetailModel = new TransactionDetailModel();
    }
    public function index()
    {
        $items  = $this->cart->contents();
        $diskon = diskon_hari_ini();

        // total dihitung dari harga yang sudah dipotong diskon hari ini
        $total = 0;
        foreach ($items as $item) {
            $total += harga_diskon($item['price']) * $item['qty'];
        }

        return view('v_keranjang', [
            'items'  => $items,
            'diskon' => $diskon,
            'total'  => $total,
        ]);
    }

    public function cart_add()
    {
        $this->cart->insert([
            'id' => $this->request->getPost('id'),
            'qty' => 1,
            'price' => $this->request->getPost('harga'),
            'name' => $this->request->getPost('nama'),
            'options' => [
                'foto' => $this->request->getPost('foto')
            ]
        ]);

        session()->setFlashdata(
            'success',
            'Produk berhasil ditambahkan ke keranjang. 
	    <a href="' . base_url('keranjang') . '">Lihat</a>'
        );

        return redirect()->to(base_url('/'));
    }

    public function cart_edit()
    {
        $i = 1;
        foreach ($this->cart->contents() as $item) {
            $qty = $this->request->getPost('qty' . $i++);

            $this->cart->update([
                'rowid' => $item['rowid'],
                'qty' => $qty
            ]);
        }

        session()->setFlashdata(
            'success',
            'Keranjang berhasil diperbarui'
        );

        return redirect()->to(base_url('keranjang'));
    }
    public function cart_delete($rowid)
    {
        $this->cart->remove($rowid);

        session()->setFlashdata(
            'success',
            'Produk berhasil dihapus dari keranjang'
        );

        return redirect()->to(base_url('keranjang'));
    }
    public function cart_clear()
    {
        $this->cart->destroy();

        session()->setFlashdata(
            'success',
            'Keranjang berhasil dikosongkan'
        );

        return redirect()->to(base_url('keranjang'));
    }
    public function checkout()
    {
        $items  = $this->cart->contents();
        $diskon = diskon_hari_ini();

        // subtotal (belum termasuk ongkir) memakai harga setelah diskon
        $total = 0;
        foreach ($items as $item) {
            $total += harga_diskon($item['price']) * $item['qty'];
        }

        return view('v_checkout', [
            'items'  => $items,
            'diskon' => $diskon,
            'total'  => $total,
        ]);
    }
    public function destinations()
    {
        $search = $this->request->getGet('q');

        if (empty($search)) {
            return $this->response->setJSON([
                'results' => []
            ]);
        }

        $service = new RajaOngkirService();
        $response = $service->getDestination($search);

        $results = [];
        $data = $response['data'] ?? [];

        foreach ($data as $item) {
            $results[] = [
                'id' => $item['id'],
                'text' => $item['label']
            ];
        }

        return $this->response->setJSON([
            'results' => $results
        ]);
    }

    public function costs()
    {
        $origin = '64999';
        $destination = $this->request->getGet('destination');
        $weight = '1000';
        $courier = 'jne';

        $service = new RajaOngkirService();
        $response = $service->getCost($origin, $destination, $weight, $courier);

        $results = [];
        $data = $response['data'] ?? [];

        foreach ($data as $item) {
            $results[] = [
                'service' => $item['service'],
                'description' => $item['description'],
                'cost' => $item['cost'],
                'etd' => $item['etd']
            ];
        }

        return $this->response->setJSON($results);
    }

    public function buy()
    {
        $cartItems = $this->cart->contents();

        if (empty($cartItems)) {
            return redirect()->back();
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $diskon = diskon_hari_ini();

        // subtotal memakai harga setelah dipotong diskon yang berlaku hari ini
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += harga_diskon($item['price']) * $item['qty'];
        }

        $ongkir = (int) $this->request->getPost('ongkir');

        $transaction = [
            'username' => $this->request->getPost('username'),
            'alamat' => $this->request->getPost('alamat'),
            'ongkir' => $ongkir,
            'total_harga' => $subtotal + $ongkir,
            'status' => 0,
        ];

        // insert transaction
        if (!$this->transactionModel->insert($transaction)) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Gagal membuat transaksi');
        }

        $transactionId = $this->transactionModel->getInsertID();

        // insert transaction detail
        foreach ($cartItems as $item) {
            $this->transactionDetailModel->insert([
                'transaction_id' => $transactionId,
                'product_id' => $item['id'],
                'jumlah' => $item['qty'],
                'diskon' => $diskon, // nominal diskon per item yang berlaku
                'subtotal_harga' => harga_diskon($item['price']) * $item['qty']
            ]);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->back()->with('error', 'Gagal membuat transaksi');
        }

        //hapus session keranjang belanja 
        $this->cart->destroy();
        return redirect()->to(base_url());
    }

    public function history()
    {
        $username = session()->get('username');

        $transactions = $this->transactionModel->where('username', $username)->findAll();
        $transactionIds = array_column($transactions, 'id');

        $products = $this->transactionDetailModel->getProductsByTransactionIds($transactionIds);

        $data = [
            'username' => $username,
            'transactions' => $transactions,
            'products' => $products
        ];

        return view('v_history', $data);
    }
}
