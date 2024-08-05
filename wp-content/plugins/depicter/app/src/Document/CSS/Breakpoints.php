<?php
namespace Depicter\Document\CSS;


class Breakpoints
{
	const DESKTOP = 'default';
	const TABLET  = 'tablet';
	const MOBILE  = 'mobile';

	const DESKTOP_SIZE = '';
	const TABLET_SIZE  = '1024';
	const MOBILE_SIZE  = '767';

	public static function names(){
		return array_keys( self::all() );
	}

	public static function all(){
		return [
			self::DESKTOP => self::DESKTOP_SIZE,
			self::TABLET  => self::TABLET_SIZE,
			self::MOBILE  => self::MOBILE_SIZE
		];
	}

	protected static function getSize( $breakpoint_name = 'default' ){
		if( isset( self::all()[ $breakpoint_name ] ) ){
			return self::all()[ $breakpoint_name ];
		}
		return '';
	}

	public static function getParentDevice( $device ) {
		$devices = [
			self::DESKTOP => '',
			self::TABLET => self::DESKTOP,
			self::MOBILE => self::TABLET
		];

		return $devices[ $device ] ?? '';
	}
}
