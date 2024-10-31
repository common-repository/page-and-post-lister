<?php
/*
Plugin Name: Page and Post Lister
Plugin URI: http://www.codesandgraphics.com/cg-products/page-and-post-lister/
Description: Displays all posts and pages in a single page view, including hierarchy/category, ID, permalink, author, date modified, shortcut links to edit and view, Google XML Sitemap status, search engine visibility and comment switches.
Version: 1.2.1
Author: Codes and Graphics
Author URI: http://www.codesandgraphics.com

Released under the GPL v.2, http://www.gnu.org/copyleft/gpl.html

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
*/

require_once("cg_pages_pages.php");
	
	class cg_pages extends cg_pages_pages{
		
		var $version="1.2.1";  // check for consistency with the versions above and the readme.txt 	
			
		function __construct(){		
		
			parent::__construct();
					
			add_action("admin_menu", array(&$this,"cg_menu"));
							
		}//func
	
		function cg_menu() {
	
			add_submenu_page("edit.php",
							 "Post Lister",
							 "Post Lister",
							 8,
							 "", // from __FILE__ fix for the "Class cannot be redeclared error
							 array(&$this,"launcher1"));
							 
			add_submenu_page("edit.php?post_type=page",
							 "Page Lister",
							 "Page Lister",
							 8,
							  __FILE__, 
							 array(&$this,"launcher2"));							 
	
				
		}//func
	
		function launcher1(){
			$this->showEm(true);
		}//func	
		
		function launcher2(){
			$this->showEm(false);				
		}//func
		
		function showEm($isPost){	
			$selected = "selected='selected'";	
			extract($_POST);
			?>
				<div class="wrap">
				<h2><?php echo $isPost?"Post":"Page";?> Lister</h2>
				<p>Version <?php echo $this->version;?> </p>
				<?php 
					//print_r($_POST);
					$this->processMassActions();		
					$this->getPageSettings();	
				?>
				<form  action="" method="post" name="change_type" onsubmit="return confirmAction();">
					<input id="post_type" name="post_type" type="hidden" value="<?php echo $isPost?'post':'page';?>"/>	
					<select id="post_status" name="post_status" style="width:120px;">
							<?php $this->showPostDrop($post_status,$isPost?"post":"page");  ?>			
					</select>
				<?php																
					if($isPost){ 
						$this->showPostFilterControls(); 
					} else {
						$this->showPageFilterControls(); 
					}
				?>	
					<select name="post_order" id="post_order">
						<option value="">Select Sort Order</option>
						<option value="title-a" <?php echo $post_order=="title-a"?$selected:""; ?>>Sort By Title Ascending</option>
						<option value="title-d" <?php echo $post_order=="title-d"?$selected:""; ?>>Sort By Title Descending</option>			
						<option value="created-a" <?php echo $post_order=="created-a"?$selected:""; ?>>Sort By Date Created Ascending</option>
						<option value="created-d" <?php echo $post_order=="created-d"?$selected:""; ?>>Sort By Date Created Descending</option>
						<option value="modified-a" <?php echo $post_order=="modified-a"?$selected:""; ?>>Sort By Date Modified Ascending</option>
						<option value="modified-d" <?php echo $post_order=="modified-d"?$selected:""; ?>>Sort By Date Modified Descending</option>								
					</select>	
					<input type="submit" name="submit" value="Update"/>						
					<table width='99%' id='cg_pages'>
					<tr>
						  <th style='width:10px;'><input id='main_check' type='checkbox' onclick='checks.scan();' value='1'/></th>
						  <th>Title</th>
						  <th width='50'>ID#</th>
						  <th>Permalink</th>
						  <th width='70'>Author</th>
						  <th width='75'>Date Modified</th>
						  <?php if(!$isPost) echo "<th width='50'>Template</th>"; ?>
						  <?php if($this->xmlmap) echo "<th width='75'>Google XML Sitemap</th>";?>
						  <th width='75' title="Allow Search Engine Indexing">Allow Indexing</th>
						  <th width='35' colspan='2'>Comments</th>
						  <th width='35'>View</th>
						  <th width='35'>Delete</th>
					</tr>
				<?php		  
					if($isPost){
						$this->displayPosts($_POST["post_categories"],0);
					} else {
						$this->show_pages(true);
					}				
				?>
					</table>
				
					<div id="batch_actions">						
					With selected:  
						<input type="hidden" name="mass_ids" id="mass_ids"/>
						<select id="mass_action" name="mass_action">
							<option value="">Select Batch Action</option>					
							<option value="google-search-in">Allow Indexing</option>
							<option value="google-search-ex">Block Indexing</option>									
							<option value="comment-close">Close Comments</option>
							<option value="comment-open">Open Comments</option>
							<option value="publish">Set to Publish</option>
							<option value="draft">Set to Draft</option>
							<option value="private">Set to Private</option>
							<option value="batch-delete">Batch Delete</option>
						</select>
						<input type="submit" value="Apply" name="submit" onclick="return checkBoxes();"/>			
					</div>	
				</form> 
				<p>
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="hosted_button_id" value="9AYZTYW3WN4DE">
					<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
					<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
					</form>				
				</p>
				<p>Plugin provided by: <a href="http://www.codesandgraphics.com">http://www.codesandgraphics.com</a></p>
				<script type="text/javascript">
					cg_site_url = "<?php echo $this->site;?>/";
				</script>
				</div>			
							
			<?php	
	
		}//func
		
		function showPostDrop($post_status,$type){
			global $wpdb;
			
			$post_status = trim($post_status)==""?"publish":""; 
			
			$stats = array("publish"=>"Published",
						   "draft"=>"Drafts",
						   "pending"=>"Pending Review",
						   "future"=>"Scheduled",
						   "trash"=>"Trashed",
						   "private"=>"Private");
			
			$stum = $wpdb->get_results("SELECT 
							  post_status,
							  count(ID) as tstat
							FROM
							  $wpdb->posts      
							WHERE post_type='$type'       
							GROUP by post_status");
						
			foreach($stum as $st){
				if(array_key_exists($st->post_status, $stats)){
					$stats[$st->post_status]=$stats[$st->post_status]." ({$st->tstat})";
				}
			}			
									   		
			?>
						<option value="">Select Status</option>
						<?php
							foreach($stats as $k=>$v){
								$selected = $post_status==$k?"selected='selected'":"";							
								echo "<option value='$k' $selected>$v</option>";
							}
						?>	
			<?php						
		}//func
} //class

if(is_admin()){
	$cgpages = new cg_pages();	
} else {	
	add_action("wp_head", "cg_google_blocks");		
}

?>