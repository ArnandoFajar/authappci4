<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModel;

class Login extends BaseController
{
    public function index()
    {
        if (session()->get('logged_in')) {
            return redirect()->to(base_url('/'));
        }
        return view('login');
    }

    public function process()
    {
        $users = new UsersModel();

        //get input User
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        //collect data user exist
        $dataUser = $users->where([
            'email' => $email,
        ])->first();

        session()->set('loginattempt', 0);

        if ($dataUser) {
            if ($dataUser['active'] == 0) {
                session()->setFlashdata('error', 'Akun Anda Sudah dinonaktifkan karena salah password 3 kali mohon hubungi admin');
                return redirect()->back();
            } elseif (password_verify($password, $dataUser['password'])) {
                session()->set([
                    'email' => $dataUser['email'],
                    'name' => $dataUser['name'],
                    'logged_in' => TRUE
                ]);
                //mengosongkan login attempt
                //menggunakan DB
                $users->update($dataUser['id'], ['loginattempt' => 0]);
                //menggunakan session
                session()->set('loginattempt', 0);
                return redirect()->to(base_url('/'));
            } else {
                //jika user gagal password 3 kali maka dinonaktifkan

                //login attempt dimulai dari 0 menggunakan DB
                if ($dataUser['loginattempt'] == 2) {
                    $nonaktifkan = [
                        'active' => 0
                    ];
                    $users->update($dataUser['id'], $nonaktifkan);
                } else {
                    $loginAttempt = [
                        'loginattempt' => $dataUser['loginattempt'] + 1
                    ];
                    $users->update($dataUser['id'], $loginAttempt);
                }
                //menggunakan session
                session()->set('loginattempt', session('loginattempt') + 1);
                session()->setFlashdata('error', 'Username & Password Salah');
                return redirect()->back();
            }
        } else {
            session()->setFlashdata('error', 'Username & Password Salah');
            return redirect()->back();
        }
    }
    function logout()
    {
        session()->destroy();
        return redirect()->to('login');
    }
}
