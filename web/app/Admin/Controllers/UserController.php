<?php

namespace App\Admin\Controllers;

use Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Encore\Admin\{
    Grid,
    Form,
    Layout\Content,
    Facades\Admin
};

class UserController extends Controller
{
    public function index(Request $request, Content $content)
    {
        return $content
        ->header('列表')
        ->description('用户列表')
        ->body($this->grid($request));
    }
    
    public function show($id)
    {
        return redirect('/admin/users');
    }

    protected function grid(Request $request)
    {
        $user = $request->user('admin');

        $grid = new Grid(new User);
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->between('created_at', '注册时间')->datetime();
        });

        $query = $grid->model()->newQuery();
        $isAdmin = false;
        foreach ($user->roles as $role) {
            if ($role->id === 1) {
                $isAdmin = true;
                break;
            }
        }
        if (!$isAdmin) $query->where('channel', $user->id);

        $grid->id('ID')->sortable();

        if (!$isAdmin) {
            $grid->name('用户名称')->display(function($value) {
                return '*****' . substr($value, -6);
            });
            $grid->mobile('用户手机号')->display(function($value) {
                return '*****' . substr($value, -6);
            });
        } else {
            $grid->name('用户名称');
            $grid->mobile('用户手机号');
        }

        $grid->login_at('登录时间');
        $grid->created_at('注册时间');

        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableRowSelector();
        $grid->disableActions();

        return $grid;
    }

    public function create(Content $content)
    {
        return redirect('/admin/users');
        // return $content
        // ->header('创建')
        // ->description('创建产品信息')
        // ->body($this->form());
    }

    public function edit($id, Content $content)
    {
        return redirect('/admin/users');
        // return $content
        //     ->header('编辑')
        //     ->description('编辑产品信息')
        //     ->body($this->form()->edit($id));
    }

    protected function form()
    {

    }

    public function update(Request $request, $id)
    {
    }

    public function store(Request $request)
    {
//
    }
}
