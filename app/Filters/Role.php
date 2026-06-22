<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Role implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Jika user belum login, arahkan ke halaman Login
        if (!session()->has('isLoggedIn')) {
            return redirect()->to(site_url('login'));
        }

        // Jika sudah login namun role-nya admin, arahkan ke Home
        if (session()->get('role') == 'admin') {
            return redirect()->to(site_url('/'));
        }

        // Selain itu (role guest) diijinkan mengakses
    }

    //--------------------------------------------------------------------

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
