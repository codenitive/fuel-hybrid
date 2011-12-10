<?php

class Acl_Ip
{
	/**
	 * Ip filtering should be able to give access or revoke it
	 */
	public static function _init()
	{
		
	}

	public static function make()
	{
		return new static();
	}

	public static function forge()
	{
		return static::make();
	}

	public static function factory()
	{
		return static::make();
	}

}