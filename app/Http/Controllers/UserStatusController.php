<?php

namespace App\Http\Controllers;

use App\Models\Userstatus;
use App\Repository\UserStatus\UserStatusRepositoryInterface;
use Illuminate\Http\Request;

class UserStatusController extends Controller
{
    private $userStatusRepository;
    public function __construct(UserStatusRepositoryInterface $userStatusRepository)
    {
        $this->userStatusRepository = $userStatusRepository;
    }

    public function display()
    {
        $result = $this->userStatusRepository->display();
        return $result;
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'toachieve' => 'required|numeric|min:0',
        ]);

        $result = $this->userStatusRepository->create($data);
        return $result;
    }


    public function delete($id)
    {
        $result = $this->userStatusRepository->delete($id);
        return $result;
    }
    public function StatuswithUser($id)
    {
        $result = $this->userStatusRepository->StatuswithUser($id);
        return $result;
    }
}
