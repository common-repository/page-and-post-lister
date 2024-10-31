<?php

require_once("cg_third_party.php");

class cg_pages_core extends cg_third {
	

	var $alt=false;
	var $exes=array();
	var $google_blocked=array();	
	var $site = "";
	var $context = "";	
	
	function __construct(){
				
		parent::__construct();		
						
		$this->site = get_bloginfo("wpurl");
		
		add_action("wp_print_scripts", array(&$this,"cssAndScripts"));
		
		// ajax responses		
		
		add_action('wp_ajax_sitemapEX', array(&$this,"ajax_sitemapEX"));		
		add_action('wp_ajax_enCO', array(&$this,"ajax_enCO"));
		add_action('wp_ajax_googleBlock', array(&$this,"ajax_googleBlock"));
		add_action('wp_ajax_include', array(&$this,"ajax_include"));
		add_action('wp_ajax_exclude', array(&$this,"ajax_exclude"));		
		add_action('wp_ajax_delPost', array(&$this,"ajax_delPost"));			
	} //func
			

	function processMassActions(){	
	global $wpdb;	
		extract($_POST);
		if($submit=="Apply" && $mass_ids!=""){			
			$ids = explode(",",$mass_ids);	
			foreach($ids as $id){
				switch($mass_action){					
					case "google-search-in": $this->ajax_googleBlock($id,"-"); break;
					case "google-search-ex": $this->ajax_googleBlock($id,"allowed"); break;					
					case "comment-close": $this->ajax_enCOMass($id,"closed"); break;
					case "comment-open": $this->ajax_enCOMass($id,"open"); break;
					case "batch-delete": wp_delete_post($id); break;
					case "draft":  
					case "publish": 
					case "private":  $this->setStatus($mass_action, $id); break;  																							
				}
			}
		}
	} //func		
	
	function setStatus($stat,$id){
		global $wpdb;
		$wpdb->query("UPDATE $wpdb->posts SET post_status='$stat' WHERE ID='$id'");		
	}
	

	function isPost(){
		return isset($_POST["post_type"]) && $_POST["post_type"]=="post";
	}//func	
	

	function getPageSettings(){
		global $wpdb;
		
		// get excludes;
		
		$ex = $wpdb->get_results("SELECT 
									  $wpdb->postmeta.post_id,
									  $wpdb->postmeta.meta_value
									FROM
									  $wpdb->postmeta 
									WHERE meta_key='_widgets_exclude'");	
		foreach($ex as $x){
			$this->exes[$x->post_id]=$x->meta_value?"excluded":"-";
		}			
		
		// get google blocked;
		
		$goog_block_string = "_cg_google_blocked";
		
		$ex = $wpdb->get_results("SELECT 
									  $wpdb->postmeta.post_id,
									  $wpdb->postmeta.meta_value
									FROM
									  $wpdb->postmeta 
									WHERE meta_key='$goog_block_string'");	
									
		foreach($ex as $x){
			$this->google_blocked[$x->post_id]=$x->meta_value?"blocked":"allowed";
		}			
	}//func	
	
	function listPages($type, $isParent, $parent=0, $level, $category=0){	
		global $wpdb;
				
		$status = isset($_POST["post_status"])?$_POST["post_status"]:"publish";
		
		if($type=="page"){
			if($isParent){
				$xpages = $this->getAllPages("page",$status,$parent,true,0);
			} else {
				$xpages = $this->getAllPages("page",$status,$parent,false,$category);
			}
		} else {
			$xpages = $this->getAllPages("post",$status,$parent,false,$category);
		}																
		
		$xsiteurl = get_bloginfo("siteurl");
		
		if(count($xpages)){				  			  
			
			foreach($xpages as $p){								
				$ID=$p->ID;				
								
				$tr_class = $this->alt? "class='alter $type'":"class='$type'";
								
				$this->alt = !$this->alt;									
				$editLink = $this->site."/wp-admin/post.php?action=edit&post=$ID";				
				$comment = $p->comment_status;			
				$commct = $p->comment_count==0?0:$p->comment_count;	
				$google = $this->getGoogle($ID);				
				$permalink = get_permalink($ID);				
				$template = $type=="post"?"NA":$this->getTemplate($ID);			
				$delete = $this->getDelete($ID);
				
				$sitemap = $this->xmlSMStat($ID,$type);

				$view = "<span class='view_icon'><a href='$permalink' target='_blank'>&nbsp;&nbsp;&nbsp;&nbsp;<a/></span>";
								
				$o ="<tr $tr_class id='tr_$ID'>".
					 '<td align="center"><input class="post_check" type="checkbox" name="sel" id="check_'.$ID.'" value="'.$ID.'"/></td>'.
					 "<td class='level_$level title'><a href='$editLink' target='_blank'>".$p->post_title."</a></td>".
					 "<td>$ID</td>".
					 "<td class='per'>".(str_replace($xsiteurl,"",$permalink))."</td>".
					 "<td class='da'>{$p->user_nicename}</td>".	
					 "<td class='da'>".date("Y-m-d",strtotime($p->post_modified))."</td>".				
					 ($type=="post"?"":"<td class='tem'>".$template."</td>").
					 ($this->xmlmap?"<td class='tem co'><a class='sm' pid='$ID'>$sitemap</a></td>":"").
					 "<td class='co'><a class='go' pid='$ID'>$google</a></td>".					 
					 "<td class='ic co' width='40'><a class='com' pid='$ID'>$comment</a></td>".
					  "<td class='co cc' width='40'>$commct</td>".
					 "<td class='ic'>$view</td>".
					 "<td class='ic'>$delete</td>".
					 "</tr>\n";					 
				echo $o;
				if($isParent==false && $type=="page") { 
					$this->listPages("page",false,$p->ID,$level+1);
				}					
			}				 	 	
			
		}
	}//func		
	
	function getAllPages($type,$status,$parent=0,$isParentPage=false,$category=0){
		global $wpdb;
		
		$status = trim($status)==""?"publish":$status;
		
		$order = "";
					
		switch($_POST["post_order"]){
			case "created-a": $order = " ORDER BY post_date ASC "; break;
			case "created-d": $order = " ORDER BY post_date DESC "; break;
			case "modified-d": $order = " ORDER BY post_modified DESC "; break;
			case "modified-a": $order = " ORDER BY post_modified ASC "; break;
			case "title-d": $order = " ORDER BY post_title DESC "; break;
			default: $order = " ORDER BY post_title ASC "; break;
		}
					
		if($type=="post") {						
			$cat = $category==0?"":" term_id='$category'".
				   " AND taxonomy = 'category' AND  ";
				   							  																
			$ssql = "SELECT 
						  $wpdb->posts.post_title,
					  	  $wpdb->posts.ID,
					 	  $wpdb->posts.comment_status,
						  comment_count,
						  1 as sitemap,
						  post_date,
						  post_modified,
						  $wpdb->users.user_nicename
						FROM
						  $wpdb->term_taxonomy						  
						  INNER JOIN $wpdb->term_relationships ON ($wpdb->term_taxonomy.term_taxonomy_id = $wpdb->term_relationships.term_taxonomy_id)
						  INNER JOIN $wpdb->posts ON ($wpdb->term_relationships.object_id = $wpdb->posts.ID)
						  INNER JOIN $wpdb->users ON ($wpdb->posts.post_author = $wpdb->users.ID)
						WHERE
						  $cat 						  
						  post_status = '$status' AND 
						  post_type = 'post' AND 
						  post_parent = 0
						GROUP BY ID  						  
						$order";
			
			//echo $ssql;
					  														 
			$xpages = $wpdb->get_results($ssql);		
			//echo $wpdb->last_query."<br/>";			  
						  				  							
		} else {
			
			$parentFilter = $isParentPage?" ID = $parent ": " post_parent = $parent ";
			
			$sql = "SELECT 
					  $wpdb->posts.post_title,
					  $wpdb->posts.ID,
					  $wpdb->posts.comment_status,
					  $wpdb->posts.comment_count,
					  $wpdb->posts.post_modified,
					  $wpdb->users.user_nicename					  						  
					FROM
					  $wpdb->posts
					  INNER JOIN $wpdb->users ON ($wpdb->posts.post_author = $wpdb->users.ID)
					WHERE
					  post_status = '$status' AND 
					  post_type = 'page'  AND 
					  $parentFilter
					GROUP BY ID 
					$order";
			
			//echo $sql;
						
			$xpages = $wpdb->get_results($sql);
										  																			 
		}	
		return $xpages;	
	} //func
	
	
	function getDelete($ID){
		$jsdel = "onclick=\"delPost($ID,this);\"";			
		$delete = "<span class='del_icon'><a class='pdel' $jsdel>&nbsp;&nbsp;&nbsp;&nbsp;</a></span>";
		return $delete;				
	}//func
	
	function getTemplate($ID){
		$template =  str_replace(".php","",get_post_meta($ID,"_wp_page_template",true));		
		if(strlen($template)>10){
			$template = substr($template,0,10)."...";
		}		
		return $template;		
	}//func
	
	
	function getGoogle($ID){
		$google = (isset($this->google_blocked[$ID])?$this->google_blocked[$ID]:"allowed");
		return $google;		  		
	} //func
	
	
	function cssAndScripts(){		
		wp_enqueue_script("cg_pages_script",
						  WP_PLUGIN_URL."/page-and-post-lister/cg_pages_tool.js",
						  array("jquery"),
						  "1.0");
		echo '<link rel="stylesheet" href="'.WP_PLUGIN_URL.'/page-and-post-lister/cg_pages.css" type="text/css" />';		
	}//func	
	

	function ajax_enCOMass($idx,$status){
		global $wpdb;		
															
		$affected = $wpdb->query("UPDATE $wpdb->posts 
								  SET comment_status='$status' 
						      	  WHERE ID='$idx'");		

	} // func	

	function ajax_sitemapEX($idx=0){
		global $wpdb;		
			
		$sm = get_option("sm_options");
											
		$id = $idx==0?$_POST["id"]:$idx;
		
		if(in_array($id,$sm["sm_b_exclude"])){
			// is currently in the excluded list,
			// therefore remove (include back to sitemap)
			
			//check first if it is in the excluded categories,
			// if yes, return failed
			
			if(in_category($this->xmlsettings["sm_b_exclude_cats"],$id)){
				echo "Failed: This post is under an excluded category.";
				die;
			}
			
			unset($sm["sm_b_exclude"][array_search($id,$sm["sm_b_exclude"])]);
			update_option("sm_options",$sm);	
			if($idx==0){
				echo "included";
				die;
			}
		} else {
			// is currently NOT in the excluded list,
			// therefore add (exclude from sitemap)
			$sm["sm_b_exclude"][] = $id;
			update_option("sm_options",$sm);	
			if($idx==0){
				echo "excluded";
				die;
			}			
		}
	} // func		
	
	function ajax_enCO(){
		global $wpdb;		
			
		if($_POST["id"]){
			$p = $_POST["id"];																
			$o = $wpdb->get_var("SELECT 								
									  $wpdb->posts.comment_status
									FROM
									  $wpdb->posts
									WHERE ID = '$p'");			  								
																							
			$status =$o == 'closed'?'open':'closed';
			
			$affected = $wpdb->query("UPDATE $wpdb->posts 
									  SET comment_status='$status' 
							      	  WHERE ID='$p'");		
			if($affected>0){
				echo $status;
			} else {
				echo "failed";
			}
		}; 
		die;
	} // func	
	
	function ajax_googleBlock($idx=0,$op=""){
		global $wpdb;
		
		$p = $idx==0?$_POST["id"]:$idx;
		$op = $op==""?$_POST["what"]:$op;	
		
		$key = "_cg_google_blocked";
		
		if($op=="allowed"){
			// include																							
			$o = $wpdb->query("REPLACE INTO $wpdb->postmeta
							(post_id,meta_key,meta_value)
							VALUES   
							($p,'$key',1)");		
			if($idx==0) {
				echo "blocked";
				die();
			}
		} else {
			// exclude																					
			$o = $wpdb->query("DELETE FROM $wpdb->postmeta
							  WHERE meta_key='_cg_google_blocked' AND post_id='$p'");		
			if($idx==0){
				echo "allowed";
				die();
			}
		}		
	}//func		
					
	function ajax_delPost(){
		global $wpdb;
			if($_POST["id"]){
				if(wp_delete_post($_POST["id"])) {
					echo "Successfully deleted the post/page.";
				} else {
					echo "Error deleting post/page.";
				};
			}
		die;
	}//func;		
	
} //class

//global functions

	function cg_google_blocks(){
		global $post, $wpdb;	
		if($post->ID==""){
			return false;
		}
		$f = $wpdb->get_row("SELECT 
								  $wpdb->postmeta.post_id,
								  $wpdb->postmeta.meta_key
								FROM
								  $wpdb->postmeta
							 WHERE post_id='$post->ID' AND meta_key='_cg_google_blocked'");	
		if(count($f)){
			?>
				<!-- CG Google Meta (this post was tagged to be blocked from Google and other Search Engines) -->	
				<meta name="robots" content="noindex">
				<meta name="googlebot" content="noindex">
				<meta name="robots" content="noarchive">
				<meta name="googlebot" content="noarchive">
				<meta name="googlebot" content="nosnippet">			
			<?php						
		}		
	}//func

?>