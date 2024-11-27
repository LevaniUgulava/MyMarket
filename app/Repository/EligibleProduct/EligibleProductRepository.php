<?php

namespace App\Repository\EligibleProduct;

use App\Models\Eligibleproduct;
use App\Models\Userstatus;

class EligibleProductRepository implements EligibleProductRepositoryInterface
{

    public function display($id)
    {
        try {
            $userStatus = Userstatus::with('eligibleProducts')->findorfail($id);
            if (!$userStatus) {
                return response()->json([
                    'message' => "User status not found"
                ], 404);
            }

            return response()->json([
                'status' => $userStatus,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "error to display",
                'error' => $e->getMessage()
            ]);
        }
    }


    public function create($statusid, array $ids, $discount)
    {

        try {
            $userStatus = Userstatus::findorfail($statusid);
            if (!$userStatus) {
                return response()->json([
                    'message' => 'user status doesnot exist'
                ]);
            }
            foreach ($ids as $id) {
                $userStatus->eligibleProducts()->attach($id, ['discount' => $discount]);
            }
            return response()->json([
                'message' => "Added succesfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "cant Create",
                'error' => $e->getMessage()
            ]);
        }
    }

    public function delete($statusid, array $ids)
    {
        try {
            $userStatus = Userstatus::findorfail($statusid);
            if (!$userStatus) {
                return response()->json([
                    'message' => 'user status doesnot exist'
                ]);
            }
            foreach ($ids as $id) {
                $userStatus->eligibleProducts()->detach($id);
            }
            return response()->json([
                'message' => "deleted succesfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "cant Create",
                'error' => $e->getMessage()
            ]);
        }
    }
}
