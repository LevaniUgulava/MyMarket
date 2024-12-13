<?php

namespace App\Repository\EligibleProduct;

interface EligibleProductRepositoryInterface
{

    public function display($id);
    public function create($statusid, array $id, $discount);
    public function delete($statusid, array $id);
}
