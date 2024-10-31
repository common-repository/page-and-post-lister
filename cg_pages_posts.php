<?php

require_once("cg_pages_core.php");

class cg_pages_posts extends cg_pages_core {
		
	function __construct(){
		parent::__construct();
	}
	
	function showPostFilterControls(){
		$act = get_option("siteurl")."/wp-admin/options-general.php?page=page-and-post-lister/cg_pages.php";						
		?>											
			<select name="post_categories" id="post_categories" <?php echo $_POST["post_type"]=="page" ?"style='display:none;'":""; ?>>
				<option value="0">Show All Categories</option>			
				<?php echo $this->listCategories(); ?>
			</select>															
		<?php		
	}	
		
	function displayPosts($parent=0,$level=0){
		global $wpdb;

		$colspan = $this->xmlmap_ok?10:11;			
		
		if($parent!=0 && $level==0) {
			//individual category selected
		
			$cats = $wpdb->get_results("SELECT 
									  $wpdb->terms.name,
									  $wpdb->terms.term_id,
									  $wpdb->term_taxonomy.taxonomy
									FROM
									  $wpdb->terms
									  INNER JOIN $wpdb->term_taxonomy ON ($wpdb->terms.term_id = $wpdb->term_taxonomy.term_id)
									WHERE $wpdb->term_taxonomy.taxonomy='category' 
									AND $wpdb->terms.term_id ='".$_POST["post_categories"]."' ".
									"ORDER BY $wpdb->terms.name ASC");
			
			foreach($cats as $c){
					echo 	"<tr class='category'>
							  <td>&nbsp;</td>	
							  <td colspan='$colspan'>{$c->name}</td>				  
							 </tr>";	
					$this->listPages("post",false,$_POST["page_parent"],0,$c->term_id);
					$this->displayPosts($c->term_id,$level+1);								
			}				

		} else {			
			$args =   array('type'=> 'post',
						    'child_of' => $parent,
						    'orderby' => 'name',
						    'order' => 'ASC',
						    'hide_empty' => 1,
						    'hierarchical' => 1,
						    'exclude' => '',
						    'include' => '',
						    'number' => '',
						    'pad_counts' => true);
							
			$cats = get_categories($args);
			
			foreach($cats as $c){
				if($c->count){	
					echo 	"<tr class='category'>
							  <td>&nbsp;</td>	
							  <td colspan='$colspan'>{$c->name} ($c->count)</td>				  
							 </tr>";	
					$this->listPages("post",false,$_POST["page_parent"],0,$c->cat_ID);
				}
				$this->displayPosts($c->cat_ID,$level+1);
			}		
		}
																			
	}//func	
	
	
	
	function hasChildCategories($parent){
		$args =   array('type'=> 'post',
						'parent'=>$parent,
					    'orderby' => 'name',
					    'order' => 'ASC',
					    'hide_empty' => 0,
					    'hierarchical' => 1,
					    'exclude' => '',
					    'include' => '',
					    'number' => '',
					    'pad_counts' => true);
						
		$cats = get_categories($args);
		return count($cats)>0;		
	}

	function listCategories($parent=0,$level=0){		
		$args =   array('type'=> 'post',
						'parent'=>$parent,
					    'orderby' => 'name',
					    'order' => 'ASC',
					    'hide_empty' => 0,
					    'hierarchical' => 1,
					    'exclude' => '',
					    'include' => '',
					    'number' => '',
					    'pad_counts' => true);
						
		$cats = get_categories($args);
		$all_cats="";
		foreach($cats as $cat){
			if($cat->count>0 || $this->hasChildCategories($cat->cat_ID)){	
				$count = $cat->count==0?"":"({$cat->count})";
				$padding = str_repeat("&nbsp;&nbsp;",$level);				
				$selected = $_POST["post_categories"]==$cat->cat_ID?"selected='selected'":"";
				$all_cats .= "<option value='".$cat->cat_ID."' $selected>$padding".$cat->cat_name." $count</option>";
			}		
			$all_cats .= $this->listCategories($cat->cat_ID,$level+1);
		}		
		return 	$all_cats; 	
	}	
	

	
}

?>