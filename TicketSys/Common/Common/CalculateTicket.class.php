<?php
/**
 * 梁晓伟
 */
namespace  Common\Common;
class CalculateTicket
{
	/**
	 * 计算西安票价
	 * 0~6区间：2元，7~10区间：3元，11~16区间：4元，17个区间及以上：5元。 （普通乘客）
	 */
	public  static  function XiAnTicket($start_point,$end_start,$type='')
	{
		if(empty($type))
		{
			$section= CalculateTicket::XiAnSection($start_point, $end_start);
			switch ($section)
			{
				case  $section<=6:
					return 2;
				case  $section<=10&&$section>6:
					return 3;
				case  $section<=16&&$section>10:
					return 4;
				case  $section>17:
					return 5;
				default:return null;
			}
		}
	}
	/**
	 * 计算路过几个站
	 */
	public static function XiAnSection($start_point,$end_start)
	{
		if($start_point<20&&$end_start<20)
		{
			//在第一条线
			return abs($start_point-$end_start);
		}
	    if($start_point>=20&&$end_start>=20&&$start_point<=40&&$end_start<=40)
		{
			//在第二条路线
			return abs($start_point-$end_start);
		}
		else 
		{
			if($start_point<=19)
			{
				//28为两车交接处 北大街（第二条线路标号）
				//9为两车交接处 北大街（第一条线路标号）
				$num1=abs($start_point-10);
				$num2=abs($end_start-29);
				return $num1+$num2;
			}
			else 
			{
				$num1=abs($start_point-29);
				$num2=abs($end_start-10);
				return $num1+$num2;
			}
		   
		}
	}
}