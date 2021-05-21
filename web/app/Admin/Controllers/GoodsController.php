<?php

namespace App\Admin\Controllers;

use Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Goods\Goods;
use Encore\Admin\{
    Grid,
    Form,
    Layout\Content,
    Facades\Admin
};

class GoodsController extends Controller
{
    protected $goods;

    public function index(Content $content)
    {
        return $content
        ->header('列表')
        ->description('产品列表')
        ->body($this->grid());
    }
    
    public function show($id)
    {
        return redirect('/admin/goods');
    }

    protected function grid()
    {
        $grid = new Grid(new Goods);

        $grid->id('ID')->sortable();
        $grid->name('产品名称');

        $grid->logo('logo')->display(function($value) {
            return '<img width="40" height="40" src="' . $value . '"/>';
        });
        
        $grid->url('产品链接')->display(function($value) {
            return '<a href="' . $value . '">' . $value . '</a>';
        });
        $grid->status('产品状态')->display(function($value) {
            if ($value == 'on') {
                return '<label class="label label-success">上线</label>';
            }
            return '<label class="label label-default">未上线</label>';
        });

        $grid->stock('产品库存');
        $grid->loan_ratio('放款率');
        $grid->day_profit_ratio('日利率');
        $grid->contact_name('联系人');
        $grid->contact_phone('联系人电话');
        $grid->mode('合作模式');
        $grid->click_num('点击量')->sortable();
        $grid->register_num('注册量')->sortable();

        $grid->conversion('转化率')->sortable();
        $grid->income('收益')->sortable();
        
        $grid->disableExport();
        $grid->actions(function ($actions) {
            // $actions->disableDelete();
            // $actions->disableEdit();
            $actions->disableView();
        });

        return $grid;
    }

    public function create(Content $content)
    {
        return $content
        ->header('创建')
        ->description('创建产品信息')
        ->body($this->form());
    }

    public function edit($id, Content $content)
    {
        $this->goods = Goods::query()->find($id);
        return $content
            ->header('编辑')
            ->description('编辑产品信息')
            ->body($this->form()->edit($id));
    }

    protected function form()
    {
        $mode = $this->goods ? $this->goods->mode : 'cpa+cps';
        $status = $this->goods ? $this->goods->status : 'off';
        $type = $this->goods ? $this->goods->type : '0';
        $star = $this->goods ? $this->goods->star : '1';
        $form = new Form(new Goods);

        $form->display('id', 'ID');
        $form->text('name', '产品名称');
        $form->number('order', '排序');

        $form->image('logo', '产品logo');
        $form->image('banner_url', '产品横幅');
        $form->text('tags', '产品标签')->placeholder('产品标签，多个请使用逗号隔开')->help('多个请使用逗号隔开');
        $form->text('popularity', '产品人气');
        $form->text('loan_speed', '放款速度');
        $form->text('loan_range', '放款范围');

        $form->radio('star', '产品星级')
        ->options([
            '1' => '1星', 
            '2' => '2星', 
            '3' => '3星', 
            '4' => '4星', 
            '5' => '5星', 
        ])->default($star);

        $form->radio('type', '产品类型')
        ->options([
            '0'=> '新品推荐',
            '1' => '小额极速',
            '2'=> '大额低息',
            '3'=> '热门推荐',
        ])->default($type);

        $form->radio('status', '是否上线')
        ->options([
            'on' => '是',
            'off' => '否'
        ])->default($status);
        $form->text('url', '产品链接');
        $form->editor('content', '产品内容');
        $form->radio('mode', '合作模式')
        ->options([
            'uv' =>'UV',//还有uv合作模式的；
            'cpa' => 'CPA', 
            'cps'=> 'CPS',
            'cpa+cps'=> 'CPS+CPS',
        ])->default($mode);

        $form->text('cpa', 'cpa单价');
        $form->text('cps', 'cps分成比');
        $form->text('loan_price', '放款单价');
        $form->text('loan_money', '放款金额');
        $form->text('loan_num', '放款数量');
        $form->text('register_num', '注册量');
        $form->text('register_price', '注册单价');
        $form->text('conversion', '转化率');
        $form->text('income', '推广收益');

        $form->text('stock', '产品库存');
        $form->text('loan_ratio', '产品放款率');
        $form->text('day_profit_ratio', '产品日利率');
        $form->text('contact_name', '联系人');
        $form->text('contact_phone', '联系人电话');

        $form->display('created_at', 'Created At');
        $form->display('updated_at', 'Updated At');

        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        $clickNum = $this->goods ? $this->goods->click_num : 0;
        $form->html(view('admin..form-script', [
            'mode' => $mode,
            'clickNum' => $clickNum,
        ])->render())->plain();

        return $form;
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'url' => 'required|string',
            'logo' => 'file',
            'banner_url' => 'file',
            'content' => 'string'
        ], [
            'name.required' => '产品名字必须',
            'url.required' => '产品链接必须'
        ]);

        $goods = Goods::query()->findOrFail($id);
        $goods->fill($request->all());
        $logo = $request->file('logo');
        if ($logo) {
            $logoPath = $logo->store('/public/' . date('Y-m-d') . '/logo');
            $logoUrl = Storage::url($logoPath);
            $goods->logo = $logoUrl;
        }
        $banner = $request->file('banner_url');
        if ($banner) {
            $bannerPath = $banner->store('/public/' . date('Y-m-d') . '/banner');
            $bannerUrl = Storage::url($bannerPath);
            $goods->banner_url = $bannerUrl;
        }
        
        $goods->saveOrFail();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'url' => 'required|string',
            'logo' => 'required|file',
            'banner_url' => 'file',
            'content' => 'string'
        ], [
            'name.required' => '产品名字必须',
            'url.required' => '产品链接必须',
            'logo.required' => '产品logo必须'
        ]);

        $goods = new Goods($request->all());
        $logo = $request->file('logo');
        if ($logo) {
            $logoPath = $logo->store('/public/' . date('Y-m-d') . '/logo');
            $logoUrl = Storage::url($logoPath);
            $goods->logo = $logoUrl;
        }
        $banner = $request->file('banner_url');
        if ($banner) {
            $bannerPath = $banner->store('/public/' . date('Y-m-d') . '/banner');
            $bannerUrl = Storage::url($bannerPath);
            $goods->banner_url = $bannerUrl;
        }
        $goods->saveOrFail();
    }

    public function destroy(Request $request, $id)
    {
        $goods = Goods::query()->findOrFail($id);
        $goods->delete();
    }
}
