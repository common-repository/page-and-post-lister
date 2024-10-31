
	function switchThird(){
		if (j("#post_type").val() == "post") {
			j("#page_parent").val(0).hide();
			j("#post_categories").val(0).show();
			j("#post_categories").css("display","visible");
			switchFourth();
		} else {
			j("#page_parent").val(0).show();
			j("#post_categories").val(0).hide();
			j("#post_order").val(0).hide();
		}
	}

	function switchFourth(){
		if (j("#post_type").val() == "post") {
			if (j("#post_status").val() == "pending") {
				j("#page_parent").val(0).hide();
				j("#post_order").val(0).show();
				j("#post_order").css("display", "visible");
			}
			else {				
				j("#post_order").val(0).hide();
			}
		}
	}

	function exclude(id,rowid){
		j.ajax({
			   type: "POST",
			   url: cg_site_url + "wp-admin/admin-ajax.php",
			   data: "action=exclude&id="+id,
			   success: function(msg){
				 j("tr#row_"+rowid+" td.cell_x").html("Removed");
			   }
			 });

	}
	
	function include(id,rowid){
		j.ajax({
			   type: "POST",
			   url: cg_site_url + "wp-admin/admin-ajax.php",
			    data: "action=include&id="+id,
			   success: function(msg){
				 j("tr#row_"+rowid+" td.cell_x").html("Added");
			   }
			 });
	}
	
	function delPost(id,container){
		if (confirm("Are you sure you want to delete the selected page/post? ")) {
			j("tr#tr_"+id).remove();
			j.ajax({
				type: "POST",
				url: cg_site_url + "wp-admin/admin-ajax.php",
				data: "action=delPost&id=" + id+"&cookie=" + encodeURIComponent(document.cookie),
				success: function(msg){
					alert(msg);
				}
			});	
		}			 
	}	
	
	function confirmAction(){
		if (j("#mass_action").val() == "batch-delete") {
			if (confirm("WARNING: You are about to DELETE all checked pages/posts? ")) {
				return true;
			} else {
				j("#mass_action").val("");	
				return false;
			}			
		}
		
	}	
	
	function checkBoxes(){
		if(j("input#mass_ids").val()==""){
			alert("Nothing selected.");
			return false;
		}
		if(j("select#mass_action").val()==""){
			alert("No command selected.");
			return false;
		}		
		return true;	
	}

	function enCO(id,container){
					
	}

var cg_toggles = {
	init: function(){
		
		  	// sitemap
		  	 j(".co a.sm").click(function(){
			 	var active = this;
			 	what = j(active).html();
				j(active).html("wait...");
			 	j.ajax({
					type: "POST",
					url: cg_site_url + "wp-admin/admin-ajax.php",
					data: "action=sitemapEX&id=" + j(active).attr("pid") + "&what=" + what  + "&cookie=" + encodeURIComponent(document.cookie),
					success: function(msg){
						if (msg != "included" && msg != "excluded") {
							j(active).html(what);
							alert(msg);
						}
						else {
							j(active).html(msg).css("color", msg === "excluded" ? "#ff0000" : "#339900");
						}
					}
				});
			 })
		 
			 // postmeta	 	
		  	 j(".co a.go").click(function(){
			 	var active = this;
			 	what = j(active).html();
				j(active).html("wait...");
			 	j.ajax({
					type: "POST",
					url: cg_site_url + "wp-admin/admin-ajax.php",
					data: "action=googleBlock&id=" + j(active).attr("pid") + "&what=" + what  + "&cookie=" + encodeURIComponent(document.cookie),
					success: function(msg){
						j(active).html(msg).css("color", msg === "blocked" ? "#ff0000" : "#339900");
					}
				});	
			 })	
			 
			 // comments
			 
			j(".co a.com").click(function(){
			 	var active = this;
			 	what = j(active).html();
				j(active).html("wait...");
			 	j.ajax({
					type: "POST",
					url: cg_site_url + "wp-admin/admin-ajax.php",
					data: "action=enCO&id=" + j(active).attr("pid")+ "&cookie=" + encodeURIComponent(document.cookie),
					success: function(msg){
						j(active).html(msg).css("color", msg === "open" ? "#ff0000" : "#339900");
					}
				});	
			 })				 		
	}
}	

	
  var checks = {
  		init: function(){
			j("input.post_check").click(function(){
				checks.getIds();			
			})	
		},
		scan: function(){
			var theval = j("input#main_check:checked").length > 0?true:false;
			j("input.post_check").attr("checked",theval);
			if (theval) {
				checks.getIds();
			} else {
				j("input#mass_ids").val("");
			}	
		},
		getIds: function(){
			var tmp="";
			pc = j("input.post_check:checked").length;
			if(pc>0){
				var x=0;
				j("input.post_check:checked").each(function(){
					x++;
					tmp += j(this).val()+(x<pc?",":"");
				})
				j("input#mass_ids").val(tmp);
			}
		}			
  }	

	
  var j = jQuery.noConflict();	
		
  j(document).ready(function(){
		j("#menu").show();
		j(".co a:contains('open')").css("color","#ff0000");
		j(".co a:contains('sitemap')").css("color","#339900");
		j(".co a:contains('excluded')").css("color","#ff0000");
		j(".co a:contains('blocked')").css("color","#ff0000");

		checks.init();
		
		cg_toggles.init();
	});