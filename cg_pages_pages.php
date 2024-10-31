<?php

require_once("cg_pages_posts.php");

class cg_pages_pages extends cg_pages_posts {
		
	function __construct(){
		parent::__construct();
	}//func
		
	function show_pages($showHead=true) {	
		extract($_POST);				
		$this->listPages("page",true,!isset($page_parent)?0:$page_parent,0,0);
		$this->listPages("page",false,!isset($page_parent)?0:$page_parent,1,0);	
	}//func		

	function showPageFilterControls(){					
		?>
			<select name="page_parent" id="page_parent">
				<option value="0">Show All Pages</option>
				<?php echo $this->getParents(0,0); ?>
			</select>
		<?php		
	} //func		
	
	function hasChildren($pid){
		global $wpdb;
		
		$children = $wpdb->get_var("SELECT count(ID) 
								    FROM $wpdb->posts
		 							WHERE post_type='page' AND post_status='publish' AND post_parent='$pid'");
									
		return $children>0;							
	}//func	
	
	function getParents($pid,$level){
		global $wpdb;
							
		$allparents = $wpdb->get_results("SELECT ID, post_title 
									  FROM $wpdb->posts
		 							  WHERE post_type='page' AND post_status='publish' AND post_parent='$pid'
									  ORDER by post_title");
			
		$all_parents_options = "";
									  		
		foreach($allparents as $par){
			$pref = "";
			for($xxx=0;$xxx<$level;$xxx++) $pref .= "-";			
			if($this->hasChildren($par->ID)){
				$selected = $_POST["page_parent"]==$par->ID?"selected='selected'":"";
				$all_parents_options .= "<option value='".$par->ID."' class='lev-$level' $selected>$pref".$par->post_title."</option>";
				$all_parents_options .= $this->getParents($par->ID,$level+1);
			} 
		}	
		
		return 	$all_parents_options;
	}//func	
	
	
}//class

?>