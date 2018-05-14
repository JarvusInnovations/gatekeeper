<?php
/**
 * MP4Info
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @copyright   Copyright (c) 2006-2009 Tommy Lacroix
 * @license		LGPL version 3, http://www.gnu.org/licenses/lgpl.html
 * @package 	php-mp4info
 * @link 		$HeadURL: https://php-mp4info.googlecode.com/svn/trunk/MP4Info/Box/hdlr.php $
 */

// ---

/**
 * ?.? Initial Object Descriptor (IODS)
 * 
 * @author 		Tommy Lacroix <lacroix.tommy@gmail.com>
 * @version 	1.0.20090601	$Id: hdlr.php 2 2009-06-11 14:12:31Z lacroix.tommy@gmail.com $
 */
class MP4Info_Box_iods extends MP4Info_Box {
	/**
	 * Handler type
	 *
	 * @var uint32
	 */
	protected $handlerType;
	
	/**
	 * Name
	 *
	 * @var	string
	 */
	protected $name;
	
	/**
	 * Timezone
	 *
	 * @var int
	 * @static
	 */
	protected static $timezone = false;
	
	/**
	 * Constructor
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param	int					$totalSize
	 * @param	int					$boxType
	 * @param	file|string			$data
	 * @param	MP4Info_Box			$parent
	 * @return 	MP4Info_Box_hdlr
	 * @access 	public
	 * @throws 	MP4Info_Exception
	 */	
	public function __construct($totalSize, $boxType, $data, $parent) {
		if (!self::isCompatible($boxType, $parent)) {
			throw new Exception('This box isn\'t "iods"');
		}

		// Get timezone
		if (self::$timezone === false) {
			self::$timezone = date('Z');
		}
		
		// Call ancestor
		parent::__construct($totalSize,$boxType,'',$parent);
		
		// Get data
		$data = self::getDataFrom3rd($data,$totalSize);

		// Unpack
		$ar = unpack('Cversion/C3flags',$data);
		$ar2 = unpack('CiodTag/Nlength/nODID/CODProfileLevel/CsceneProfileLevel/CaudioProfileLevel/CvideoProfileLevel/CgraphicsProfileLevel', substr($data,4));
		
		// Save		
		$this->version = $ar['version'];
		$this->flags = $ar['flags1']*65536+$ar['flags1']*256+$ar['flags1']*1;
		
		print_r($ar2);
		die();
	} // Constructor
	
	
	/**
	 * Check if block is compatible with class
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @param 	int					$boxType
	 * @param 	MP4Info_Box			$parent
	 * @return 	bool
	 * @access 	public
	 * @static
	 */	
	static public function isCompatible($boxType, $parent) {
		return $boxType == 0x696f6473;
	} // isCompatible method
	
	
	/**
	 * Handler type getter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return	int
	 * @access 	public
	 */
	public function getHandlerType() {
		return $this->handlerType;
	} // getHandlerType method
	
	
	/**
	 * Get creation time
	 * 
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return	int
	 * @access 	public
	 */
	public function getCreationTime() {
		return $this->ctime;
	} // getCreationTime
	
	
	/**
	 * Get modification time
	 * 
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return	int
	 * @access 	public
	 */
	public function getModificationTime() {
		return $this->mtime;
	} // getModificationTime
	
	/**
	 * String converter
	 *
	 * @author 	Tommy Lacroix <lacroix.tommy@gmail.com>
	 * @return	string
	 * @access 	public
	 */	
	public function toString() {
		return '[MP4Info_Box_hdlr:'.$this->handlerType.']';
	} // toString method
} // MP4Info_Box_hdlr class