<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
</head>
<body>
<?php

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');

ini_set('max_execution_time', 300000);
	class BinaryStream {
		public $binary='';
		private $counter = 0;
		private $reverse = false;
		function __construct($binary,$reverse) {
			$this->binary=$binary;
			$this->counter=0;
			$this->reverse=$reverse;
		}
		private function byteToDec($data){
			return hexdec(bin2hex($data));
		}
		function readBytes($size){
			$data=substr($this->binary,$this->counter,$size);
			if($this->reverse) $data=strrev($data);
			$this->counter+=$size;
			return bin2hex($data);
		}
		function readName(){
			$size=4;
			$data=substr($this->binary,$this->counter,$size);
			if($this->reverse) $data=strrev($data);
			$this->counter+=$size;
			$size=hexdec(bin2hex($data));
			$data=substr($this->binary,$this->counter,$size);
			$this->counter+=$size;
			return $data;
		}
		function readString(){
			$size=4;
			$data=substr($this->binary,$this->counter,$size);
			$this->counter+=$size;
			$size=hexdec(bin2hex($data));
			$data=substr($this->binary,$this->counter,$size);
			$this->counter+=$size;
			return str_replace("\0",'',$data);
		}
		function readOctets(){
			$size=$this->readLength();
			$data=substr($this->binary,$this->counter,$size);
			if($this->reverse) $data=strrev($data);
			$this->counter+=$size;
			return bin2hex($data);
		}
		function readByte(){
			$size=1;
			$data=substr($this->binary,$this->counter,$size);
			$this->counter+=$size;
			return hexdec(bin2hex($data));
		}
		function readInt64(){
			$size=8;
			$data=substr($this->binary,$this->counter,$size);
			if($this->reverse) $data=strrev($data);
			$this->counter+=$size;
			return hexdec(bin2hex($data));
		}
		function readInt32(){
			$size=4;
			$data=substr($this->binary,$this->counter,$size);
			if($this->reverse) $data=strrev($data);
			$this->counter+=$size;
			return hexdec(bin2hex($data));
		}
		function readInt16(){
			$size=2;
			$data=substr($this->binary,$this->counter,$size);
			if($this->reverse) $data=strrev($data);
			$this->counter+=$size;
			return reset(unpack('s',pack('s',hexdec(bin2hex($data)))));
		}
		function readFloat(){
			$size=4;
			$data=substr($this->binary,$this->counter,$size);
			if($this->reverse) $data=strrev($data);
			$this->counter+=$size;
			
			$v = hexdec(bin2hex($data));
			if($v==0) return 0.0;
			$x = ($v & ((1 << 23) - 1)) + (1 << 23) * ($v >> 31 | 1);
			$exp = ($v >> 23 & 0xFF) - 127;
			return floatval($x * pow(2, $exp - 23));
		}
	}
	//io functions
	function saveBytes($value){
		return pack('H*',$value);
	}
	function saveByte($value){
		return pack('C*',$value);
	}
	function saveInt16($value){
		return pack('v*',$value);
	}
	function rawSingleHex($num) {
		return strrev(unpack('h*', pack('f', $num))[1]);
	}
	function saveFloat($value){
		return strrev(pack('f', $value));
	}
	function saveInt32($value){
		return pack('V*',$value);
	}
	function saveInt64($value){
		return pack('J*',$value);
	}
	function packOctets($string){
		$Octets = pack("H*", $string);
		return packetLength($Octets).$Octets;
	}
	function saveString($string){
		$Message = $string;
		$len=strlen($Message);
		return saveInt32($len).$Message;
	}
	function saveString16($string){
		$Message = iconv("UTF-8", "UTF-16LE", $string);
		$len=strlen($Message);
		return saveInt32($len).$Message;
	}
	function espaceString($string){
		$string=str_replace("\r",'\\r',$string);
		$string=str_replace("\n",'\\n',$string);
		$string=str_replace("\t",'\\t',$string);
		$string=str_replace('	','\\t',$string);
		return $string;
	}
	function unespaceString($string){
		$string=str_replace('\\r',"\r",$string);
		$string=str_replace('\\n',"\n",$string);
		$string=str_replace('\\t',"\t",$string);
		return $string;
	}
	// SAVE FILE
	$lang='en';
	if(isset($_GET['lang'])) $lang=$_GET['lang'];
	if ($handle = opendir('./'.$lang.'/id/')) {
		$sections=array();
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
				$file=explode('.',$entry);
				$sections[]=$file[0];
			}
		}
		closedir($handle);
		$array=array();
		
		$binary='';
		$binary.=saveInt32(1196310860); //magic
		$binary.=saveInt32(1); //version
		$binary.=saveInt32(1548038144); //timestamp
		$sections=array('ai','skill','data_recipe','map','data_quest','common','ui','age','photobook','title','arena','countbirds','combat','data_treasures','policy','data_text','daily',
		'item','error','data_item','quest','interface','questionaire','data_monster','data_npc','login','data_config','pet','miscs','help','achievement','config','lottery','question','animation',
		'social','data_equip','dungeon');
		$important_characters=array('%','&','$','{','}','%s','%d');
		$ic=count($important_characters);
		$cnt=count($sections);
		$binary.=saveInt16($cnt);
		for($s=0;$s<$cnt;$s++){
			$sec=array();
			$sec['unk']=3176595456;
			$sec['bundle_name']=$sections[$s];
			if($sec['bundle_name']!='ai') $sec['unk']=3176644077;
			$binary.=saveInt32($sec['unk']);
			$binary.=saveString($sec['bundle_name']);
			$sec['bundle_count']=0;
			$sec['i18n']=array();
			$fn = fopen('./'.$lang.'/id/'.$sec['bundle_name'].'.txt',"r");
			while(! feof($fn))  {
				$result=str_replace("\n",'',fgets($fn));
				if(!$result) continue;
				$result = explode("\t",$result);
				$sec['i18n'][(int)$result[0]]=array('id'=>(string)$result[1]);
				if(count($result)<2) echo 'TAB ERROR ID ['.$sec['bundle_name'].' id: '.$result[0].']<br />';
				$sec['bundle_count']++;
			}
			fclose($fn);
			$fn = fopen('./'.$lang.'/source/'.$sec['bundle_name'].'.txt',"r");
			while(! feof($fn))  {
				$result=str_replace("\n",'',fgets($fn));
				if(!$result) continue;
				$result = explode("\t",$result);
				if(count($result)<2) echo 'TAB ERROR SOURCE ['.$sec['bundle_name'].' id: '.$result[0].']<br />';
				$sec['i18n'][(int)$result[0]]['source']=((string)$result[1]);
			}
			fclose($fn);
			$fn = fopen('./'.$lang.'/target/'.$sec['bundle_name'].'.txt',"r");
			$l=0;
			while(! feof($fn))  {
				$result=str_replace("\n",'',fgets($fn));
				if(!$result) continue;
				$result = explode("\t",$result);
				$id=(int)$result[0];
				if(count($result)<2) echo 'TAB ERROR TARGET ['.$sec['bundle_name'].' id: '.$id.']<br />';
				if($l!=$id) echo 'INDEX ERROR TARGET ['.$sec['bundle_name'].' id: '.$l.'/'.$id.']<br />';
				$sec['i18n'][$id]['target']=((string)$result[1]);
				for($c=0;$c<$ic;$c++){
					$c1=substr_count($sec['i18n'][$id]['source'], $important_characters[$c]);
					$c2=substr_count($sec['i18n'][$id]['target'], $important_characters[$c]);
					if($c1 != $c2) echo 'TAB '.$sec['bundle_name'].' ID: '.$id.': <b>'.$important_characters[$c].'</b> count invalid ('.$c2.'/'.$c1.')<br />';
				}
				
				$l++;
			}
			fclose($fn);
			$binary.=saveInt32($sec['bundle_count']);
			for($i=0;$i<$sec['bundle_count'];$i++){
				$binary.=saveString($sec['i18n'][$i]['id']);
				$binary.=saveString(unespaceString($sec['i18n'][$i]['source']));
				$binary.=saveString(unespaceString($sec['i18n'][$i]['target']));
			}
			$array[]=$sec;
			echo $sec['bundle_name'].' done...<br />';
		}
		$binary.=saveInt16(48621);
		if($file = fopen('./lang_DATA.data', 'wb')){
			fwrite($file, $binary);
			fclose($file);
		}
		
	}
?>