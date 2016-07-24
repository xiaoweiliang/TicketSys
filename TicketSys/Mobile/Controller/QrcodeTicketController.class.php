<?php
namespace Mobile\Controller;
use Think\Controller;
use Admin\Model\OrderModel;
class	QrcodeTicketController extends	Controller
{
	public function index()
	{
		
	}
	public function GetQrcodeTicket()
	{
		$order=new OrderModel('tp_userorder');
		$date=$order->QrcodeTicket($_POST);
		return $date;
	}
}