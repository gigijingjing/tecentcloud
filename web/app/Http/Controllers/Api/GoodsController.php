<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use Validator;
use App\YimeiSMS\MobileCode;
use App\Goods\Goods;

class GoodsController extends ApiController
{

    public function __construct()
    {

    }

    // 首页产品
    public function getHome(Request $request)
    {
        // 热度产品
        $goodsList = Goods::query()
            ->where('status', 'on')
            ->where('type', '3')
            ->orderByRaw('`order` is NULL,`order` ASC')
            ->orderBy('income', 'DESC')
//            ->take(4)
            ->get();

        // 新加产品
        $newGoodsList = Goods::query()
            ->where('status', 'on')
            ->where('type', '0')
            ->orderByRaw('`order` is NULL,`order` ASC')
            ->orderBy('income', 'DESC')
//            ->take(4)
            ->get();

        return $this->responseJson([
            'new' => $newGoodsList,
            'popular' => $goodsList,
        ]);
    }

    // 产品列表
    public function getList(Request $request)
    {
        $type = $request->input('type');
        $page = $request->input('page');

        $query = Goods::query();
        // 列表模式
        switch ($type) {
            case 'new':
                $query->where('type', '0');
                break;
            case 'small-amount':
                $query->where('type', '1');
                break;
            case 'fast':
                $query->where('type', '2');
                break;
            case 'popular':
                $query->where('type', '3');
                break;
        }

        $paginate = $query
            ->orderByRaw('`order` is NULL,`order` ASC')
            ->orderBy('income', 'DESC')
            ->where('status', 'on')
            ->paginate(10, ['*'], 'page', $page);

        return $this->responseJson($paginate);
    }

    // 产品详情
    public function getDetail(Request $request, $id)
    {
        $goods = Goods::query()->findOrFail($id);
        $goods->click_num = $goods->click_num + 1;
        $goods->saveOrFail();
        return $this->responseJson($goods);
    }
}
