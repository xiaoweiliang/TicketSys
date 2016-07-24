<?php
namespace Mobile\Controller;
use Think\Controller;
use Admin\Model\SpendModel;
use Common\Common\JsonOperate;
use Common\Common\ToolBack;
use Common\Common\KeyTool;
/**
 * 消费详情
 */
class ConsumeController extends Controller
{
	public function index()
	{
		
	}
	/**
	 *查询消费接口
	 */
	public  function get_consume_record()
	{
		$consume=new SpendModel('tp_usermoneyaccount');
		$user_id=KeyTool::base_decodes($_GET['user_id']);
		$user_id=($_GET['user_id']);
		$date=$consume->GetUserSpend($user_id);
		 return $date;
	}
	/**
	 * 删除消费记录
	 */
	public  function  delete_record()
	{
		$consume=new SpendModel('tp_usermoneyaccount');
		$consume->DelUserSpend($_POST['user_id'],$_POST['id']);
		return 1;
	}
	
}