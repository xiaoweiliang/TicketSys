<?php
namespace Mobile\Controller;
use Think\Controller;
use Common\Common\JsonOperate;
/**
 * 反馈详情
 */
class FeedBackController extends Controller
{
	public function index()
	{

	}
	/**		
	 *反馈接口
	 */
	public  function get_user_feedback()
	{
		$feedback=new \Admin\Model\FeedBackModel('tp_feedback');
		$date=$feedback->AddUserFB($_POST['user_id'], $_POST['content']);
	   return $date;
	}
}