<?php

namespace App\Actions;

use App\Model\Candidate;
use App\Exceptions\Model\ResourceErrorException;
use Lorisleiva\Actions\Action;

/**
 * 參加者放棄獎項
 *
 * @OA\Put(
 *     path = "/api/withdraw",
 *     summary = "參加者放棄獎項",
 *     description = "參加者放棄獎項，一次只能放棄一筆，不能多筆放棄",
 *     tags = {"抽獎"},
 *     @OA\RequestBody(
 *         description = "API傳入的內容",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property = "staffCode",
 *                 description = "員編",
 *                 example = {"0001"}
 *             ),
 *         ),
 *     ),
 *     @OA\Response(
 *         response = "200",
 *         description = "正常回傳",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property = "data",
 *                 type = "boolean",
 *                 description = "新增後結果",
 *                 example = true
 *             ),
 *         ),
 *     ),
 *     @OA\Response(
 *          response = "400",
 *          description = "沒中獎的人不能放棄得獎",
 *          @OA\JsonContent(
 *              @OA\Property(
 *                  property = "status",
 *                  type = "integer",
 *                  description = "status code",
 *                  default = 500,
 *                  example = 400,
 *              ),
 *              @OA\Property(
 *                  property = "message",
 *                  type = "string",
 *                  description = "錯誤訊息",
 *                  default = "",
 *                  example = "沒中獎的人不能放棄得獎！",
 *             ),
 *             @OA\Property(
 *                  property = "details",
 *                  description = "額外錯誤資訊",
 *                  example = {},
 *             ),
 *         ),
 *     ),
 * )
 */
class Withdraw extends Action
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'staffCode' => 'required|array',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return void
     * @throws ResourceErrorException
     */
    public function handle(array $staffCode)
    {
        $withdrawingCandidates = Candidate::whereIn('staff_code', $staffCode)->get();
        $awards = $withdrawingCandidates->pluck('award_id');
        if ($awards->contains(null)) {
            throw new ResourceErrorException('沒中獎的人不能放棄得獎！');
        }

        $withdrawingCandidates->each(function ($candidate) {
            $award = $candidate->award;
            $award->number += 1;
            $award->save();
        });

        $candidateIds = $withdrawingCandidates->pluck('id');
        Candidate::whereIn('id', $candidateIds)->update(['award_id' => null]);
    }

    /**
     * response 控制
     *
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function response()
    {
        return response(['data' => true]);
    }
}
