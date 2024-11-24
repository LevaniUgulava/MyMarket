<?php

namespace App\Http\Controllers;

use App\Repository\EligibleProduct\EligibleProductRepositoryInterface;
use Illuminate\Http\Request;

class EligibleProductController extends Controller
{
    private $eligibleproductrepository;
    public function __construct(EligibleProductRepositoryInterface $eligibleproductrepository)
    {
        $this->eligibleproductrepository = $eligibleproductrepository;
    }

    public function display($id)
    {
        $result = $this->eligibleproductrepository->display($id);
        return $result;
    }
    public function create($id, Request $request)
    {
        $data = $request->validate([
            'id' => 'required|array'
        ]);
        $result = $this->eligibleproductrepository->create($id, $data);
        return $result;
    }
    public function delete($id, Request $request)
    {
        $data = $request->validate([
            'id' => 'required|array'
        ]);
        $result = $this->eligibleproductrepository->delete($id, $data);
        return $result;
    }
}
