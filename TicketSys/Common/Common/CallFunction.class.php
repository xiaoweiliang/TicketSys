<?php
/*
 * 获取调用者的所有参数
 */
 namespace Common\Common;
class CallFunction
{
    private $FuncInfo;
    /*
     * 获取所有信息
     */
    public function __construct()
    {
        $this->FuncInfo=debug_backtrace();
 
    }
    /*
     * 获取调用者的方法
     */
    public function GetCallFunc()
    {
        return $this->FuncInfo[1]['function'];
    }
    /*
     * 获取调用者的参数
     */
    public function GetCallArg()
    {
        return $this->FuncInfo[1]['args'];
    }
    /*
     * 获取调用者的类名
     */
    public function GetCallClass()
    {
        return $this->FuncInfo[1]['class'];
    }
    
    /*
     * 获取本方法名
     */
    public function GetBCallFunc()
    {
        return $this->FuncInfo[0]['function'];
    }
    /*
     * 获取本方法所在的类名
     */
    public function GetBFuncClass()
    {
        return $this->FuncInfo[0]['class'];
    }
    /*
     * 获取本方法参数
     */
    public function GetBFuncArg()
    {
        return $this->FuncInfo[0]['args'];
    }
}