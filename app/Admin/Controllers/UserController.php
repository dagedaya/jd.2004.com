<?php

namespace App\Admin\Controllers;

use App\Model\UserModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'UserModel';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserModel());

        $grid->column('user_id', __('User id'));
        $grid->column('user_name', __('User name'));
        $grid->column('password', __('Password'));
        $grid->column('question', __('Question'));
        $grid->column('answer', __('Answer'));
        $grid->column('sex', __('Sex'));
        $grid->column('birthday', __('Birthday'));
        $grid->column('user_money', __('User money'));
        $grid->column('frozen_money', __('Frozen money'));
        $grid->column('pay_points', __('Pay points'));
        $grid->column('rank_points', __('Rank points'));
        $grid->column('address_id', __('Address id'));
        $grid->column('reg_time', __('Reg time'));
        $grid->column('last_login', __('Last login'));
        $grid->column('last_ip', __('Last ip'));
        $grid->column('visit_count', __('Visit count'));
        $grid->column('alias', __('Alias'));
        $grid->column('is_validated', __('Is validated'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(UserModel::findOrFail($id));

        $show->field('user_id', __('User id'));
        $show->field('user_name', __('User name'));
        $show->field('password', __('Password'));
        $show->field('question', __('Question'));
        $show->field('answer', __('Answer'));
        $show->field('sex', __('Sex'));
        $show->field('birthday', __('Birthday'));
        $show->field('user_money', __('User money'));
        $show->field('frozen_money', __('Frozen money'));
        $show->field('pay_points', __('Pay points'));
        $show->field('rank_points', __('Rank points'));
        $show->field('address_id', __('Address id'));
        $show->field('reg_time', __('Reg time'));
        $show->field('last_login', __('Last login'));
        $show->field('last_ip', __('Last ip'));
        $show->field('visit_count', __('Visit count'));
        $show->field('alias', __('Alias'));
        $show->field('is_validated', __('Is validated'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new UserModel());

        $form->text('user_name', __('用户名'));
        $form->password('password', __('密码'));
        $form->text('question', __('问题'));
        $form->text('answer', __('回答'));
        $form->switch('sex', __('性别'));
        $form->number('birthday', __('生日'));
        $form->decimal('user_money', __('用户金额'))->default(0.00);
        $form->decimal('frozen_money', __('冻结的钱'))->default(0.00);
        $form->number('pay_points', __('支付点'));
        $form->number('rank_points', __('Rank points'));
        $form->number('address_id', __('地址id'));
        $form->number('reg_time', __('注册时间'));
        $form->number('last_login', __('最后一次登陆'));
        $form->text('last_ip', __('最后登陆的ip'));
        $form->number('visit_count', __('访问次数'));
        $form->text('alias', __('Alias'));
        $form->switch('is_validated', __('验证'));

        return $form;
    }
}
