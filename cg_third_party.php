<?php

class cg_third {
	
	var $xmlmap_ok = 0;
	var $xmlsettings = array();
			
	function __construct(){
		$this->checkSitemap();		
	}//func
	
	function checkSitemap(){
		global $wpdb;
		
		$sm = $wpdb->get_row("SELECT 
							  $wpdb->options.option_name,
							  $wpdb->options.option_value
							FROM
							  $wpdb->options
							WHERE option_name='sm_options'");
		if(count($sm)){
			$this->xmlmap = true;
			$smxml = unserialize($sm->option_value);
			$want = array("sm_in_posts","sm_in_pages","sm_in_cats",
						  "sm_b_exclude","sm_b_exclude_cats");					  
			foreach($want as $w){
				$this->xmlsettings[$w] = $smxml[$w];
			}
			//print_r($this->xmlsettings);
		}					  		
	}// func
	
	function xmlSMStat($id,$type){
		global $wpdb;
		
		$stat = false;
		
		if($type=="page"){
			$stat = $this->xmlsettings["sm_in_pages"] && !in_array($id,$this->xmlsettings["sm_b_exclude"]);
		} else {
			$stat = $this->xmlsettings["sm_in_posts"] && 
					!in_array($id,$this->xmlsettings["sm_b_exclude"]) &&
					!in_category($this->xmlsettings["sm_b_exclude_cats"],$id);
		}
		
		return $stat?"included":"excluded";
		
	}//func
	
		
}


?>