<?php
namespace Mobile\Controller;
use Think\Controller;
use Common\Common\KeyTool;
use Admin\Model\OrderModel;
use Common\Common\ToolBack;
use Common\Common\JsonOperate;
/**
 * 订单详情
 */
class UserOrderController extends Controller
{
	public function index()
	{
		
	}
	/**
	 * 查询订单
	 */
	public  function order_info()
	{
		
		$user_id=KeyTool::base_decodes($_POST['user_id']);
		$order=new OrderModel('tp_userorder');
	    $date=$order->GetUserOrder($user_id,$_POST['type']);
	    return $date;
	}
	/**
	 *删除订单
	 */
	public  function delete_order()
	{
		$user_id=KeyTool::base_decodes($_POST['user_id']);
		$order=new OrderModel('tp_userorder');
		$order->DelUserOrder($user_id,$_POST['order_sn']);
		return 1;
	}
	/**
	 * 退订单
	 */
	public function recede_order()
	{
		$user_id=KeyTool::base_decodes($_POST['user_id']);
		$order=new OrderModel('tp_userorder');
		$da=$order->RecedeOrder($user_id,$_POST['order_sn']);
		return $da;
	}
	/**
	 * 取消订单
	 */
	public function cancel_order()
	{
		$order=new OrderModel('tp_userorder');
		$date=$order->CancelOrder($_POST['user_id'], $_POST['order_sn']);
		return $date;
	}
	

}